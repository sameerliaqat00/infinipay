<?php

namespace App\Http\Controllers;

use App\Models\ChargesLimit;
use App\Models\Currency;
use App\Models\Escrow;
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

class EscrowController extends Controller
{
	use Notify;

	public function index()
	{
		$userId = Auth::id();
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$escrows = Escrow::with(['sender', 'receiver', 'currency', 'disputable'])
			->where(function ($query) use ($userId) {
				$query->where('sender_id', $userId);
				$query->orWhere('receiver_id', $userId);
			})
			->latest()
			->paginate();
		return view('user.escrow.index', compact('currencies', 'escrows'));
	}

	public function search(Request $request)
	{
		$filterData = $this->_filter($request);
		$search = $filterData['search'];
		$currencies = $filterData['currencies'];
		$userId = $filterData['userId'];
		$escrows = $filterData['escrows']
			->where(function ($query) use ($userId) {
				$query->where('sender_id', $userId)->orWhere('receiver_id', $userId);
			})
			->latest()
			->paginate();
		$escrows->appends($filterData['search']);
		return view('user.escrow.index', compact('search', 'escrows', 'currencies'));
	}

	public function _filter($request)
	{
		$userId = Auth::id();
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$search = $request->all();
		$sent = isset($search['type']) ? preg_match("/sent/", $search['type']) : 0;
		$received = isset($search['type']) ? preg_match("/received/", $search['type']) : 0;
		$created_date = isset($search['created_at']) ? preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']) : 0;

		$escrows = Escrow::with('sender', 'receiver', 'currency')
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
			'escrows' => $escrows,
		];
		return $data;
	}

	public function escrowPaymentView(Request $request, $utr)
	{
		$escrow = Escrow::where('utr', $utr)->first();

		if ($request->isMethod('get')) {
			return view('user.escrow.payment', compact('escrow'));
		} elseif ($request->isMethod('post')) {
			$reqStatus = $request->status;
			$user = Auth::user();
			$message = 'No action has been taken';
			if ($escrow->receiver_id == $user->id && $escrow->status == 1) {
				if ($reqStatus == 2) {
					$escrow->status = $reqStatus;

					$receiver_wallet = updateWallet($escrow->receiver_id, $escrow->currency_id, $escrow->transfer_amount, 0);
					$message = "Transaction payment complete, we securely hold your payments until both the buyer and seller are in agreeance";
					$transaction = new Transaction();
					$transaction->amount = $escrow->amount;
					$transaction->charge = $escrow->charge;
					$transaction->currency_id = $escrow->currency_id;
					$escrow->transactional()->save($transaction);

					$acceptFromUser = $escrow->receiver;
					$params = [
						'sender' => optional($escrow->sender)->name,
						'amount' => getAmount($escrow->amount),
						'currency' => optional($escrow->currency)->code,
						'transaction' => $escrow->utr,
					];
					$action = [
						"link" => route('escrow.index'),
						"icon" => "fa fa-money-bill-alt text-white"
					];
					$firebaseAction = route('escrow.index');
					$this->sendMailSms($acceptFromUser, 'ESCROW_REQUEST_ACCEPT_FROM', $params);
					$this->userPushNotification($acceptFromUser, 'ESCROW_REQUEST_ACCEPT_FROM', $params, $action);
					$this->userFirebasePushNotification($acceptFromUser, 'ESCROW_REQUEST_ACCEPT_FROM', $params, $firebaseAction);

					// send mail to request receiver who accept request
					$acceptByUser = $escrow->sender;
					$params = [
						'amount' => getAmount($escrow->amount),
						'currency' => optional($escrow->currency)->code,
						'transaction' => $escrow->utr,
					];
					$action = [
						"link" => route('escrow.index'),
						"icon" => "fa fa-money-bill-alt text-white"
					];
					$firebaseAction = route('escrow.index');
					$this->sendMailSms($acceptByUser, 'ESCROW_REQUEST_ACCEPT_BY', $params);
					$this->userPushNotification($acceptByUser, 'ESCROW_REQUEST_ACCEPT_BY', $params, $action);
					$this->userFirebasePushNotification($acceptByUser, 'ESCROW_REQUEST_ACCEPT_BY', $params, $firebaseAction);

				} elseif ($reqStatus == 5) {
					$escrow->status = $reqStatus; //0=Pending, 1=generated, 2 = payment done, 3 = sender request to payment disburse, 4 = payment disbursed,5 = cancel, 6= dispute
					$message = "Request has been canceled";

					// send mail to request sender who send request
					$acceptFromUser = $escrow->receiver;
					$params = [
						'sender' => optional($escrow->sender)->name,
						'amount' => getAmount($escrow->amount),
						'currency' => optional($escrow->currency)->code,
						'transaction' => $escrow->utr,
					];
					$action = [
						"link" => route('escrow.index'),
						"icon" => "fa fa-money-bill-alt text-white"
					];
					$firebaseAction = route('escrow.index');
					$this->sendMailSms($acceptFromUser, 'ESCROW_REQUEST_CANCEL_FROM', $params);
					$this->userPushNotification($acceptFromUser, 'ESCROW_REQUEST_CANCEL_FROM', $params, $action);
					$this->userFirebasePushNotification($acceptFromUser, 'ESCROW_REQUEST_CANCEL_FROM', $params, $firebaseAction);

					// send mail to request receiver who cancel request
					$acceptByUser = $escrow->sender;
					$params = [
						'amount' => getAmount($escrow->amount),
						'currency' => optional($escrow->currency)->code,
						'transaction' => $escrow->utr,
					];
					$action = [
						"link" => route('escrow.index'),
						"icon" => "fa fa-money-bill-alt text-white"
					];
					$firebaseAction = route('escrow.index');
					$this->sendMailSms($acceptByUser, 'ESCROW_REQUEST_CANCEL_BY', $params);
					$this->userPushNotification($acceptByUser, 'ESCROW_REQUEST_CANCEL_BY', $params, $action);
					$this->userFirebasePushNotification($acceptByUser, 'ESCROW_REQUEST_CANCEL_BY', $params, $firebaseAction);
				}
			} elseif ($escrow->sender_id == $user->id && $escrow->status == 2) {
				if ($reqStatus == 3) {
					$escrow->status = $reqStatus; //0=Pending, 1=generated, 2 = payment done, 3 = sender request to payment disburse, 4 = payment disbursed,5 = cancel, 6= dispute
					$message = "Request has been submitted";

					// ESCROW_PAYMENT_DISBURSED_REQUEST_FROM
					$receiver = $escrow->receiver;
					$params = [
						'sender' => optional($escrow->sender)->name,
						'amount' => getAmount($escrow->amount),
						'currency' => optional($escrow->currency)->code,
						'transaction' => $escrow->utr,
					];
					$action = [
						"link" => route('escrow.index'),
						"icon" => "fa fa-money-bill-alt text-white"
					];
					$firebaseAction = route('escrow.index');
					$this->sendMailSms($receiver, 'ESCROW_PAYMENT_DISBURSED_REQUEST_FROM', $params);
					$this->userPushNotification($receiver, 'ESCROW_PAYMENT_DISBURSED_REQUEST_FROM', $params, $action);
					$this->userFirebasePushNotification($receiver, 'ESCROW_PAYMENT_DISBURSED_REQUEST_FROM', $params, $firebaseAction);

					// ESCROW_PAYMENT_DISBURSED_REQUEST_BY
					$sender = $escrow->sender;
					$params = [
						'amount' => getAmount($escrow->amount),
						'currency' => optional($escrow->currency)->code,
						'transaction' => $escrow->utr,
					];
					$action = [
						"link" => route('escrow.index'),
						"icon" => "fa fa-money-bill-alt text-white"
					];
					$firebaseAction = route('escrow.index');
					$this->sendMailSms($sender, 'ESCROW_PAYMENT_DISBURSED_REQUEST_BY', $params);
					$this->userPushNotification($sender, 'ESCROW_PAYMENT_DISBURSED_REQUEST_BY', $params, $action);
					$this->userFirebasePushNotification($sender, 'ESCROW_PAYMENT_DISBURSED_REQUEST_BY', $params, $firebaseAction);
				}
			} elseif ($escrow->receiver_id == $user->id && $escrow->status == 3) {
				if ($reqStatus == 4) {
					$escrow->status = $reqStatus; // 0=Pending, 1=generated, 2=payment done, 3=sender request to payment disburse, 4=payment disbursed,5=cancel, 6=dispute
					/*
					 * Add money to Sender Wallet
					 * */
					$sender_wallet = updateWallet($escrow->sender_id, $escrow->currency_id, $escrow->received_amount, 1);
					$message = "Payment has been disbursed";
					$transaction = new Transaction();
					$transaction->amount = $escrow->amount;
					$transaction->charge = $escrow->charge;
					$transaction->currency_id = $escrow->currency_id;
					$escrow->transactional()->save($transaction);

					// ESCROW_PAYMENT_DISBURSED_FROM
					$sender = $escrow->sender;
					$params = [
						'sender' => optional($escrow->receiver)->name,
						'amount' => getAmount($escrow->amount),
						'currency' => optional($escrow->currency)->code,
						'transaction' => $escrow->utr,
					];
					$action = [
						"link" => route('escrow.index'),
						"icon" => "fa fa-money-bill-alt text-white"
					];
					$firebaseAction = route('escrow.index');
					$this->sendMailSms($sender, 'ESCROW_PAYMENT_DISBURSED_FROM', $params);
					$this->userPushNotification($sender, 'ESCROW_PAYMENT_DISBURSED_FROM', $params, $action);
					$this->userFirebasePushNotification($sender, 'ESCROW_PAYMENT_DISBURSED_FROM', $params, $firebaseAction);

					// ESCROW_PAYMENT_DISBURSED_BY
					$receiver = $escrow->receiver;
					$params = [
						'amount' => getAmount($escrow->amount),
						'currency' => optional($escrow->currency)->code,
						'transaction' => $escrow->utr,
					];
					$action = [
						"link" => route('escrow.index'),
						"icon" => "fa fa-money-bill-alt text-white"
					];
					$firebaseAction = route('escrow.index');
					$this->sendMailSms($receiver, 'ESCROW_PAYMENT_DISBURSED_BY', $params);
					$this->userPushNotification($receiver, 'ESCROW_PAYMENT_DISBURSED_BY', $params, $action);
					$this->userFirebasePushNotification($receiver, 'ESCROW_PAYMENT_DISBURSED_BY', $params, $firebaseAction);

				} elseif ($reqStatus == 6) {
					return redirect(route('user.dispute.view', $escrow->utr));
				}
			}
			$escrow->save();

			return redirect(route('escrow.index'))->with('success', $message);
		}
	}

	public function checkAmountValidate($amount, $currency_id, $transaction_type_id, $charge_from)
	{
		//$charge_from :- 0 = sender, 1 = receiver
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

	public function create(Request $request)
	{
		if ($request->isMethod('get')) {
			$currencies = Currency::select('id', 'code', 'name', 'currency_type')->where('is_active', 1)->get();
			$template = Template::where('section_name', 'escrow')->first();
			return view('user.escrow.create', compact('currencies', 'template'));
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());
			$validationRules = [
				'recipient' => 'required|min:4',
				'amount' => 'required|numeric|min:1|not_in:0',
				'currency' => 'required|integer|min:1|not_in:0',
			];
			$validate = Validator::make($purifiedData, $validationRules);
			if ($validate->fails()) {
				return back()->withErrors($validate)->withInput();
			}
			$purifiedData = (object)$purifiedData;
			$amount = $purifiedData->amount;
			$currency_id = $purifiedData->currency;
			$recipient = $purifiedData->recipient;
			$charge_from = 1;
			$checkAmountValidate = $this->checkInitiateAmountValidate($amount, $currency_id, config('transactionType.escrow'), $charge_from);//5 = Escrow
			if (!$checkAmountValidate['status']) {
				return back()
					->withInput()
					->withErrors('amount', $checkAmountValidate['message'])
					->with('alert', $checkAmountValidate['message']);
			}
			$checkRecipientValidate = $this->checkRecipientValidate($recipient);
			if (!$checkRecipientValidate['status']) {
				return back()
					->withInput()
					->withErrors('recipient', $checkRecipientValidate['message'])
					->with('alert', $checkRecipientValidate['message']);
			}

			$receiver = $checkRecipientValidate['receiver'];
			$escrow = new Escrow();
			$escrow->sender_id = Auth::id();
			$escrow->receiver_id = optional($receiver)->id ?? null;
			$escrow->currency_id = $checkAmountValidate['currency_id'];
			$escrow->percentage = $checkAmountValidate['percentage'];
			$escrow->charge_percentage = $checkAmountValidate['percentage_charge']; // amount after calculation percent of charge
			$escrow->charge_fixed = $checkAmountValidate['fixed_charge'];
			$escrow->charge = $checkAmountValidate['charge'];
			$escrow->amount = $checkAmountValidate['amount'];
			$escrow->transfer_amount = $checkAmountValidate['transfer_amount'];
			$escrow->received_amount = $checkAmountValidate['received_amount'];
			$escrow->charge_from = $checkAmountValidate['charge_from']; //0 = Sender, 1 = Receiver
			$escrow->note = $purifiedData->note;
			$escrow->email = optional($receiver)->email ?? $recipient;
			$escrow->status = 0;// 0=Pending,1=accept/hold,2=payment done,3=sender request to payment disburse,4=payment disbursed,5=cancel
			$escrow->utr = (string)Str::uuid();
			$escrow->save();
			return redirect(route('escrow.confirmInit', $escrow->utr));
		}
	}

	public function confirmInit(Request $request, $utr)
	{
		$escrow = Escrow::where('utr', $utr)->first();

		if (!$escrow || $escrow->status != 0) { //Check is transaction found and unpaid
			return redirect(route('escrow.createRequest'))->with('alert', 'Transaction already complete or invalid code');
		}

		$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => Auth::id()]);
		$enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);

		if ($request->isMethod('get')) {
			return view('user.escrow.confirmInit', compact(['utr', 'escrow', 'enable_for']));
		} elseif ($request->isMethod('post')) {
			// Security PIN check and validation
			if (in_array('escrow', $enable_for)) {
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
			$checkAmountValidate = $this->checkAmountValidate($escrow->amount, $escrow->currency_id, config('transactionType.escrow'), $escrow->charge_from);//5=escrow

			if (!$checkAmountValidate['status']) {
				return back()->withInput()->with('alert', $checkAmountValidate['message']);
			}

			$escrow->status = 1; //0=Pending, 1=generated, 2=payment done, 3=sender request to payment disburse, 4=payment disbursed,5=cancel, 6=dispute
			$escrow->save();

			// Email send to request sender
			$senderUser = $escrow->sender;
			$params = [
				'receiver' => optional($escrow->receiver)->name,
				'amount' => getAmount($escrow->amount),
				'currency' => optional($escrow->currency)->code,
				'transaction' => $escrow->utr,
			];

			$action = [
				"link" => route('escrow.index'),
				"icon" => "fa fa-money-bill-alt text-white"
			];
			$firebaseAction = route('escrow.index');
			$this->sendMailSms($senderUser, 'ESCROW_REQUEST_SENDER', $params);
			$this->userPushNotification($senderUser, 'ESCROW_REQUEST_SENDER', $params, $action);
			$this->userFirebasePushNotification($senderUser, 'ESCROW_REQUEST_SENDER', $params, $firebaseAction);

			// Email send to request receiver
			$receiverUser = $escrow->receiver;
			$params = [
				'sender' => Auth::user()->name,
				'amount' => getAmount($escrow->amount),
				'currency' => optional($escrow->currency)->code,
				'transaction' => $escrow->utr,
			];

			$url = route('escrow.paymentView', $utr);
			$action = [
				"link" => $url,
				"icon" => "fa fa-money-bill-alt text-white"
			];

			$this->userPushNotification($receiverUser, 'ESCROW_REQUEST_RECEIVER', $params, $action);
			$params['links'] = $url;
			$this->sendMailSms($receiverUser, 'ESCROW_REQUEST_RECEIVER', $params);

			return redirect(route('escrow.index'))->with("success", "Your escrow has been initiated successfully");
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
			$data['message'] = 'Transfer not allowed to self account';
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

		if ($min_limit == 0 && $max_limit == 0) {
			$message = "Payment method not available for this transaction";
		} elseif ($amount < $min_limit || $amount > $max_limit) {
			$message = "Minimum payment $min_limit and maximum payment limit $max_limit";
		} else {
			$status = true;
			$message = "";
		}

		if ($charge_from) {
			$transfer_amount = $amount;
			$received_amount = getAmount($amount - $charge, $limit);
		} else {
			$transfer_amount = getAmount($amount + $charge, $limit);
			$received_amount = $amount;
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
		$data['remaining_balance'] = 0;
		$data['charge'] = $charge;
		$data['charge_from'] = $charge_from;
		$data['amount'] = $amount;
		$data['currency_id'] = $currency_id;
		return $data;
	}
}
