<?php

namespace App\Http\Controllers;

use App\Models\BillMethod;
use App\Models\BillPay;
use App\Models\BillService;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\Fund;
use App\Models\Gateway;
use App\Models\PayoutMethod;
use App\Models\Transaction;
use App\Models\TwoFactorSetting;
use App\Models\Wallet;
use App\Traits\Notify;
use App\Traits\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Stevebauman\Purify\Facades\Purify;


class PayBillController extends Controller
{
	use Notify, Upload;

	public function payBill()
	{
		$currencies = Currency::select('id', 'code', 'name', 'currency_type')->where('is_active', 1)->get();
		$data['billMethod'] = BillMethod::with(['billServices'])->where('code', 'flutterwave')->firstOrFail();
		$billServices = $data['billMethod']->billServices->groupBy('country');
		$countryLists = config('country');
		$isoCode = [];
		$countryName = [];
		foreach ($billServices as $key => $item) {
			foreach ($countryLists as $value) {
				if ($key == $value['code']) {
					$isoCode[$value['iso_code']] = $value['iso_code'];
					$countryName[$value['code']] = $value['name'];
					break;
				}
			}
		}
		$data['isoCodes'] = $isoCode;
		$data['countryNames'] = $countryName;
		return view('user.bill_pay.request', $data, compact('countryLists', 'currencies'));
	}

	public function payBillSubmit(Request $request)
	{
		$purifiedData = Purify::clean($request->all());
		$validator = Validator::make($purifiedData, [
			'category' => 'required',
			'country' => 'required',
			'service' => 'required',
			'customer' => 'required',
			'amount' => 'required',
			'from_wallet' => 'required',
		]);
		if ($validator->fails()) {
			return back()->withErrors($validator)->withInput();
		}
		$service = BillService::with(['method'])->findOrFail($purifiedData['service']);

		if ($service->amount > 0) {
			$amount = $service->amount;
		} else {
			$amount = $purifiedData['amount'];
		}

		if ($service->min_amount > $amount) {
			return back()->with('alert', 'Amount must be greater than minimum amount');
		}
		if ($service->max_amount > 0 && $service->max_amount < $amount) {
			return back()->with('alert', 'Amount must be smaller than maximum amount');
		}

		$value = $this->convertRate($service->currency, $service->method, $purifiedData['from_wallet']);
		if (!$value) {
			return back()->withInput()->with('alert', 'Something went wrong');
		}

		$charge = $this->calculateCharge($amount, $service->fixed_charge, $service->percent_charge);
		$billPay = new BillPay();
		$billPay->method_id = $service->bill_method_id;
		$billPay->user_id = auth()->id();
		$billPay->service_id = $service->id;
		$billPay->from_wallet = $purifiedData['from_wallet'];
		$billPay->customer = $purifiedData['customer'];
		$billPay->type = $service->type;
		$billPay->category_name = $purifiedData['category'];
		$billPay->country_name = $purifiedData['country'];
		$billPay->amount = $amount;
		$billPay->charge = $charge;
		$billPay->payable_amount = $amount + $charge;
		$billPay->currency = $service->currency;
		$billPay->exchange_rate = $value['rate'];
		$billPay->utr = (string)Str::uuid();
		$billPay->status = 0;

		$billPay->save();

		return redirect(route('pay.bill.confirm', $billPay->utr))->with('success', 'Bill initiated successfully');
	}

	public function payBillConfirm(Request $request, $utr)
	{
		$user = Auth::user();
		$data['billPay'] = BillPay::with(['service', 'walletCurrency'])->where('utr', $utr)->where('user_id', auth()->id())->firstOrFail();
		$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
		$enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);

		if ($request->method() == 'GET') {
			return view('user.bill_pay.confirm', $data, compact('enable_for'));
		}
		if ($request->method() == 'POST') {
			$purifiedData = Purify::clean($request->all());
			if (in_array('bill_payment', $enable_for)) {
				$validationRules['security_pin'] = 'required|integer|digits:5';
			}

			$validate = Validator::make($purifiedData, $validationRules);
			if ($validate->fails()) {
				return back()->withErrors($validate)->withInput();
			}

			if (in_array('bill_payment', $enable_for) && !Hash::check($purifiedData['security_pin'], $twoFactorSetting->security_pin)) {
				return back()->withErrors(['security_pin' => 'You have entered an incorrect PIN'])->withInput();
			}

			$value = $this->convertRate($data['billPay']->currency, $data['billPay']->method, $data['billPay']->from_wallet);
			if (!$value) {
				return back()->withInput()->with('alert', 'Something went wrong');
			}

			$payableAmount = ($data['billPay']->payable_amount / $value['rate']) + ($data['billPay']->charge / $value['rate']);
			$fromWallet = Wallet::where('is_active', 1)->where('user_id', $data['billPay']->user_id)->where('currency_id', $data['billPay']->from_wallet)->firstOrFail();
			if ($payableAmount > $fromWallet->balance) {
				return back()->withInput()->with('alert', 'Please add fund ' . $data['billPay']->walletCurrency->name . ' wallet to payment bill');
			}

			updateWallet($data['billPay']->user_id, $data['billPay']->from_wallet, $payableAmount, 0);

			$data['billPay']->pay_amount_in_base = $payableAmount;
			$data['billPay']->base_currency_id = $fromWallet->currency_id;
			$data['billPay']->status = 2;

			$transaction = new Transaction();
			$transaction->amount = $data['billPay']->amount;
			$transaction->charge = $data['billPay']->charge;
			$transaction->currency_code = $data['billPay']->currency;
			$data['billPay']->transactional()->save($transaction);
			$data['billPay']->save();


			$res = $this->billPayApi($data['billPay']);
			if ($res['status'] == 'error') {
				$data['billPay']->last_api_error = $res['message'];
				$data['billPay']->save();
				return redirect()->route('pay.bill.list')->with('alert', 'Something went wrong');
			}
			if ($res['status'] == 'success') {
				$data['billPay']->status = 3;
				$data['billPay']->save();
				$params = [
					'amount' => $data['billPay']->amount,
					'currency' => $data['billPay']->currency,
					'transaction' => $data['billPay']->utr,
				];
				$action = [
					"link" => "",
					"icon" => "fa fa-money-bill-alt text-white"
				];

				$this->sendMailSms($data['billPay']->user, 'BILL_PAY', $params);
				$this->userPushNotification($data['billPay']->user, 'BILL_PAY', $params, $action);
				$this->userFirebasePushNotification($data['billPay']->user, 'BILL_PAY', $params);
				return redirect()->route('pay.bill.list')->with('alert', $res['message']);
			}
		}
	}

	public function convertRate($inputCurrency, $method, $fromWallet)
	{
		$data = array();
		$currency = Currency::find($fromWallet);
		if ($currency) {
			$data['currency_id'] = $currency->id;
			if ($method) {
				foreach ($method->convert_rate as $key => $rate) {
					if ($key == $inputCurrency) {
						$rate = $rate;
						break;
					}
				}
			}
		}
		if (isset($rate)) {
			if ($currency->exchange_rate != 0) {
				$data['rate'] = $rate / $currency->exchange_rate;
			} else {
				$data['rate'] = $rate;
			}
		} else {
			if ($currency->exchange_rate != 0) {
				$data['rate'] = 1 / $currency->exchange_rate;
			} else {
				$data['rate'] = 1;
			}
		}

		return $data;
	}

	function calculateCharge($amount, $fixed_charge, $percent_charge)
	{
		$fromPercent = $amount * $percent_charge / 100;
		$charge = $fromPercent + $fixed_charge;
		return $charge;
	}

	public function fetchServices(Request $request)
	{
		$category = $request->category;
		$code = $request->code;
		$services = BillService::where(['service' => $category, 'country' => $code, 'status' => 1])->get();
		return response()->json([
			'status' => 'success',
			'data' => $services
		]);
	}

	public function billPayApi($billPay)
	{
		$method = $billPay->method;
		$methodObj = 'App\\Services\\Bill\\' . $method->code . '\\Card';
		$response = $methodObj::payBill($billPay);
		if (!$response) {
			return redirect()->route('pay.bill')->with('alert', 'Something Went Wrong');
		}
		if ($response['status'] == 'error') {
			$data = [
				'status' => 'error',
				'message' => $response['data'],
			];
			return $data;
		}
		if ($response['status'] == 'success') {
			$data = [
				'status' => 'success',
				'message' => 'pay bill completed',
			];
			return $data;
		}
	}

	public function payBillList(Request $request)
	{
		$search = $request->all();
		$created_date = isset($search['created_at']) ? preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']) : 0;

		$data['bills'] = BillPay::where('user_id', auth()->id())
			->when(isset($search['category']), function ($query) use ($search) {
				return $query->where('category_name', 'LIKE', "%{$search['category']}%");
			})
			->when(isset($search['type']), function ($query) use ($search) {
				return $query->where('type', 'LIKE', "%{$search['type']}%");
			})
			->when(isset($search['status']), function ($query) use ($search) {
				if ($search['status'] == 'generate') {
					return $query->where('status', 0);
				} elseif ($search['status'] == 'pending') {
					return $query->where('status', 1);
				} elseif ($search['status'] == 'payment_completed') {
					return $query->where('status', 2);
				} elseif ($search['status'] == 'bill_completed') {
					return $query->where('status', 3);
				} elseif ($search['status'] == 'bill_return') {
					return $query->where('status', 4);
				}
			})
			->when($created_date == 1, function ($query) use ($search) {
				return $query->whereDate("created_at", $search['created_at']);
			})
			->latest()->paginate(config('basic.paginate'));
		return view('user.bill_pay.index', $data);
	}
}
