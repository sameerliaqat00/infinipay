<?php

namespace App\Http\Controllers;

use App\Models\ChargesLimit;
use App\Models\Currency;
use App\Models\RedeemCode;
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

class RedeemCodeController extends Controller
{
	use Notify;

	public function index()
	{
		$userId = Auth::id();
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$redeemCodes = RedeemCode::with(['sender', 'receiver', 'currency'])
			->where(function ($query) use ($userId) {
				$query->where('sender_id', $userId);
				$query->orWhere('receiver_id', $userId);
			})
			->latest()
			->paginate();
		return view('user.redeem.index', compact('currencies', 'redeemCodes'));
	}

	public function search(Request $request)
	{
		$filterData = $this->_filter($request);
		$search = $filterData['search'];
		$currencies = $filterData['currencies'];
		$userId = $filterData['userId'];

		$redeemCodes = $filterData['redeemCodes']
			->where(function ($query) use ($userId) {
				$query->where('sender_id', $userId)->orWhere('receiver_id', $userId);
			})
			->latest()
			->paginate();
		$redeemCodes->appends($filterData['search']);
		return view('user.redeem.index', compact('search', 'redeemCodes', 'currencies'));
	}

	public function _filter($request)
	{
		$userId = Auth::id();
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$search = $request->all();
		$sent = isset($search['type']) ? preg_match("/sent/", $search['type']) : 0;
		$received = isset($search['type']) ? preg_match("/received/", $search['type']) : 0;
		$created_date = isset($search['created_at']) ? preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']) : 0;

		$redeemCodes = RedeemCode::with('sender', 'receiver', 'currency')
			->when(isset($search['email']), function ($query) use ($search) {
				return $query->where('email', 'LIKE', "%{$search['email']}%");
			})
			->when(isset($search['utr']), function ($query) use ($search) {
				return $query->where('utr', 'LIKE', "%{$search['utr']}%");
			})
			->when(isset($search['min']), function ($query) use ($search) {
				return $query->where('amount', '>=', $search['min']);
			})
			->when(isset($search['max']), function ($query) use ($search) {
				return $query->where('amount', '<=', $search['max']);
			})
			->when(isset($search['currency_id']), function ($query) use ($search) {
				return $query->where('currency_id', $search['currency_id']);
			})
			->when(isset($search['sender']), function ($query) use ($search) {
				return $query->whereHas('sender', function ($qry) use ($search) {
					$qry->where('name', 'LIKE', "%{$search['sender']}%");
				});
			})
			->when(isset($search['receiver']), function ($query) use ($search) {
				return $query->whereHas('receiver', function ($qry) use ($search) {
					$qry->where('name', 'LIKE', "%{$search['receiver']}%");
				});
			})
			->when($sent == 1, function ($query) use ($search) {
				return $query->where("sender_id", Auth::id());
			})
			->when($received == 1, function ($query) use ($search) {
				return $query->where("receiver_id", Auth::id());
			})
			->when($created_date == 1, function ($query) use ($search) {
				return $query->whereDate("created_at", $search['created_at']);
			})
			->when(isset($search['status']), function ($query) use ($search) {
				return $query->where('status', $search['status']);
			});

		$data = [
			'userId' => $userId,
			'currencies' => $currencies,
			'search' => $search,
			'redeemCodes' => $redeemCodes,
		];
		return $data;
	}

	public function insertRedeemCode(Request $request)
	{
		$user = Auth::user();
		if ($request->isMethod('get')) {
			return view('user.redeem.insert');
		} elseif ($request->isMethod('post')) {

			$purifiedData = Purify::clean($request->all());
			$validationRules = [
				'redeemCode' => 'required|uuid',
			];

			$validate = Validator::make($purifiedData, $validationRules);
			if ($validate->fails()) {
				return back()->withErrors($validate)->withInput();
			}
			$purifiedData = (object)$purifiedData;
			$utr = $purifiedData->redeemCode;
			$redeemCode = RedeemCode::where('utr', $utr)->first();

			if (!$redeemCode) { //Check is transaction found
				return back()
					->withInput()
					->withErrors(['redeemCode' => 'Your redeem code is invalid'])
					->with('alert', 'Your redeem code is invalid');
			} elseif ($redeemCode->sender_id == $user->id) { // Check is transaction try to used by own
				return back()
					->withInput()
					->withErrors(['redeemCode' => 'Not allowed to self generated code'])
					->with('alert', 'Not allowed to self generated code');
			} elseif ($redeemCode->status != 1) { // Check is transaction unused
				return back()
					->withInput()
					->withErrors(['redeemCode' => 'Your redeem code is already used'])
					->with('alert', 'Your redeem code is already used');
			}

			/*
			* Add money to receiver wallet
			* */
			$receiver_wallet = updateWallet($user->id, $redeemCode->currency_id, $redeemCode->received_amount, 1);

			$transaction = new Transaction();
			$transaction->amount = $redeemCode->amount;
			$transaction->charge = $redeemCode->charge;
			$transaction->currency_id = $redeemCode->currency_id;
			$redeemCode->transactional()->save($transaction);
			$redeemCode->receiver_id = $user->id;
			$redeemCode->email = $user->email;
			$redeemCode->status = 2;// 1 = success, 0 = pending, 2 = used
			$redeemCode->save();


			$receivedUserUsedBy = $user;
			$params = [
				'amount' => getAmount($redeemCode->amount),
				'currency' => optional($redeemCode->currency)->code,
				'transaction' => $redeemCode->utr,
			];
			$action = [
				"link" => route('redeem.index'),
				"icon" => "fa fa-money-bill-alt text-white"
			];
			$firebaseAction = route('redeem.index');
			$this->sendMailSms($receivedUserUsedBy, 'REDEEM_CODE_USED_BY', $params);
			$this->userPushNotification($receivedUserUsedBy, 'REDEEM_CODE_USED_BY', $params, $action);
			$this->userFirebasePushNotification($receivedUserUsedBy, 'REDEEM_CODE_USED_BY', $params, $firebaseAction);

			$receivedUserSender = optional($redeemCode->sender)->name;
			$params = [
				'receiver' => optional($redeemCode->receiver)->name,
				'amount' => getAmount($redeemCode->amount),
				'currency' => optional($redeemCode->currency)->code,
				'transaction' => $redeemCode->utr,
			];

			$this->sendMailSms($receivedUserSender, 'REDEEM_CODE_SENDER', $params);
			$this->userPushNotification($receivedUserSender, 'REDEEM_CODE_SENDER', $params, $action);
			$this->userFirebasePushNotification($receivedUserSender, 'REDEEM_CODE_SENDER', $params, $firebaseAction);

			return redirect(route('redeem.index'))->with('success', 'Redeem code submitted successfully');
		}
	}

	public function initialize(Request $request)
	{
		if ($request->isMethod('get')) {
			$currencies = Currency::select('id', 'code', 'name', 'currency_type')->where('is_active', 1)->get();
			$template = Template::where('section_name', 'generate-redeem-code')->first();
			return view('user.redeem.create', compact('currencies', 'template'));
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());
			$validationRules = [
				'amount' => 'required|numeric|min:1|not_in:0',
				'currency' => 'required|integer|min:1|not_in:0',
				'charge_from' => 'nullable|integer|not_in:0',
			];

			$validate = Validator::make($purifiedData, $validationRules);
			if ($validate->fails()) {
				return back()->withErrors($validate)->withInput();
			}
			$purifiedData = (object)$purifiedData;
			$amount = $purifiedData->amount;
			$currency_id = $purifiedData->currency;
			$charge_from = isset($purifiedData->charge_from);

			$checkAmountValidate = $this->checkAmountValidate($amount, $currency_id, config('transactionType.redeem'), $charge_from);//1 = transfer

			if (!$checkAmountValidate['status']) {
				return back()->withInput()->with('alert', $checkAmountValidate['message']);
			}

			$redeemCode = new RedeemCode();
			$redeemCode->sender_id = Auth::id();
			$redeemCode->receiver_id = null;
			$redeemCode->currency_id = $checkAmountValidate['currency_id'];
			$redeemCode->percentage = $checkAmountValidate['percentage'];
			$redeemCode->charge_percentage = $checkAmountValidate['percentage_charge']; // amount after calculation percent of charge
			$redeemCode->charge_fixed = $checkAmountValidate['fixed_charge'];
			$redeemCode->charge = $checkAmountValidate['charge'];
			$redeemCode->amount = $checkAmountValidate['amount'];
			$redeemCode->transfer_amount = $checkAmountValidate['transfer_amount'];
			$redeemCode->received_amount = $checkAmountValidate['received_amount'];
			$redeemCode->charge_from = $checkAmountValidate['charge_from']; //0 = Sender, 1 = Receiver
			$redeemCode->note = $purifiedData->note;
			$redeemCode->email = null;
			$redeemCode->status = 0;// 1 = success, 0 = pending
			$redeemCode->utr = (string)Str::uuid();
			$redeemCode->save();
			return redirect(route('redeem.confirm', $redeemCode->utr))->with('success', 'Redeem code initiated successfully');
		}
	}

	public function confirmGenerate(Request $request, $utr)
	{
		$user = Auth::user();
		$redeemCode = RedeemCode::where('utr', $utr)->first();

		if (!$redeemCode || $redeemCode->status != 0) { //Check is transaction found and unpaid
			return redirect(route('redeem.initialize'))->with('success', 'Transaction already complete or invalid code');
		}

		$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
		$enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);

		if ($request->isMethod('get')) {
			return view('user.redeem.confirm', compact(['utr', 'redeemCode', 'enable_for']));
		} elseif ($request->isMethod('post')) {
			if (in_array('redeem', $enable_for)) {
				$purifiedData = Purify::clean($request->all());
				$validationRules = [
					'security_pin' => 'required|integer|digits:5',
				];
				$validate = Validator::make($purifiedData, $validationRules);

				if ($validate->fails()) {
					return back()->withErrors(['security_pin' => 'this is first message'])->withInput();
				}
				if (!Hash::check($purifiedData['security_pin'], $twoFactorSetting->security_pin)) {
					return back()->withErrors(['security_pin' => 'You have entered an incorrect PIN'])->with('alert', 'You have entered an incorrect PIN')->withInput();
				}
			}

			$checkAmountValidate = $this->checkAmountValidate($redeemCode->amount, $redeemCode->currency_id, config('transactionType.redeem'), $redeemCode->charge_from);//4 = redeem

			if (!$checkAmountValidate['status']) {
				return back()->withInput()->with('alert', $checkAmountValidate['message']);
			}

			$sender_wallet = updateWallet($redeemCode->sender_id, $redeemCode->currency_id, $redeemCode->transfer_amount, 0);
			$redeemCode->status = 1;
			$redeemCode->save();

			$receivedUser = $user;
			$params = [
				'amount' => getAmount($redeemCode->amount),
				'currency' => optional($redeemCode->currency)->code,
				'transaction' => $redeemCode->utr,
			];

			$action = [
				"link" => route('redeem.index'),
				"icon" => "fa fa-money-bill-alt text-white"
			];
			$firebaseAction = route('redeem.index');
			$this->sendMailSms($receivedUser, 'REDEEM_CODE_GENERATE', $params);
			$this->userPushNotification($receivedUser, 'REDEEM_CODE_GENERATE', $params, $action);
			$this->userFirebasePushNotification($receivedUser, 'REDEEM_CODE_GENERATE', $params, $firebaseAction);

			return redirect(route('redeem.index'))->with("success", "Your redeem code has been generated, your remaining amount of money $sender_wallet");
		}
	}

	public function checkAmount(Request $request)
	{
		if ($request->ajax()) {
			$amount = $request->amount;
			$currency_id = $request->currency_id;
			$transaction_type_id = $request->transaction_type_id;
			$charge_from = $request->charge_from;
			$data = $this->checkAmountValidate($amount, $currency_id, $transaction_type_id, $charge_from);
			return response()->json($data);
		}
	}

	public function checkAmountValidate($amount, $currency_id, $transaction_type_id, $charge_from)
	{
		$chargesLimit = ChargesLimit::with('currency')->where(['currency_id' => $currency_id, 'transaction_type_id' => $transaction_type_id, 'is_active' => 1])->first();
		$wallet = Wallet::firstOrCreate(['user_id' => Auth::id(), 'currency_id' => $currency_id]);
		$limit = optional($chargesLimit->currency)->currency_type == 0 ? 8 : 2;

		$balance = getAmount($wallet->balance, $limit);
		$amount = getAmount($amount, $limit);
		$status = false;
		$charge = 0;
		$min_limit = 0;
		$max_limit = 0;
		$fixed_charge = 0;
		$percentage = 0;
		$percentage_charge = 0;

		if ($chargesLimit) {
			$percentage = getAmount($chargesLimit->percentage_charge, $limit);
			$percentage_charge = getAmount(($amount * $percentage) / 100, $limit);
			$fixed_charge = getAmount($chargesLimit->fixed_charge, $limit);
			$min_limit = getAmount($chargesLimit->min_limit, $limit);
			$max_limit = getAmount($chargesLimit->max_limit, $limit);
			$charge = getAmount($percentage_charge + $fixed_charge, $limit);
		}

		if ($charge_from) {
			$transfer_amount = $amount;
			$received_amount = getAmount($amount - $charge, $limit);
		} else {
			$transfer_amount = getAmount($amount + $charge, $limit);
			$received_amount = $amount;
		}

		$remaining_balance = getAmount($balance - $transfer_amount, $limit);

		if ($wallet->is_active != 1) {
			$message = 'Currency not available for this transfer';
		} elseif ($amount < $min_limit || $amount > $max_limit) {
			$message = "minimum payment $min_limit and maximum payment limit $max_limit";
		} elseif ($transfer_amount > $balance) {
			$message = 'Does not have enough money to cover transactions';
		} else {
			$status = true;
			$message = "Remaining balance : $remaining_balance";
		}

		$data['status'] = $status;
		$data['message'] = $message;
		$data['fixed_charge'] = $fixed_charge;
		$data['percentage'] = $percentage;
		$data['percentage_charge'] = $percentage_charge;
		$data['min_limit'] = $min_limit;
		$data['max_limit'] = $max_limit;
		$data['balance'] = $balance;
		$data['transfer_amount'] = $transfer_amount;
		$data['received_amount'] = $received_amount;
		$data['remaining_balance'] = $remaining_balance;
		$data['charge'] = $charge;
		$data['charge_from'] = $charge_from;
		$data['amount'] = $amount;
		$data['currency_id'] = $currency_id;
		$data['currency_limit'] = $limit;
		return $data;
	}
}
