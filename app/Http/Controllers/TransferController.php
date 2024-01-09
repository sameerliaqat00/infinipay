<?php

namespace App\Http\Controllers;

use App\Models\ChargesLimit;
use App\Models\Currency;
use App\Models\Template;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\TwoFactorSetting;
use App\Models\User;
use App\Models\Wallet;
use App\Traits\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Validator;

class TransferController extends Controller
{
	use Notify;

	public function index()
	{
		$userId = Auth::id();
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$transfers = Transfer::with(['sender', 'receiver', 'currency'])
			->where(function ($query) use ($userId) {
				$query->where('sender_id', '=', $userId);
				$query->orWhere('receiver_id', '=', $userId);
			})
			->latest()->paginate();
		return view('user.transfer.index', compact('transfers', 'currencies'));
	}

	public function search(Request $request)
	{
		$filterData = $this->_filter($request);
		$search = $filterData['search'];
		$currencies = $filterData['currencies'];
		$userId = $filterData['userId'];
		$transfers = $filterData['transfers']
			->where(function ($query) use ($userId) {
				$query->where('sender_id', '=', $userId)->orWhere('receiver_id', '=', $userId);
			})
			->latest()
			->paginate();
		$transfers->appends($filterData['search']);
		return view('user.transfer.index', compact('search', 'transfers', 'currencies'));
	}


	public function _filter($request)
	{
		$userId = Auth::id();
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$search = $request->all();
		$sent = isset($search['type']) ? preg_match("/sent/", $search['type']) : 0;
		$received = isset($search['type']) ? preg_match("/received/", $search['type']) : 0;
		$created_date = isset($search['created_at']) ? preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']) : 0;

		$transfers = Transfer::with('sender', 'receiver', 'currency')
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
			'transfers' => $transfers,
		];
		return $data;
	}

	public function initialize(Request $request)
	{
		if ($request->isMethod('get')) {
			$currencies = Currency::select('id', 'code', 'name', 'currency_type')->where('is_active', 1)->get();
			$template = Template::where('section_name', 'send-money')->first();
			return view('user.transfer.create', compact('currencies', 'template'));
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());
			$validationRules = [
				'recipient' => 'required|min:4',
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
			$recipient = $purifiedData->recipient;
			$charge_from = isset($purifiedData->charge_from);

			$checkAmountValidate = $this->checkAmountValidate($amount, $currency_id, config('transactionType.transfer'), $charge_from);//1 = transfer

			if (!$checkAmountValidate['status']) {
				return back()->withInput()->with('alert', $checkAmountValidate['message']);
			}

			$checkRecipientValidate = $this->checkRecipientValidate($recipient);
			if (!$checkRecipientValidate['status']) {
				return back()->withInput()->with('alert', $checkRecipientValidate['message']);
			}
			$receiver = $checkRecipientValidate['receiver'];
			$transfer = new Transfer();
			$transfer->sender_id = Auth::id();
			$transfer->receiver_id = $receiver->id;
			$transfer->currency_id = $checkAmountValidate['currency_id'];
			$transfer->percentage = $checkAmountValidate['percentage'];
			$transfer->charge_percentage = $checkAmountValidate['percentage_charge']; // amount after calculation percent of charge
			$transfer->charge_fixed = $checkAmountValidate['fixed_charge'];
			$transfer->charge = $checkAmountValidate['charge'];
			$transfer->amount = $checkAmountValidate['amount'];
			$transfer->transfer_amount = $checkAmountValidate['transfer_amount'];
			$transfer->received_amount = $checkAmountValidate['received_amount'];
			$transfer->charge_from = $checkAmountValidate['charge_from']; //0 = Sender, 1 = Receiver
			$transfer->note = $purifiedData->note;
			$transfer->email = $receiver->email;
			$transfer->status = 0;// 1 = success, 0 = pending
			$transfer->utr = (string)Str::uuid();
			$transfer->save();

			return redirect(route('transfer.confirm', $transfer->utr))->with('success', 'Transfer initiated successfully');
		}
	}

	public function confirmTransfer(Request $request, $utr)
	{
		$user = Auth::user();
		$transfer = Transfer::with('sender', 'receiver', 'currency')->where('utr', $utr)->first();
		if (!$transfer || $transfer->status) { //Check is transaction found and unpaid
			return redirect(route('transfer.initialize'))->with('success', 'Transaction already complete');
		}

		$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
		$enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);

		if ($request->isMethod('get')) {
			return view('user.transfer.confirm', compact(['utr', 'transfer', 'enable_for']));
		} elseif ($request->isMethod('post')) {
			// Security PIN check and validation
			if (in_array('transfer', $enable_for)) {
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

			$checkAmountValidate = $this->checkAmountValidate($transfer->amount, $transfer->currency_id, config('transactionType.transfer'), $transfer->charge_from);//1 = transfer
			if (!$checkAmountValidate['status']) {
				return back()->withInput()->with('alert', $checkAmountValidate['message']);
			}
			$checkRecipientValidate = $this->checkRecipientValidate($transfer->email);
			if (!$checkRecipientValidate['status']) {
				return back()->withInput()->with('alert', $checkRecipientValidate['message']);
			}

			/*
			 * Deduct money from Sender Wallet
			 * */
			$sender_wallet = updateWallet($transfer->sender_id, $transfer->currency_id, $transfer->transfer_amount, 0);
			/*
			 * Add money to receiver wallet
			 * */
			$receiver_wallet = updateWallet($transfer->receiver_id, $transfer->currency_id, $transfer->received_amount, 1);

			$transaction = new Transaction();
			$transaction->amount = $transfer->amount;
			$transaction->charge = $transfer->charge;
			$transaction->currency_id = $transfer->currency_id;
			$transfer->transactional()->save($transaction);
			$transfer->status = 1;
			$transfer->save();

			// TRANSFER_TO
			$receivedUserTO = $transfer->receiver;
			$paramsTO = [
				'sender' => $user->name,
				'amount' => getAmount($transfer->received_amount),
				'currency' => optional($transfer->currency)->code,
				'transaction' => $transfer->utr,
			];
			$action = [
				"link" => route('transfer.index'),
				"icon" => "fa fa-money-bill-alt text-white"
			];
			$firebaseAction = route('transfer.index');
			$this->sendMailSms($receivedUserTO, 'TRANSFER_TO', $paramsTO);
			$this->userPushNotification($receivedUserTO, 'TRANSFER_TO', $paramsTO, $action);
			$this->userFirebasePushNotification($receivedUserTO, 'TRANSFER_TO', $paramsTO, $firebaseAction);

			// TRANSFER_FROM
			$receivedUserFrom = $user;
			$paramsFrom = [
				'receiver' => optional($transfer->receiver)->name,
				'amount' => getAmount($transfer->received_amount),
				'currency' => optional($transfer->currency)->code,
				'transaction' => $transfer->utr,
			];

			$this->sendMailSms($receivedUserFrom, 'TRANSFER_FROM', $paramsFrom);
			$this->userPushNotification($receivedUserFrom, 'TRANSFER_FROM', $paramsFrom, $action);
			$this->userFirebasePushNotification($receivedUserFrom, 'TRANSFER_FROM', $paramsFrom, $firebaseAction);

			return redirect(route('transfer.index'))->with("success", "Your transfer has been submitted your remaining amount of money $sender_wallet");
		}
	}

	public function checkRecipient(Request $request)
	{
		if ($request->ajax()) {
			$data = $this->checkRecipientValidate($request->recipient);
			return response()->json($data);
		}
	}

	public function checkRecipientValidate($recipient)
	{
		$receiver = User::where('username', $recipient)
			->orWhere('email', $recipient)
			->first();

		if ($receiver && $receiver->id == Auth::id()) {
			$data['status'] = false;
			$data['message'] = 'Transfer not allowed to self email';
		} elseif ($receiver && $receiver->id != Auth::id()) {
			$data['status'] = true;
			$data['message'] = "User found. Are you looking for $receiver->name ?";
			$data['receiver'] = $receiver;
		} else {
			$data['status'] = false;
			$data['message'] = 'No user found';
		}
		return $data;
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
			$message = 'Does not have enough money to transfer';
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
