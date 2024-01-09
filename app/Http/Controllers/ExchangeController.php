<?php

namespace App\Http\Controllers;

use App\Models\ChargesLimit;
use App\Models\Currency;
use App\Models\Exchange;
use App\Models\Template;
use App\Models\Transaction;
use App\Models\TwoFactorSetting;
use App\Models\Wallet;
use App\Traits\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Validator;

class ExchangeController extends Controller
{
	use Notify;

	public function initialize(Request $request)
	{
		if ($request->isMethod('get')) {
			$currencies = Currency::select('id', 'code', 'name', 'currency_type')->where('is_active', 1)->get();
			$template = Template::where('section_name', 'exchange-money')->first();
			return view('user.exchange.create', compact('currencies', 'template'));
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());
			$validationRules = [
				'from_wallet' => 'required|integer|min:1|not_in:0',
				'to_wallet' => 'required|integer|min:1|not_in:0',
				'amount' => 'required|numeric|min:1|not_in:0',
			];
			$validate = Validator::make($purifiedData, $validationRules);
			if ($validate->fails()) {
				return back()->withErrors($validate)->withInput();
			}
			$purifiedData = (object)$purifiedData;

			$userId = Auth::id();
			$fromCurrencyId = $purifiedData->from_wallet;
			$toCurrencyId = $purifiedData->to_wallet;
			$amount = $purifiedData->amount;

			$checkAmountValidate = $this->checkAmountValidate($userId, $fromCurrencyId, $toCurrencyId, $amount);

			if (!$checkAmountValidate['status']) {
				return back()->withInput()->with('alert', $checkAmountValidate['message']);
			}

			$exchange = new Exchange();
			$exchange->user_id = $userId;
			$exchange->from_wallet = $checkAmountValidate['from_wallet'];
			$exchange->to_wallet = $checkAmountValidate['to_wallet'];
			$exchange->percentage = $checkAmountValidate['percentage'];
			$exchange->charge_percentage = $checkAmountValidate['charge_percentage'];
			$exchange->charge_fixed = $checkAmountValidate['charge_fixed'];
			$exchange->charge = $checkAmountValidate['charge'];
			$exchange->exchange_rate = $checkAmountValidate['exchange_rate'];
			$exchange->amount = $checkAmountValidate['amount'];
			$exchange->transfer_amount = $checkAmountValidate['transfer_amount'];
			$exchange->received_amount = $checkAmountValidate['received_amount'];
			$exchange->utr = (string)Str::uuid();
			$exchange->status = 0; //pending
			$exchange->save();
			return redirect(route('exchange.confirm', $exchange->utr))->with('success', 'Exchange initiated successfully');
		}
	}

	public function confirmExchange(Request $request, $utr)
	{
		$user = Auth::user();
		$exchange = Exchange::with(['fromWallet', 'toWallet'])->where('utr', $utr)->first();

		if (!$exchange || $exchange->status) { //Check is exchange found and unpaid
			return redirect(route('exchange.initialize'))->with('success', 'Exchange already complete');
		}

		$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
		$enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);

		if ($request->isMethod('get')) {
			return view('user.exchange.confirm', compact(['utr', 'exchange', 'enable_for']));
		} elseif ($request->isMethod('post')) {
			if (in_array('exchange', $enable_for)) {
				$purifiedData = Purify::clean($request->all());
				$validationRules = [
					'security_pin' => 'required|integer|digits:5',
				];
				$validate = Validator::make($purifiedData, $validationRules);

				if ($validate->fails()) {
					return back()->withErrors($validate)->withInput();
				}
				if (!Hash::check($purifiedData['security_pin'], $twoFactorSetting->security_pin)) {
					return back()->withErrors(['security_pin' => 'You have entered an incorrect PIN'])->with('alert', 'You have entered an incorrect PIN')->withInput();
				}
			}

			$checkAmountValidate = $this->checkAmountValidate($exchange->user_id, optional($exchange->fromWallet)->currency_id, optional($exchange->toWallet)->currency_id, $exchange->amount);
			if (!$checkAmountValidate['status']) {
				return back()->withInput()->with('alert', $checkAmountValidate['message']);
			}

			/*
			 * Deduct money from from Wallet
			 * */
			$sender_wallet = updateWallet($exchange->user_id, optional($exchange->fromWallet)->currency_id, $exchange->transfer_amount, 0);
			/*
			 * Add money to receiver wallet
			 * */
			$receiver_wallet = updateWallet($exchange->user_id, optional($exchange->toWallet)->currency_id, $exchange->received_amount, 1);

			$transaction = new Transaction();
			$transaction->amount = $exchange->amount;
			$transaction->charge = $exchange->charge;
			$transaction->currency_id = optional($exchange->fromWallet)->currency_id;
			$exchange->transactional()->save($transaction);
			$exchange->status = 1;
			$exchange->save();

			$receivedUser = $user;
			$params = [
				'from_amount' => getAmount($exchange->transfer_amount),
				'from_currency' => optional(optional($exchange->fromWallet)->currency)->code,
				'to_amount' => getAmount($exchange->received_amount),
				'to_currency' => optional(optional($exchange->toWallet)->currency)->code,
				'transaction' => $exchange->utr,
			];

			$action = [
				"link" => route('exchange.index'),
				"icon" => "fa fa-money-bill-alt text-white"
			];
			$firebaseAction = route('exchange.index');
			$this->sendMailSms($receivedUser, 'MONEY_EXCHANGE', $params);
			$this->userPushNotification($receivedUser, 'MONEY_EXCHANGE', $params, $action);
			$this->userFirebasePushNotification($receivedUser, 'MONEY_EXCHANGE', $params, $firebaseAction);

			return redirect(route('exchange.index'))->with("success", "Your exchange has been completed your remaining amount of money $sender_wallet");
		}
	}

	public function currenciesExceptSelected(Request $request)
	{
		$from_wallet = $request->from_wallet;
		$to_wallet = Currency::select('id', 'code', 'name')->where('id', '!=', $from_wallet)->where(['is_active' => 1])->get();

		$data['status'] = true;
		$data['message'] = '';
		$data['to_wallet'] = $to_wallet;

		return $data;
	}

	public function checkAmount(Request $request)
	{
		$userId = Auth::id();
		$fromCurrencyId = $request->from_wallet;
		$toCurrencyId = $request->to_wallet;
		$amount = $request->amount;

		$data = $this->checkAmountValidate($userId, $fromCurrencyId, $toCurrencyId, $amount);
		return response()->json($data);
	}

	public function checkAmountValidate($userId, $fromCurrencyId, $toCurrencyId, $amount)
	{
		$defaultCurrency = Currency::where('id', basicControl()->base_currency)->first();
		$defaultCurrencyRate = $defaultCurrency->exchange_rate;

		$fromWallet = Wallet::with('currency')->where(['user_id' => $userId, 'currency_id' => $fromCurrencyId])->first();
		$toWallet = Wallet::with('currency')->where(['user_id' => $userId, 'currency_id' => $toCurrencyId])->first();
		$toLimit = optional($toWallet->currency)->currency_type == 0 ? 8 : 4;
		$chargesLimit = ChargesLimit::with('currency')->where(['currency_id' => $fromCurrencyId, 'transaction_type_id' => config('transactionType.exchange'), 'is_active' => 1])->first();
		$limit = optional($chargesLimit->currency)->currency_type == 0 ? 8 : 4;

		$amount = getAmount($amount, $limit);
		$status = false;
		$percentage = 0;
		$chargeFixed = 0;
		$chargePercentage = 0;
		$charge = 0;
		$minLimit = 0;
		$maxLimit = 0;

		if ($chargesLimit) {
			$percentage = getAmount($chargesLimit->percentage_charge, $limit);
			$chargeFixed = getAmount($chargesLimit->fixed_charge, $limit);
			$chargePercentage = getAmount(($amount * $percentage) / 100, $limit);
			$charge = getAmount($chargePercentage + $chargeFixed, $limit);
			$minLimit = getAmount($chargesLimit->min_limit, $limit);
			$maxLimit = getAmount($chargesLimit->max_limit, $limit);
		}

		$fromExchangeRate = getAmount($fromWallet->currency->exchange_rate, $limit);
		$toExchangeRate = getAmount($toWallet->currency->exchange_rate, $toLimit);
		$exchangeRate = getAmount(($defaultCurrencyRate / $fromExchangeRate) * $toExchangeRate, $toLimit);

		$transferAmount = getAmount($amount + $charge, $limit);
		$receivedAmount = getAmount($amount * $exchangeRate, $toLimit);

		$fromWalletBalance = getAmount($fromWallet->balance, $limit);
		$fromWalletUpdateBalance = getAmount($fromWalletBalance - $transferAmount, $limit);
		$toWalletUpdateBalance = getAmount($toWallet->balance + $receivedAmount, $toLimit);

		if ($amount < $minLimit || $amount > $maxLimit) {
			$message = "minimum transfer $minLimit and maximum transfer limit $maxLimit";
		} elseif ($transferAmount > $fromWalletBalance) {
			$message = 'Does not have enough money to cover transfer';
		} else {
			$status = true;
			$message = "Remaining balance : $fromWalletUpdateBalance " . optional($fromWallet->currency)->code;
		}

		$data = [
			'balance' => $fromWalletBalance,
			'user_id' => $userId,
			'from_wallet' => $fromWallet->id,
			'to_wallet' => $toWallet->id,
			'percentage' => $percentage,
			'charge_percentage' => $chargePercentage,
			'charge_fixed' => $chargeFixed,
			'charge' => $charge,
			'exchange_rate' => $exchangeRate,
			'amount' => $amount,
			'transfer_amount' => $transferAmount,
			'received_amount' => $receivedAmount,
			'status' => $status,
			'message' => $message,
			'fromWalletUpdateBalance' => $fromWalletUpdateBalance,
			'toWalletUpdateBalance' => $toWalletUpdateBalance,
			'min_limit' => $minLimit,
			'max_limit' => $maxLimit,
			'currency_limit' => $limit,
		];

		return $data;
	}

	public function index()
	{
		$userId = Auth::id();
		$wallets = Wallet::with('currency')->where('user_id', $userId)->get();
		$exchanges = Exchange::with(['fromWallet', 'toWallet'])->where(['user_id' => $userId])->latest()->paginate();
		return view('user.exchange.index', compact('wallets', 'exchanges'));
	}

	public function search(Request $request)
	{
		$wallets = Wallet::with('currency')->where('user_id', Auth::id())->get();
		$search = $request->all();
		$created_date = isset($search['created_at']) ? preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']) : 0;

		$exchanges = Exchange::with('fromWallet', 'toWallet')
			->where('user_id', Auth::id())
			->when(isset($search['utr']), function ($query) use ($search) {
				return $query->where('utr', 'LIKE', "%{$search['utr']}%");
			})
			->when(isset($search['min']), function ($query) use ($search) {
				return $query->where('amount', '>=', $search['min']);
			})
			->when(isset($search['max']), function ($query) use ($search) {
				return $query->where('amount', '<=', $search['max']);
			})
			->when($created_date == 1, function ($query) use ($search) {
				return $query->whereDate("created_at", $search['created_at']);
			})
			->when(isset($search['from_wallet']), function ($query) use ($search) {
				return $query->where('from_wallet', $search['from_wallet']);
			})
			->when(isset($search['to_wallet']), function ($query) use ($search) {
				return $query->where('to_wallet', $search['to_wallet']);
			})
			->when(isset($search['status']), function ($query) use ($search) {
				return $query->where('status', $search['status']);
			})
			->latest()
			->paginate();
		$exchanges->appends($search);
		return view('user.exchange.index', compact('search', 'exchanges', 'wallets'));
	}
}
