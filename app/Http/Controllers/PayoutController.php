<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Payout;
use App\Models\PayoutMethod;
use App\Models\Template;
use App\Models\TwoFactorSetting;
use App\Models\Wallet;
use App\Traits\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class PayoutController extends Controller
{
	use Notify;

	public function index()
	{
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$payouts = Payout::with(['user', 'user.profile', 'admin', 'currency'])
			->where(['user_id' => Auth::id()])
			->latest()->paginate();
		return view('user.payout.index', compact('currencies', 'payouts'));
	}

	public function search(Request $request)
	{
		$filterData = $this->_filter($request);
		$search = $filterData['search'];
		$currencies = $filterData['currencies'];
		$userId = $filterData['userId'];
		$payouts = $filterData['payouts']
			->where(['user_id' => $userId])
			->latest()->paginate();
		$payouts->appends($filterData['search']);
		return view('user.payout.index', compact('search', 'payouts', 'currencies'));
	}

	public function _filter($request)
	{
		$userId = Auth::id();
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$search = $request->all();
		$sent = isset($search['type']) ? preg_match("/sent/", $search['type']) : 0;
		$received = isset($search['type']) ? preg_match("/received/", $search['type']) : 0;
		$created_date = isset($search['created_at']) ? preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']) : 0;

		$payouts = Payout::with('user', 'user.profile', 'admin', 'currency')
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
			->when($sent == 1, function ($query) use ($search, $userId) {
				return $query->where("user_id", $userId);
			})
			->when($received == 1, function ($query) use ($search, $userId) {
				return $query->where("receiver_id", $userId);
			})
			->when($created_date == 1, function ($query) use ($search) {
				return $query->whereDate("created_at", $search['created_at']);
			});

		$data = [
			'userId' => $userId,
			'currencies' => $currencies,
			'search' => $search,
			'payouts' => $payouts,
		];
		return $data;
	}

	public function payoutRequest(Request $request)
	{
		if ($request->isMethod('get')) {
			$baseControl = basicControl();
			$payoutMethods = PayoutMethod::where('is_active', 1)->get();
			$template = Template::where('section_name', 'payout-request')->first();
			return view('user.payout.request', compact('payoutMethods', 'baseControl', 'template'));
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());
			$validator = Validator::make($purifiedData, [
				'methodId' => 'required|numeric|not_in:0',
			]);
			if ($validator->fails()) {
				return back()->withInput()->with('alert', 'Please select a method first');
			}

			$methodId = $purifiedData['methodId'];
			$payoutMethod = PayoutMethod::find($methodId);

			if (!$payoutMethod->is_active) {
				return back()->withInput()->with('alert', 'Payout method not active');
			}

			$min_limit = $payoutMethod->min_limit;
			$max_limit = $payoutMethod->max_limit;

			$validator = Validator::make($purifiedData, [
				'amount' => "required|numeric|min:$min_limit|max:$max_limit",
			]);

			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}
			$user = Auth::user();
			$amount = $purifiedData['amount'];
			$currency_id = basicControl()->base_currency;
			$checkAmountValidate = $this->checkAmountValidate($amount, $methodId, $currency_id);

			if (!$checkAmountValidate['status']) {
				return back()->withInput()->with('alert', $checkAmountValidate['message']);
			}

			$payout = new Payout();
			$payout->user_id = $user->id;
			$payout->admin_id = null;
			$payout->currency_id = $currency_id;
			$payout->payout_method_id = $methodId;
			$payout->percentage = $checkAmountValidate['percentage'];
			$payout->charge_percentage = $checkAmountValidate['percentage_charge']; // amount after calculation percent of charge
			$payout->charge_fixed = $checkAmountValidate['fixed_charge'];
			$payout->charge = $checkAmountValidate['charge'];
			$payout->amount = $checkAmountValidate['amount'];
			$payout->transfer_amount = $checkAmountValidate['transfer_amount'];
			$payout->received_amount = $checkAmountValidate['received_amount'];
			$payout->charge_from = $checkAmountValidate['charge_from']; // 0 = sender, 1 = receiver
			$payout->note = null;
			$payout->email = $user->email;
			$payout->status = 0;
			$payout->utr = (string)Str::uuid();
			$payout->save();

			return redirect(route('payout.confirm', $payout->utr))->with('success', 'Payout initiated successfully');
		}
	}

	public function checkAmountValidate($amount, $methodId, $currency_id)
	{
		$payoutMethod = PayoutMethod::find($methodId);
		$wallet = Wallet::firstOrCreate(['user_id' => Auth::id(), 'currency_id' => $currency_id]);

		$limit = optional(basicControl()->currency)->currency_type == 0 ? 8 : 2;

		$balance = getAmount($wallet->balance, $limit);
		$amount = getAmount($amount, $limit);
		$status = false;
		$charge = 0;
		$min_limit = 0;
		$max_limit = 0;
		$fixed_charge = 0;
		$percentage = 0;
		$percentage_charge = 0;

		if ($payoutMethod) {
			$percentage = getAmount($payoutMethod->percentage_charge, $limit);
			$percentage_charge = getAmount(($amount * $percentage) / 100, $limit);
			$fixed_charge = getAmount($payoutMethod->fixed_charge, $limit);
			$min_limit = getAmount($payoutMethod->min_limit, $limit);
			$max_limit = getAmount($payoutMethod->max_limit, $limit);
			$charge = getAmount($percentage_charge + $fixed_charge, $limit);
		}

		$transfer_amount = getAmount($amount + $charge, $limit);
		$received_amount = $amount;

		$remaining_balance = getAmount($balance - $transfer_amount, $limit);

		if ($wallet->is_active != 1) {
			$message = 'Currency not available for this transfer';
		} elseif ($amount < $min_limit || $amount > $max_limit) {
			$message = "minimum payout $min_limit and maximum payout limit $max_limit";
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
		$data['charge_from'] = 0;
		$data['amount'] = $amount;
		$data['currency_id'] = $currency_id;
		$data['currency_limit'] = $limit;
		return $data;
	}

	public function checkLimit(Request $request)
	{
		if ($request->ajax()) {
			$currency = basicControl();
			$methodId = $request->methodId;
			$payoutMethod = PayoutMethod::find($methodId);
			$limit = optional($currency->currency)->currency_type == 0 ? 8 : 2;

			$status = true;
			$fixed_charge = getAmount($payoutMethod->fixed_charge, $limit);
			$percentage_charge = getAmount($payoutMethod->percentage_charge, $limit);
			$min_limit = getAmount($payoutMethod->min_limit, $limit);
			$max_limit = getAmount($payoutMethod->max_limit, $limit);

			$data['status'] = $status;
			$data['fixed_charge'] = $fixed_charge;
			$data['percentage_charge'] = $percentage_charge;
			$data['min_limit'] = $min_limit;
			$data['max_limit'] = $max_limit;
			$data['currency_limit'] = $limit;
			$data['currency_code'] = optional($currency->currency)->code;

			return response()->json($data);
		}
	}

	public function confirmPayout(Request $request, $utr)
	{
		$user = Auth::user();
		$payout = Payout::where('utr', $utr)->first();
		$payoutMethod = PayoutMethod::find($payout->payout_method_id);
		$wallet = Wallet::firstOrCreate(['user_id' => $user->id, 'currency_id' => basicControl()->base_currency]);

		$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
		$enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);
		if ($request->isMethod('get')) {
			if ($payoutMethod->code == 'flutterwave') {
				return view('user.payout.gateway.' . $payoutMethod->code, compact('payout', 'payoutMethod', 'wallet', 'enable_for'));
			} elseif ($payoutMethod->code == 'paystack') {
				return view('user.payout.gateway.' . $payoutMethod->code, compact('payout', 'payoutMethod', 'wallet', 'enable_for'));
			}
			return view('user.payout.confirm', compact('payout', 'payoutMethod', 'wallet', 'enable_for'));
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());

			$rules = [];
			$inputField = [];
			if ($payoutMethod->inputForm != null) {
				foreach (json_decode($payoutMethod->inputForm) as $key => $cus) {

					$rules[$key] = [$cus->validation];
					if ($cus->type == 'file') {
						array_push($rules[$key], 'image');
						array_push($rules[$key], 'mimes:jpeg,jpg,png');
						array_push($rules[$key], 'max:2048');
					}
					if ($cus->type == 'text') {
						array_push($rules[$key], 'max:191');
					}
					if ($cus->type == 'textarea') {
						array_push($rules[$key], 'max:300');
					}
					$inputField[] = $key;
				}
			}
			if (in_array('payout', $enable_for)) {
				$rules['security_pin'] = 'required|integer|digits:5';
			}

			if ($payoutMethod->is_automatic == 1) {
				$rules['currency_code'] = 'required';
				if ($payoutMethod->code == 'paypal') {
					$rules['recipient_type'] = 'required';
				}
			}

			$validate = Validator::make($request->all(), $rules);

			if ($validate->fails()) {
				return back()->withErrors($validate)->withInput();
			}

			if (in_array('payout', $enable_for) && !Hash::check($purifiedData['security_pin'], $twoFactorSetting->security_pin)) {
				return back()->withErrors(['security_pin' => 'You have entered an incorrect PIN'])->withInput();
			}

			$checkAmountValidate = $this->checkAmountValidate($payout->amount, $payout->payout_method_id, $payout->currency_id);
			if (!$checkAmountValidate['status']) {
				return back()->withInput()->with('alert', $checkAmountValidate['message']);
			}
			$collection = collect($purifiedData);
			$reqField = [];
			if ($payoutMethod->inputForm != null) {
				foreach ($collection as $k => $v) {
					foreach (json_decode($payoutMethod->inputForm) as $inKey => $inVal) {
						if ($k != $inKey) {
							continue;
						} else {
							if ($inVal->type == 'file') {
								if ($request->file($inKey) && $request->file($inKey)->isValid()) {
									$extension = $request->$inKey->extension();
									$fileName = strtolower(strtotime("now") . '.' . $extension);
									$storedPath = base_path('assets/upload/payoutFile/') . $fileName;
									$imageMake = Image::make($purifiedData[$inKey]);
									$imageMake->save($storedPath);

									$reqField[$inKey] = [
										'fieldValue' => $fileName,
										'type' => $inVal->type,
									];
								}
							} else {
								$reqField[$inKey] = $v;
								$reqField[$inKey] = [
									'fieldValue' => $v,
									'type' => $inVal->type,
								];
							}
						}
					}
				}
				if ($payoutMethod->is_automatic == 1) {
					$reqField['amount'] = [
						'fieldValue' => $payout->amount * convertRate($request->currency_code, $payout),
						'type' => 'text',
					];
				}
				if ($payoutMethod->code == 'paypal') {
					$reqField['recipient_type'] = [
						'fieldValue' => $request->recipient_type,
						'type' => 'text',
					];
				}
				$payout->withdraw_information = json_encode($reqField);
			} else {
				$payout->withdraw_information = null;
			}
			$payout->currency_code = @$request->currency_code;
			$payout->status = 1;
			/*
			 * Deduct money from Sender Wallet
			 * */
			$sender_wallet = updateWallet($payout->user_id, $payout->currency_id, $payout->transfer_amount, 0);
			$payout->save();

			$this->userNotify($user, $payout);
			return redirect(route('payout.index'))->with('success', 'Payout generated successfully');
		}
	}

	public function getBankForm(Request $request)
	{
		$bankName = $request->bankName;
		$bankArr = config('banks.' . $bankName);

		if ($bankArr['api'] != null) {

			$methodObj = 'App\\Services\\Payout\\flutterwave\\Card';
			$data = $methodObj::getBank($bankArr['api']);
			$value['bank'] = $data;
		}
		$value['input_form'] = $bankArr['input_form'];
		return $value;
	}

	public function flutterwavePayout(Request $request, $utr)
	{
		$user = Auth::user();
		$payout = Payout::where('utr', $utr)->first();
		$payoutMethod = PayoutMethod::find($payout->payout_method_id);
		$wallet = Wallet::firstOrCreate(['user_id' => $user->id, 'currency_id' => basicControl()->base_currency]);

		$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
		$enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);

		$purifiedData = Purify::clean($request->all());
		if (empty($purifiedData['transfer_name'])) {
			return back()->with('alert', 'Transfer field is required');
		}
		$validation = config('banks.' . $purifiedData['transfer_name'] . '.validation');

		$rules = [];
		$inputField = [];
		if ($validation != null) {
			foreach ($validation as $key => $cus) {
				$rules[$key] = 'required';
				$inputField[] = $key;
			}
		}
		if (in_array('payout', $enable_for)) {
			$rules['security_pin'] = 'required|integer|digits:5';
		}

		if ($request->transfer_name == 'NGN BANK' || $request->transfer_name == 'NGN DOM' || $request->transfer_name == 'GHS BANK'
			|| $request->transfer_name == 'KES BANK' || $request->transfer_name == 'ZAR BANK' || $request->transfer_name == 'ZAR BANK') {
			$rules['bank'] = 'required';
		}

		$rules['currency_code'] = 'required';

		$validate = Validator::make($request->all(), $rules);

		if ($validate->fails()) {
			return back()->withErrors($validate)->withInput();
		}

		if (in_array('payout', $enable_for) && !Hash::check($purifiedData['security_pin'], $twoFactorSetting->security_pin)) {
			return back()->withErrors(['security_pin' => 'You have entered an incorrect PIN'])->withInput();
		}

		$checkAmountValidate = $this->checkAmountValidate($payout->amount, $payout->payout_method_id, $payout->currency_id);
		if (!$checkAmountValidate['status']) {
			return back()->withInput()->with('alert', $checkAmountValidate['message']);
		}

		$collection = collect($purifiedData);
		$reqField = [];
		$metaField = [];

		if (config('banks.' . $purifiedData['transfer_name'] . '.input_form') != null) {
			foreach ($collection as $k => $v) {
				foreach (config('banks.' . $purifiedData['transfer_name'] . '.input_form') as $inKey => $inVal) {
					if ($k != $inKey) {
						continue;
					} else {

						if ($inVal == 'meta') {
							$metaField[$inKey] = $v;
							$metaField[$inKey] = [
								'fieldValue' => $v,
								'type' => 'text',
							];
						} else {
							$reqField[$inKey] = $v;
							$reqField[$inKey] = [
								'fieldValue' => $v,
								'type' => 'text',
							];
						}
					}
				}
			}

			if ($request->transfer_name == 'NGN BANK' || $request->transfer_name == 'NGN DOM' || $request->transfer_name == 'GHS BANK'
				|| $request->transfer_name == 'KES BANK' || $request->transfer_name == 'ZAR BANK' || $request->transfer_name == 'ZAR BANK') {

				$reqField['account_bank'] = [
					'fieldValue' => $request->bank,
					'type' => 'text',
				];
			} elseif ($request->transfer_name == 'XAF/XOF MOMO') {
				$reqField['account_bank'] = [
					'fieldValue' => 'MTN',
					'type' => 'text',
				];
			} elseif ($request->transfer_name == 'FRANCOPGONE' || $request->transfer_name == 'mPesa' || $request->transfer_name == 'Rwanda Momo'
				|| $request->transfer_name == 'Uganda Momo' || $request->transfer_name == 'Zambia Momo') {
				$reqField['account_bank'] = [
					'fieldValue' => 'MPS',
					'type' => 'text',
				];
			}

			if ($request->transfer_name == 'Barter') {
				$reqField['account_bank'] = [
					'fieldValue' => 'barter',
					'type' => 'text',
				];
			} elseif ($request->transfer_name == 'flutterwave') {
				$reqField['account_bank'] = [
					'fieldValue' => 'barter',
					'type' => 'text',
				];
			}


			$reqField['amount'] = [
				'fieldValue' => $payout->amount * convertRate($request->currency_code, $payout),
				'type' => 'text',
			];

			$payout->withdraw_information = json_encode($reqField);
			$payout->meta_field = $metaField;
		} else {
			$payout->withdraw_information = null;
			$payout->meta_field = null;
		}

		$payout->status = 1;
		$payout->currency_code = $request->currency_code;
		$sender_wallet = updateWallet($payout->user_id, $payout->currency_id, $payout->transfer_amount, 0);
		$payout->save();
		$this->userNotify($user, $payout);
		return redirect(route('payout.index'))->with('success', 'Payout generated successfully');
	}

	public function getBankList(Request $request)
	{
		$currencyCode = $request->currencyCode;
		$methodObj = 'App\\Services\\Payout\\paystack\\Card';
		$data = $methodObj::getBank($currencyCode);
		return $data;
	}

	public function paystackPayout(Request $request, $utr)
	{
		$user = Auth::user();
		$payout = Payout::where('utr', $utr)->first();
		$payoutMethod = PayoutMethod::find($payout->payout_method_id);
		$wallet = Wallet::firstOrCreate(['user_id' => $user->id, 'currency_id' => basicControl()->base_currency]);

		$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
		$enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);

		$purifiedData = Purify::clean($request->all());

		if (empty($purifiedData['bank'])) {
			return back()->with('alert', 'Bank field is required')->withInput();
		}

		$rules = [];
		$inputField = [];
		if ($payoutMethod->inputForm != null) {
			foreach (json_decode($payoutMethod->inputForm) as $key => $cus) {

				$rules[$key] = [$cus->validation];
				if ($cus->type == 'file') {
					array_push($rules[$key], 'image');
					array_push($rules[$key], 'mimes:jpeg,jpg,png');
					array_push($rules[$key], 'max:2048');
				}
				if ($cus->type == 'text') {
					array_push($rules[$key], 'max:191');
				}
				if ($cus->type == 'textarea') {
					array_push($rules[$key], 'max:300');
				}
				$inputField[] = $key;
			}
		}

		$rules['type'] = 'required';
		$rules['currency'] = 'required';

		$validate = Validator::make($request->all(), $rules);

		if ($validate->fails()) {
			return back()->withErrors($validate)->withInput();
		}

		if (in_array('payout', $enable_for) && !Hash::check($purifiedData['security_pin'], $twoFactorSetting->security_pin)) {
			return back()->withErrors(['security_pin' => 'You have entered an incorrect PIN'])->withInput();
		}

		$checkAmountValidate = $this->checkAmountValidate($payout->amount, $payout->payout_method_id, $payout->currency_id);
		if (!$checkAmountValidate['status']) {
			return back()->withInput()->with('alert', $checkAmountValidate['message']);
		}
		$collection = collect($purifiedData);
		$reqField = [];
		if ($payoutMethod->inputForm != null) {
			foreach ($collection as $k => $v) {
				foreach (json_decode($payoutMethod->inputForm) as $inKey => $inVal) {
					if ($k != $inKey) {
						continue;
					} else {
						if ($inVal->type == 'file') {
							if ($request->file($inKey) && $request->file($inKey)->isValid()) {
								$extension = $request->$inKey->extension();
								$fileName = strtolower(strtotime("now") . '.' . $extension);
								$storedPath = base_path('assets/upload/payoutFile/') . $fileName;
								$imageMake = Image::make($purifiedData[$inKey]);
								$imageMake->save($storedPath);

								$reqField[$inKey] = [
									'fieldValue' => $fileName,
									'type' => $inVal->type,
								];
							}
						} else {
							$reqField[$inKey] = $v;
							$reqField[$inKey] = [
								'fieldValue' => $v,
								'type' => $inVal->type,
							];
						}
					}
				}
			}
			$reqField['type'] = [
				'fieldValue' => $request->type,
				'type' => 'text',
			];
			$reqField['bank_code'] = [
				'fieldValue' => $request->bank,
				'type' => 'text',
			];
			$reqField['amount'] = [
				'fieldValue' => $payout->amount * convertRate($request->currency, $payout),
				'type' => 'text',
			];
			$payout->withdraw_information = json_encode($reqField);
		} else {
			$payout->withdraw_information = null;
		}
		$payout->currency_code = $request->currency_code;
		$payout->status = 1;
		/*
		 * Deduct money from Sender Wallet
		 * */
		$sender_wallet = updateWallet($payout->user_id, $payout->currency_id, $payout->transfer_amount, 0);
		$payout->save();
		$this->userNotify($user, $payout);
		return redirect(route('payout.index'))->with('success', 'Payout generated successfully');
	}

	public function userNotify($user, $payout)
	{
		$params = [
			'sender' => $user->name,
			'amount' => getAmount($payout->amount),
			'currency' => optional($payout->currency)->code,
			'transaction' => $payout->utr,
		];

		$action = [
			"link" => route('admin.payout.index'),
			"icon" => "fa fa-money-bill-alt text-white"
		];
		$firebaseAction = route('admin.payout.index');
		$this->adminMail('PAYOUT_REQUEST_TO_ADMIN', $params);
		$this->adminPushNotification('PAYOUT_REQUEST_TO_ADMIN', $params, $action);
		$this->adminFirebasePushNotification('PAYOUT_REQUEST_TO_ADMIN', $params, $firebaseAction);

		$params = [
			'amount' => getAmount($payout->amount),
			'currency' => optional($payout->currency)->code,
			'transaction' => $payout->utr,
		];
		$action = [
			"link" => route('payout.index'),
			"icon" => "fa fa-money-bill-alt text-white"
		];
		$firebaseAction = route('payout.index');
		$this->sendMailSms($user, 'PAYOUT_REQUEST_FROM', $params);
		$this->userPushNotification($user, 'PAYOUT_REQUEST_FROM', $params, $action);
		$this->userFirebasePushNotification($user, 'PAYOUT_REQUEST_FROM', $params, $firebaseAction);
	}
}
