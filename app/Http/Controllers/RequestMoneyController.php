<?php

namespace App\Http\Controllers;

use App\Models\ChargesLimit;
use App\Models\Currency;
use App\Models\RequestMoney;
use App\Models\Template;
use App\Models\Transaction;
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

class RequestMoneyController extends Controller
{
	use Notify;

	public function initialize(Request $request)
	{
		$user = Auth::user();
		$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
		$enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);
		if ($request->isMethod('get')) {
			$currencies = Currency::select('id', 'code', 'name', 'currency_type')->where('is_active', 1)->get();
			$template = Template::where('section_name', 'request-money')->first();
			return view('user.requestMoney.create', compact('currencies', 'enable_for', 'template'));
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());
			$validationRules = [
				'recipient' => 'required|min:4',
				'amount' => 'required|numeric|min:1|not_in:0',
				'currency' => 'required|integer|min:1|not_in:0'
			];
			if (in_array('request', $enable_for)) {
				$validationRules['security_pin'] = 'required|integer|digits:5';
			}
			$validate = Validator::make($purifiedData, $validationRules);
			if ($validate->fails()) {
				return back()->withErrors($validate)->withInput();
			}

			if (in_array('request', $enable_for)) {
				if (!Hash::check($purifiedData['security_pin'], $twoFactorSetting->security_pin)) {
					return back()->withErrors(['security_pin' => 'You have entered an incorrect PIN'])->with('alert', 'You have entered an incorrect PIN')->withInput();
				}
			}

			$purifiedData = (object)$purifiedData;

			$amount = $purifiedData->amount;
			$currency_id = $purifiedData->currency;
			$recipient = $purifiedData->recipient;
			$charge_from = 0;

			$checkAmountValidate = $this->checkInitiateAmountValidate($amount, $currency_id, config('transactionType.request'), $charge_from);//2 = Request

			if (!$checkAmountValidate['status']) {
				return back()->withInput()->with('alert', $checkAmountValidate['message']);
			}

			$checkRecipientValidate = $this->checkRecipientValidate($recipient);
			if (!$checkRecipientValidate['status']) {
				return back()->withInput()->with('alert', $checkRecipientValidate['message']);
			}
			$receiver = $checkRecipientValidate['receiver'];
			$requestMoney = new RequestMoney();
			$requestMoney->sender_id = $user->id;
			$requestMoney->receiver_id = $receiver->id;
			$requestMoney->currency_id = $checkAmountValidate['currency_id'];
			$requestMoney->percentage = $checkAmountValidate['percentage'];
			$requestMoney->charge_percentage = $checkAmountValidate['percentage_charge']; // amount after calculation percent of charge
			$requestMoney->charge_fixed = $checkAmountValidate['fixed_charge'];
			$requestMoney->charge = $checkAmountValidate['charge'];
			$requestMoney->amount = $checkAmountValidate['amount'];
			$requestMoney->transfer_amount = $checkAmountValidate['transfer_amount'];
			$requestMoney->received_amount = $checkAmountValidate['received_amount'];
			$requestMoney->charge_from = $checkAmountValidate['charge_from']; //0 = Sender, 1 = Receiver
			$requestMoney->note = $purifiedData->note;
			$requestMoney->email = $receiver->email;
			$requestMoney->status = 0;// 1 = success, 0 = pending
			$requestMoney->utr = (string)Str::uuid();
			$requestMoney->save();

			$receivedUser = $requestMoney->receiver;
			$params = [
				'sender' => $user->name,
				'amount' => getAmount($requestMoney->amount),
				'currency' => $requestMoney->currency->code,
				'transaction' => $requestMoney->utr,
			];

			$action = [
				"link" => route('requestMoney.index'),
				"icon" => "fa fa-money-bill-alt text-white"
			];
			$firebaseAction = route('requestMoney.index');
			$this->sendMailSms($receivedUser, 'REQUEST_MONEY_INIT', $params);
			$this->userPushNotification($receivedUser, 'REQUEST_MONEY_INIT', $params, $action);
			$this->userFirebasePushNotification($receivedUser, 'REQUEST_MONEY_INIT', $params, $firebaseAction);

			return back()->with('success', 'Request initiated successfully');
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
		$user = Auth::user();
		$field = filter_var($recipient, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
		$receiver = User::where($field, $recipient)->first();

		if ($receiver && $receiver->id == $user->id) {
			$data['status'] = false;
			$data['message'] = 'Transfer not allowed to self email';
		} elseif ($receiver && $receiver->id != $user->id) {
			$data['status'] = true;
			$data['message'] = "User found. Are you looking for $receiver->name ?";
			$data['receiver'] = $receiver;
		} else {
			$data['status'] = false;
			$data['message'] = 'No user found';
		}
		return $data;
	}

	public function checkInitiateAmount(Request $request)
	{
		if ($request->ajax()) {
			$amount = $request->amount;
			$currency_id = $request->currency_id;
			$transaction_type_id = $request->transaction_type_id;
			$charge_from = $request->charge_from;
			$data = $this->checkInitiateAmountValidate($amount, $currency_id, $transaction_type_id, $charge_from);
			return response()->json($data);
		}
	}

	public function checkInitiateAmountValidate($amount, $currency_id, $transaction_type_id, $charge_from)
	{
		$chargesLimit = ChargesLimit::with('currency')->where(['currency_id' => $currency_id, 'transaction_type_id' => $transaction_type_id, 'is_active' => 1])->first();
		$limit = optional($chargesLimit->currency)->currency_type == 0 ? 8 : 2;

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

		if ($amount < $min_limit || $amount > $max_limit) {
			$message = "minimum payment $min_limit and maximum payment limit $max_limit";
		} else {
			$status = true;
			$message = "";
		}

		$data['status'] = $status;
		$data['message'] = $message;
		$data['fixed_charge'] = $fixed_charge;
		$data['percentage'] = $percentage;
		$data['percentage_charge'] = $percentage_charge;
		$data['min_limit'] = $min_limit;
		$data['max_limit'] = $max_limit;
		$data['balance'] = 0;
		$data['transfer_amount'] = 0;
		$data['received_amount'] = 0;
		$data['remaining_balance'] = 0;
		$data['charge'] = $charge;
		$data['charge_from'] = $charge_from;
		$data['amount'] = $amount;
		$data['currency_id'] = $currency_id;
		$data['currency_limit'] = $limit;
		return $data;
	}

	public function checkRequestMoney(Request $request, $utr)
	{
		$requestMoney = RequestMoney::with(['sender', 'receiver', 'currency'])->where('utr', $utr)->first();
		$user = Auth::user();

		//Check if transaction not found or any action done
		if (!$requestMoney || $requestMoney->status != 0) {
			return back()->with('alert', 'Not Allowed');
		}

		// check if other try to attempt confirm payment with out login user
		if ($requestMoney->receiver_id != $user->id) {
			return back()->with('alert', 'Not Allowed');
		}

		if ($request->isMethod('get')) {
			return view('user.requestMoney.confirmCreate', compact(['utr', 'requestMoney']));
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());
			$validationRules = [
				'amount' => 'required|numeric|min:1|not_in:0',
				'charge_from' => 'nullable|integer|not_in:0',
			];

			$validate = Validator::make($purifiedData, $validationRules);
			if ($validate->fails()) {
				return back()->withErrors($validate)->withInput();
			}
			$purifiedData = (object)$purifiedData;
			$amount = $purifiedData->amount;
			$currency_id = $requestMoney->currency_id;
			$recipient = $requestMoney->sender->email;
			$charge_from = isset($purifiedData->charge_from);

			$checkAmountValidate = $this->checkAmountValidate($amount, $currency_id, config('transactionType.request'), $charge_from);//1 = transfer

			if (!$checkAmountValidate['status']) {
				return back()->withInput()->with('alert', $checkAmountValidate['message']);
			}

			$checkRecipientValidate = $this->checkRecipientValidate($recipient);
			if (!$checkRecipientValidate['status']) {
				return back()->withInput()->with('alert', $checkRecipientValidate['message']);
			}

			$requestMoney->currency_id = $checkAmountValidate['currency_id'];
			$requestMoney->percentage = $checkAmountValidate['percentage'];
			$requestMoney->charge_percentage = $checkAmountValidate['percentage_charge']; // amount after calculation percent of charge
			$requestMoney->charge_fixed = $checkAmountValidate['fixed_charge'];
			$requestMoney->charge = $checkAmountValidate['charge'];
			$requestMoney->amount = $checkAmountValidate['amount'];
			$requestMoney->transfer_amount = $checkAmountValidate['transfer_amount'];
			$requestMoney->received_amount = $checkAmountValidate['received_amount'];
			$requestMoney->charge_from = $checkAmountValidate['charge_from']; //0 = Sender, 1 = Receiver
			$requestMoney->save();
			return redirect(route('requestMoney.confirm', [$requestMoney->utr]))->with('success', 'Request check successfully');
		}
	}

	public function confirmRequestMoney(Request $request, $utr)
	{
		$requestMoney = RequestMoney::with(['sender', 'receiver', 'currency'])->where('utr', $utr)->first();
		$user = Auth::user();

		// check if other try to attempt confirm payment with out login user
		if ($requestMoney->receiver_id != $user->id) {
			return back()->with('alert', 'Not Allowed');
		}
		//Check if transaction not found or any action done
		if (!$requestMoney || $requestMoney->status != 0) {
			return back()->with('alert', 'Not Allowed');
		}

		$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
		$enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);

		if ($request->isMethod('get')) {
			return view('user.requestMoney.confirm', compact(['utr', 'requestMoney', 'enable_for']));
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

			$checkAmountValidate = $this->checkAmountValidate($requestMoney->amount, $requestMoney->currency_id, config('transactionType.request'), $requestMoney->charge_from);//1 = transfer
			if (!$checkAmountValidate['status']) {
				return back()->withInput()->with('alert', $checkAmountValidate['message']);
			}
			$checkRecipientValidate = $this->checkRecipientValidate($requestMoney->sender->email);
			if (!$checkRecipientValidate['status']) {
				return back()->withInput()->with('alert', $checkRecipientValidate['message']);
			}

			/*
			 * Deduct money from Sender Wallet
			 * */
			$sender_wallet = updateWallet($requestMoney->receiver_id, $requestMoney->currency_id, $requestMoney->transfer_amount, 0);
			/*
			 * Add money to receiver wallet
			 * */
			$receiver_wallet = updateWallet($requestMoney->sender_id, $requestMoney->currency_id, $requestMoney->received_amount, 1);

			$transaction = new Transaction();
			$transaction->amount = $requestMoney->amount;
			$transaction->charge = $requestMoney->charge;
			$transaction->currency_id = $requestMoney->currency_id;
			$requestMoney->transactional()->save($transaction);
			$requestMoney->status = 1;
			$requestMoney->save();

			$receivedUser = $requestMoney->sender;
			$params = [
				'sender' => $user->name,
				'amount' => getAmount($requestMoney->amount),
				'currency' => $requestMoney->currency->code,
				'transaction' => $requestMoney->utr,
			];

			$action = [
				"link" => route('requestMoney.index'),
				"icon" => "fa fa-money-bill-alt text-white"
			];
			$firebaseAction = route('requestMoney.index');
			$this->sendMailSms($receivedUser, 'REQUEST_MONEY_CONFIRM', $params);
			$this->userPushNotification($receivedUser, 'REQUEST_MONEY_CONFIRM', $params, $action);
			$this->userFirebasePushNotification($receivedUser, 'REQUEST_MONEY_CONFIRM', $params, $firebaseAction);

			return redirect(route('transfer.initialize'))->with("success", "Your transfer has been submitted your remaining amount of money $sender_wallet");
		}
	}

	public function cancelRequestMoney($utr)
	{
		$requestMoney = RequestMoney::with(['sender', 'receiver', 'currency'])->where('utr', $utr)->first();
		$user = Auth::user();

		// check if other try to attempt confirm payment with out login user
		if (!($requestMoney->receiver_id == $user->id || $requestMoney->sender_id == $user->id)) {
			return back()->with('alert', 'Not Allowed');
		}
		//Check if transaction not found or any action done
		if (!$requestMoney || $requestMoney->status != 0) {
			return back()->with('alert', 'Not Allowed');
		}

		$requestMoney->status = 2;
		$requestMoney->save();

		$receivedUser = ($user->id == $requestMoney->sender_id) ? $requestMoney->receiver : $requestMoney->sender;

		$params = [
			'sender' => $user->name,
			'amount' => getAmount($requestMoney->amount),
			'currency' => $requestMoney->currency->code,
			'transaction' => $requestMoney->utr,
		];

		$action = [
			"link" => route('requestMoney.index'),
			"icon" => "fa fa-money-bill-alt text-white"
		];

		$this->sendMailSms($receivedUser, 'REQUEST_MONEY_CANCEL', $params);
		$this->userPushNotification($receivedUser, 'REQUEST_MONEY_CANCEL', $params, $action);

		return redirect(route('requestMoney.index'))->with("success", "Your transfer has been canceled");
	}

	public function checkAmountValidate($amount, $currency_id, $transaction_type_id, $charge_from)
	{
		$chargesLimit = ChargesLimit::where(['currency_id' => $currency_id, 'transaction_type_id' => $transaction_type_id, 'is_active' => 1])->first();
		$wallet = Wallet::firstOrCreate(['user_id' => Auth::id(), 'currency_id' => $currency_id]);

		$balance = $wallet->balance;
		$status = false;
		$charge = 0;
		$min_limit = 0;
		$max_limit = 0;
		$fixed_charge = 0;
		$percentage = 0;
		$percentage_charge = 0;

		if ($chargesLimit) {
			$percentage = $chargesLimit->percentage_charge;
			$percentage_charge = ($amount * $percentage) / 100;
			$fixed_charge = $chargesLimit->fixed_charge;
			$min_limit = $chargesLimit->min_limit;
			$max_limit = $chargesLimit->max_limit;
			$charge = $percentage_charge + $fixed_charge;
		}

		if ($charge_from) {
			$transfer_amount = $amount;
			$received_amount = $amount - $charge;
		} else {
			$transfer_amount = $amount + $charge;
			$received_amount = $amount;
		}

		$remaining_balance = $balance - $transfer_amount;

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

		return $data;
	}

	public function index()
	{
		$userId = Auth::id();
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$requestMoney = RequestMoney::with(['sender', 'receiver', 'currency'])->where(['sender_id' => $userId])->orwhere(['receiver_id' => $userId])->paginate();
		return view('user.requestMoney.index', compact('currencies', 'requestMoney'));
	}

	public function search(Request $request)
	{
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$search = $request->all();
		$sent = isset($search['type']) ? preg_match("/sent/", $search['type']) : 0;
		$received = isset($search['type']) ? preg_match("/received/", $search['type']) : 0;
		$created_date = isset($search['created_at']) ? preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']) : 0;
		$userId = Auth::id();

		$requestMoney = RequestMoney::with('sender', 'receiver', 'currency')
			->where(function ($query) use ($userId) {
				$query->where(['sender_id' => $userId])->orwhere(['receiver_id' => $userId]);
			})
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
			->latest()
			->paginate();
		$requestMoney->appends($search);
		return view('user.requestMoney.index', compact('search', 'requestMoney', 'currencies'));
	}
}
