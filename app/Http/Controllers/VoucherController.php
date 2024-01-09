<?php

namespace App\Http\Controllers;

use App\Models\ChargesLimit;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\Gateway;
use App\Models\Template;
use App\Models\Transaction;
use App\Models\TwoFactorSetting;
use App\Models\User;
use App\Models\Voucher;
use App\Models\Wallet;
use App\Traits\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Validator;

class VoucherController extends Controller
{
	use Notify;

	public function index()
	{
		$userId = Auth::id();
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$vouchers = Voucher::with(['sender', 'receiver', 'currency'])
			->where(function ($query) use ($userId) {
				$query->where('sender_id', $userId);
				$query->orWhere('receiver_id', $userId);
			})
			->latest()
			->paginate();
		return view('user.voucher.index', compact('currencies', 'vouchers'));
	}

	public function search(Request $request)
	{
		$filterData = $this->_filter($request);
		$search = $filterData['search'];
		$currencies = $filterData['currencies'];
		$userId = $filterData['userId'];

		$vouchers = $filterData['vouchers']
			->where(function ($query) use ($userId) {
				$query->where('sender_id', $userId)->orWhere('receiver_id', $userId);
			})
			->latest()
			->paginate();
		$vouchers->appends($filterData['search']);
		return view('user.voucher.index', compact('search', 'vouchers', 'currencies'));
	}

	public function _filter($request)
	{
		$userId = Auth::id();
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$search = $request->all();
		$sent = isset($search['type']) ? preg_match("/sent/", $search['type']) : 0;
		$received = isset($search['type']) ? preg_match("/received/", $search['type']) : 0;
		$created_date = isset($search['created_at']) ? preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']) : 0;

		$vouchers = Voucher::with('sender', 'receiver', 'currency')
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
			'vouchers' => $vouchers,
		];
		return $data;
	}

	public function voucherPaymentPublicView(Request $request, $utr)
	{
		$voucher = Voucher::where('utr', $utr)->first();

		abort_if(!$voucher, '404');

		if ($request->isMethod('get')) {
			return view('user.voucher.publicPayment', compact('voucher'));
		} elseif ($request->isMethod('post')) {

			$reqStatus = $request->status;

			if ($voucher->status == 1 && ($reqStatus == 2 || $reqStatus == 5)) {
				if ($reqStatus == 5) {
					$voucher->status = $reqStatus;
					$voucher->save();
					return redirect(route('home'))->with('success', 'Transaction canceled');
				} else {
					return redirect(route('voucher.public.payment', $utr));
				}
			}
		}
	}

	public function create(Request $request)
	{
		if ($request->isMethod('get')) {
			$data['currencies'] = Currency::select('id', 'code', 'name', 'currency_type')->where('is_active', 1)->get();
			$data['template'] = Template::where('section_name', 'voucher-payment')->first();
			return view('user.voucher.create', $data);
		} elseif ($request->isMethod('post')) {
			$allowUser = basicControl()->allowUser;
			$purifiedData = Purify::clean($request->all());
			$validationRules = [
				'recipient' => 'required|min:4',
				'amount' => 'required|numeric|min:1|not_in:0',
				'currency' => 'required|integer|min:1|not_in:0',
			];
			if (!$allowUser) {
				$validationRules['recipient'] = 'required|email';
			}

			$validate = Validator::make($purifiedData, $validationRules);
			if ($validate->fails()) {
				return back()->withErrors($validate)->withInput();
			}
			$purifiedData = (object)$purifiedData;

			$amount = $purifiedData->amount;
			$currency_id = $purifiedData->currency;
			$recipient = $purifiedData->recipient;
			$charge_from = 1;

			$checkAmountValidate = $this->checkInitiateAmountValidate($amount, $currency_id, config('transactionType.voucher'), $charge_from);//6 = voucher

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
			$voucher = new Voucher();
			$voucher->sender_id = Auth::id();
			$voucher->receiver_id = optional($receiver)->id ?? null;
			$voucher->currency_id = $checkAmountValidate['currency_id'];
			$voucher->percentage = $checkAmountValidate['percentage'];
			$voucher->charge_percentage = $checkAmountValidate['percentage_charge']; // amount after calculation percent of charge
			$voucher->charge_fixed = $checkAmountValidate['fixed_charge'];
			$voucher->charge = $checkAmountValidate['charge'];
			$voucher->amount = $checkAmountValidate['amount'];
			$voucher->transfer_amount = $checkAmountValidate['transfer_amount'];
			$voucher->received_amount = $checkAmountValidate['received_amount'];
			$voucher->charge_from = $checkAmountValidate['charge_from']; //0 = Sender, 1 = Receiver
			$voucher->note = $purifiedData->note;
			$voucher->email = optional($receiver)->email ?? $recipient;
			$voucher->status = 0;// 0=Pending, 1=generate, 2=payment done, 5=cancel
			$voucher->utr = (string)Str::uuid();
			$voucher->save();
			return redirect(route('voucher.confirmInit', $voucher->utr))->with('success', 'Initiated successfully');
		}
	}

	public function confirmInit(Request $request, $utr)
	{
		$voucher = Voucher::where('utr', $utr)->first();
		$user = Auth::user();

		if (!$voucher || $voucher->status != 0) {
			return redirect(route('voucher.createRequest'))->with('alert', 'Transaction already complete or invalid code');
		}

		$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
		$enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);

		if ($request->isMethod('get')) {
			return view('user.voucher.confirmInit', compact(['utr', 'voucher', 'enable_for']));
		} elseif ($request->isMethod('post')) {
			if (in_array('voucher', $enable_for)) {
				$purifiedData = Purify::clean($request->all());
				$validationRules = [
					'security_pin' => 'required|integer|digits:5',
				];
				$validate = Validator::make($purifiedData, $validationRules);

				if ($validate->fails()) {
					return back()->withErrors($validate)->withInput();
				}
				if (!Hash::check($purifiedData['security_pin'], $twoFactorSetting->security_pin)) {
					return back()->withErrors(['security_pin' => 'You have entered an incorrect PIN'])->withInput();
				}
			}
			$checkAmountValidate = $this->checkInitiateAmountValidate($voucher->amount, $voucher->currency_id, config('transactionType.voucher'), $voucher->charge_from);//6=voucher

			if (!$checkAmountValidate['status']) {
				return back()->withInput()->with('alert', $checkAmountValidate['message']);
			}

			$voucher->status = 1;
			$voucher->save();

			if (is_null($voucher->receiver_id)) {
				$url = route('voucher.paymentPublicView', $utr);
				$receivedUser = new User();
				$receivedUser->name = 'Concern';
				$receivedUser->email = $voucher->email;
				$receiver = $voucher->email;
			} else {
				$receivedUser = $voucher->receiver;
				$receiver = optional($voucher->receiver)->name;
				$url = route('voucher.paymentView', $utr);
			}

			$params = [
				'sender' => $user->name,
				'amount' => getAmount($voucher->amount),
				'currency' => optional($voucher->currency)->code,
				'transaction' => $voucher->utr,
				'link' => $url,
			];

			$action = [
				"link" => $url,
				"icon" => "fa fa-money-bill-alt text-white"
			];
			$firebaseAction = $url;
			if (is_null($voucher->receiver_id)) {
				$this->mail($receivedUser, 'VOUCHER_PAYMENT_REQUEST_TO', $params);
			} else {
				$this->sendMailSms($receivedUser, 'VOUCHER_PAYMENT_REQUEST_TO', $params);
				$this->userPushNotification($receivedUser, 'VOUCHER_PAYMENT_REQUEST_TO', $params, $action);
				$this->userFirebasePushNotification($receivedUser, 'VOUCHER_PAYMENT_REQUEST_TO', $params, $firebaseAction);
			}

			$params = [
				'receiver' => $receiver,
				'amount' => getAmount($voucher->amount),
				'currency' => optional($voucher->currency)->code,
				'transaction' => $voucher->utr,
			];
			$action = [
				"link" => route('voucher.index'),
				"icon" => "fa fa-money-bill-alt text-white"
			];
			$firebaseAction = route('voucher.index');
			$this->sendMailSms($user, 'VOUCHER_PAYMENT_REQUEST_FROM', $params);
			$this->userPushNotification($user, 'VOUCHER_PAYMENT_REQUEST_FROM', $params, $action);
			$this->userFirebasePushNotification($user, 'VOUCHER_PAYMENT_REQUEST_FROM', $params, $firebaseAction);

			return redirect(route('voucher.index'))->with("success", "Your Voucher has been initiated successfully");
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
		$field = filter_var($recipient, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

		if (basicControl()->allowUser) {
			$receiver = User::where($field, $recipient)->first();

			if ($receiver && $receiver->id == Auth::id()) {
				$data['status'] = false;
				$data['message'] = 'Transfer not allowed to self email';
			} elseif ($receiver && $receiver->id != Auth::id()) {
				$data['status'] = true;
				$data['message'] = "User found. Are you looking for $receiver->name ?";
				$data['receiver'] = $receiver;
			} elseif (!$receiver && $field == 'email') {
				$data['status'] = true;
				$data['message'] = '';
				$data['receiver'] = $receiver;
			} else {
				$data['status'] = false;
				$data['message'] = 'No user found';
			}
		} else {
			if ($field == 'email') {
				$receiver = User::where($field, $recipient)->first();
				if ($receiver) {
					$requestMoneyUrl = route('requestMoney.initialize');
					$data['status'] = false;
					$data['message'] = "User exist, voucher creation not allowed to existing user. Go to  <a href=$requestMoneyUrl>Request money</a>";
				} else {
					$data['status'] = true;
					$data['message'] = '';
					$data['receiver'] = $receiver;
				}
			} else {
				$data['status'] = false;
				$data['message'] = 'Please Enter a valid email';
			}
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

		if ($charge_from) {
			$transfer_amount = $amount;
			$received_amount = getAmount($amount - $charge, $limit);
		} else {
			$transfer_amount = getAmount($amount + $charge, $limit);
			$received_amount = $amount;
		}

		if ($wallet->is_active != 1) {
			$message = 'Currency not available for this transfer';
		} elseif ($amount < $min_limit || $amount > $max_limit) {
			$message = "minimum payment $min_limit and maximum payment limit $max_limit";
		} elseif ($received_amount < $min_limit) {
			$message = "Amount should not be less than $charge";
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
		$data['balance'] = $balance;
		$data['transfer_amount'] = $transfer_amount;
		$data['received_amount'] = $received_amount;
		$data['remaining_balance'] = 0;
		$data['charge'] = $charge;
		$data['charge_from'] = $charge_from;
		$data['amount'] = $amount;
		$data['currency_id'] = $currency_id;
		$data['currency_limit'] = $limit;
		return $data;
	}

	public function voucherPublicPayment(Request $request, $utr)
	{
		$voucher = Voucher::doesntHave('successDepositable')->where('utr', $utr)->first();

		if (!$voucher || $voucher->status != 1)
			abort(404);

		if ($request->isMethod('get')) {
			$methods = Gateway::orderBy('sort_by', 'ASC')->get();
			return view('user.voucher.payment', compact('methods', 'voucher'));
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());
			$validationRules = [
				'amount' => 'required|numeric|min:1|not_in:0',
				'currency' => 'required|integer|min:1|not_in:0',
				'methodId' => 'required|integer|min:1|not_in:0',
			];

			$validate = Validator::make($purifiedData, $validationRules);
			if ($validate->fails()) {
				return back()->withErrors($validate)->withInput();
			}

			$purifiedData = (object)$purifiedData;
			$amount = $purifiedData->amount;
			$currency_id = $purifiedData->currency;
			$methodId = $purifiedData->methodId;

			$checkAmountValidate = $this->checkAmountValidate($amount, $currency_id, config('transactionType.deposit'), $methodId);//7 = deposit

			if (!$checkAmountValidate['status']) {
				return back()->withInput()->with('alert', $checkAmountValidate['message']);
			}

			$method = Gateway::findOrFail($methodId);

			$deposit = new Deposit();
			$deposit->currency_id = $currency_id;
			$deposit->payment_method_id = $methodId;
			$deposit->amount = $amount;
			$deposit->charges_limit_id = $checkAmountValidate['charges_limit_id'];
			$deposit->percentage = $checkAmountValidate['percentage'];
			$deposit->charge_percentage = $checkAmountValidate['percentage_charge'];
			$deposit->charge_fixed = $checkAmountValidate['fixed_charge'];
			$deposit->charge = $checkAmountValidate['charge'];
			$deposit->payable_amount = $checkAmountValidate['payable_amount'] * $checkAmountValidate['convention_rate'];
			$deposit->utr = Str::random(16);
			$deposit->status = 0;// 1 = success, 0 = pending
			$deposit->email = $voucher->email;
			$deposit->payment_method_currency = $method->currency;
			$deposit->depositable_id = $voucher->id;
			$deposit->depositable_type = Voucher::class;
			$deposit->save();

			$checkAmountValidate = (new DepositController())->checkAmountValidate($deposit->amount, $deposit->currency_id, config('transactionType.deposit'), $deposit->payment_method_id);
			if (!$checkAmountValidate['status']) {
				return back()->withInput()->with('alert', $checkAmountValidate['message']);
			}

			return redirect(route('payment.process', $deposit->utr));
		}
	}

	public function checkAmountValidate($amount, $currency_id, $transaction_type_id, $methodId)
	{
		$chargesLimit = ChargesLimit::where(['currency_id' => $currency_id, 'transaction_type_id' => $transaction_type_id, 'payment_method_id' => $methodId, 'is_active' => 1])->firstOrFail();

		if (Auth::check()) {
			$wallet = Wallet::firstOrCreate(['user_id' => Auth::id(), 'currency_id' => $currency_id]);
			$balance = $wallet->balance;
		} else {
			$balance = 0;
		}


		$status = false;
		$charge = 0;
		$min_limit = 0;
		$max_limit = 0;
		$fixed_charge = 0;
		$percentage = 0;
		$percentage_charge = 0;

		if ($chargesLimit) {
			$percentage = getAmount($chargesLimit->percentage_charge);
			$percentage_charge = ($amount * $percentage) / 100;
			$fixed_charge = getAmount($chargesLimit->fixed_charge);
			$min_limit = getAmount($chargesLimit->min_limit);
			$max_limit = getAmount($chargesLimit->max_limit);
			$charge = $percentage_charge + $fixed_charge;
		}
		//Total amount with all fixed and percent charge for deduct

		$payable_amount = $amount + $charge;

		$new_balance = $balance + $amount;

		//Currency inactive
		if ($min_limit == 0 && $max_limit == 0) {
			$message = "Payment method not available for this transaction";
		} elseif ($amount < $min_limit || $amount > $max_limit) {
			$message = "minimum payment $min_limit and maximum payment limit $max_limit";
		} else {
			$status = true;
			$message = "Updated balance : $new_balance";
		}

		$data['status'] = $status;
		$data['charges_limit_id'] = $chargesLimit->id;
		$data['message'] = $message;
		$data['fixed_charge'] = $fixed_charge;
		$data['percentage'] = $percentage;
		$data['percentage_charge'] = $percentage_charge;
		$data['min_limit'] = $min_limit;
		$data['max_limit'] = $max_limit;
		$data['balance'] = $balance;
		$data['payable_amount'] = $payable_amount;
		$data['new_balance'] = $new_balance;
		$data['charge'] = $charge;
		$data['amount'] = $amount;
		$data['convention_rate'] = $chargesLimit->convention_rate;
		$data['currency_id'] = $currency_id;

		return $data;
	}

	public function voucherPaymentView(Request $request, $utr)
	{
		$user = Auth::user();
		//0=Pending, 1=generate, 2 = payment done, 5 = cancel
		$voucher = Voucher::where('utr', $utr)->first();
		if ($request->isMethod('get')) {
			return view('user.voucher.userPayment', compact('voucher'));
		} elseif ($request->isMethod('post')) {

			$reqStatus = $request->status;

			if ($voucher->status == 1 && ($reqStatus == 2 || $reqStatus == 5)) {
				if ($reqStatus == 5) {
					$voucher->status = $reqStatus;
					$voucher->save();

					// send mail sms notification who receiver payment
					$params = [
						'receiver' => optional($voucher->receiver)->name,
						'amount' => getAmount($voucher->amount),
						'currency' => optional($voucher->currency)->code,
						'transaction' => $voucher->utr,
					];
					$action = [
						"link" => route('voucher.index'),
						"icon" => "fa fa-money-bill-alt text-white"
					];
					$firebaseAction = route('voucher.index');
					$sender = $voucher->sender;
					$this->sendMailSms($sender, 'VOUCHER_PAYMENT_CANCEL_TO', $params);
					$this->userPushNotification($sender, 'VOUCHER_PAYMENT_CANCEL_TO', $params, $action);
					$this->userFirebasePushNotification($sender, 'VOUCHER_PAYMENT_CANCEL_TO', $params, $firebaseAction);

					// send mail sms notification who make payment
					$params = [
						'sender' => optional($voucher->sender)->name,
						'amount' => getAmount($voucher->amount),
						'currency' => optional($voucher->currency)->code,
						'transaction' => $voucher->utr,
					];
					$this->sendMailSms($user, 'VOUCHER_PAYMENT_CANCEL_FROM', $params);
					$this->userPushNotification($user, 'VOUCHER_PAYMENT_CANCEL_FROM', $params, $action);
					$this->userFirebasePushNotification($user, 'VOUCHER_PAYMENT_CANCEL_FROM', $params, $firebaseAction);

					return redirect(route('voucher.index'))->with('success', 'Transaction canceled');
				} elseif ($reqStatus == 2) {

					$fromWallet = Wallet::where('is_active', 1)->where('user_id', auth()->id())->where('currency_id', $voucher->currency_id)->firstOrFail();
					if ($voucher->received_amount > $fromWallet->balance) {
						return back()->withInput()->with('alert', 'Please add fund ' . $voucher->currency->name . ' wallet to payment voucher');
					}
					/*
					 *  Add money to Sender Wallet
					 * */
					$sender_wallet = updateWallet($voucher->sender_id, $voucher->currency_id, $voucher->received_amount, 1);
					/*
					 * Deduct money from receiver wallet
					 * */
					$receiver_wallet = updateWallet($voucher->receiver_id, $voucher->currency_id, $voucher->transfer_amount, 0);
					$transaction = new Transaction();
					$transaction->amount = $voucher->amount;
					$transaction->charge = $voucher->charge;
					$transaction->currency_id = $voucher->currency_id;
					$voucher->transactional()->save($transaction);
					$voucher->status = 2;
					$voucher->save();

					// send mail sms notification who receiver payment
					$params = [
						'receiver' => optional($voucher->receiver)->name,
						'amount' => getAmount($voucher->amount),
						'currency' => optional($voucher->currency)->code,
						'transaction' => $voucher->utr,
					];
					$action = [
						"link" => route('voucher.index'),
						"icon" => "fa fa-money-bill-alt text-white"
					];
					$firebaseAction = route('voucher.index');
					$sender = $voucher->sender;
					$this->sendMailSms($sender, 'VOUCHER_PAYMENT_TO', $params);
					$this->userPushNotification($sender, 'VOUCHER_PAYMENT_TO', $params, $action);
					$this->userFirebasePushNotification($sender, 'VOUCHER_PAYMENT_TO', $params, $firebaseAction);

					// send mail sms notification who make payment
					$params = [
						'sender' => optional($voucher->sender)->name,
						'amount' => getAmount($voucher->amount),
						'currency' => optional($voucher->currency)->code,
						'transaction' => $voucher->utr,
					];
					$this->sendMailSms($user, 'VOUCHER_PAYMENT_FROM', $params);
					$this->userPushNotification($user, 'VOUCHER_PAYMENT_FROM', $params, $action);
					$this->userFirebasePushNotification($user, 'VOUCHER_PAYMENT_FROM', $params, $firebaseAction);

					return back()->with('success', 'Payment Confirmed');
				}
			}
			return redirect(route('voucher.index'))->with('success', 'Payment Confirmed');
		}
	}
}
