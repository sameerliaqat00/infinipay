<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillMethod;
use App\Models\BillPay;
use App\Models\BillService;
use App\Traits\Notify;
use App\Traits\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class BillPaymentController extends Controller
{
	use Upload, Notify;

	public function index()
	{
		$data['billMethods'] = BillMethod::paginate(config('basic.paginate'));
		return view('admin.bill_payment.methods.index', $data);
	}

	public function edit(Request $request, $id)
	{
		$data['billMethod'] = BillMethod::with(['billServices'])->findOrFail($id);
		$billServices = $data['billMethod']->billServices->groupBy('country');
		$countryLists = config('country');
		$shortCode = [];
		$isoCode = [];
		foreach ($billServices as $key => $item) {
			foreach ($countryLists as $value) {
				if ($key == $value['code']) {
					$isoCode[$value['iso_code']] = $value['iso_code'];
					break;
				}
			}
		}
		$data['isoCodes'] = $isoCode;

		if ($request->method() == 'GET') {
			return view('admin.bill_payment.methods.edit', $data);
		}
		if ($request->method() == 'PUT') {

			$purifiedData = Purify::clean($request->all());
			$validator = Validator::make($purifiedData, [
				'methodName' => 'required|min:3|max:50',
				'description' => 'required|min:3|max:50',
			], [
				'min' => 'This field must be at least :min characters.',
				'string' => 'This field must be :string.',
				'required' => 'This field is required.',
			]);


			$purifiedData = (object)$purifiedData;

			$parameters = [];
			if ($data['billMethod']->parameters) {
				foreach ($request->except('_token', '_method', 'image') as $k => $v) {
					foreach ($data['billMethod']->parameters as $key => $cus) {
						if ($k != $key) {
							continue;
						} else {
							$rules[$key] = 'required|max:191';
							$parameters[$key] = $v;
						}
					}
				}
			}

			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}

			$data['billMethod']->methodName = $purifiedData->methodName;
			$data['billMethod']->description = $purifiedData->description;

			if ($request->file('logo') && $request->file('logo')->isValid()) {
				$extension = $request->logo->extension();
				$logoName = strtolower($purifiedData->methodName . '.' . $extension);
				$old = $data['billMethod']->logo;
				$data['billMethod']->logo = $this->uploadImage($request->logo, config('location.billPaymentMethod.path'), config('location.billPaymentMethod.size'), $old, $logoName);
			}

			$data['billMethod']->save();
			return redirect(route('bill.method.list'))->with('success', 'Method Successfully Saved');
		}
	}

	public function serviceRate($id, Request $request)
	{
		$billMethod = BillMethod::findOrFail($id);
		$collectionSpecification = collect($request->convert_rate);
		$rate_params = [];
		if ($collectionSpecification) {
			foreach ($collectionSpecification as $k => $v) {
				if ($v == null) {
					$v = 1.00;
				}
				$rate_params[$k] = $v;
			}
		}
		$billMethod->convert_rate = $rate_params;
		$billMethod->save();
		return redirect()->route('bill.method.edit', $billMethod->id)->with('success', 'Rate Updated');
	}


	public function fetchServices(Request $request)
	{
		$purifiedData = Purify::clean($request->all());
		$validationRules = [
			'api_service' => 'required',
		];
		$validate = Validator::make($purifiedData, $validationRules);
		if ($validate->fails()) {
			return back()->withErrors($validate)->withInput();
		}
		$methodObj = 'App\\Services\\Bill\\flutterwave\\Card';
		$response = $methodObj::fetchServices($request->api_service);
		if (!$response) {
			return back()->with('alert', 'Something Went Wrong');
		}
		if ($response['status'] == 'error') {
			return back()->with('alert', $response['data']);
		}
		if ($response['status'] == 'success') {
			$data['services'] = $response['data'];
			$data['api_service'] = $request->api_service;
			return view('admin.bill_payment.methods.fetch_service', $data);
		}
	}

	public function addServices(Request $request)
	{
		$billMethod = BillMethod::where('code', 'flutterwave')->first();
		$response = $request->res;
		$api_service = $request->api_service;

		$service = BillService::where('code', $response['biller_code'])->where('country', $response['country'])->where('service', $api_service)->first();
		if ($service) {
			$service->type = $response['biller_name'];
			$service->country = $response['country'];
			$service->amount = $response['amount'];
			$service->save();
		} else {
			$service = new BillService();
			$service->bill_method_id = $billMethod->id;
			$service->service = $api_service;
			$service->code = $response['biller_code'];
			$service->type = $response['biller_name'];
			$service->country = $response['country'];
			$service->info = $response;
			$service->label_name = $response['label_name'];
			$service->amount = $response['amount'];
			$service->currency = $this->getIsoCode($response['country']);
			$service->save();
		}

		return response()->json([
			'status' => 'success'
		]);
	}

	public function getIsoCode($countryCode)
	{
		$countryLists = config('country');
		$shortCode = [];
		$isoCode = [];
		foreach ($countryLists as $value) {
			if ($countryCode == $value['code']) {
				$currency = $value['iso_code'];
				break;
			}
		}
		return $currency;
	}

	public function addServicesBulk(Request $request)
	{
		$billMethod = BillMethod::where('code', 'flutterwave')->first();
		$responses = $request->res;
		$api_service = $request->api_service;

		if (count($responses) < 1) {
			return response()->json([
				'status' => 'error',
			]);
		}

		foreach ($responses as $response) {
			$response = json_decode($response);
			$service = BillService::where('code', $response->biller_code)->where('country', $response->country)->where('service', $api_service)->first();
			if ($service) {
				$service->type = $response->biller_name;
				$service->country = $response->country;
				$service->amount = $response->amount;
				$service->save();
			} else {
				$service = new BillService();
				$service->bill_method_id = $billMethod->id;
				$service->service = $api_service;
				$service->code = $response->biller_code;
				$service->type = $response->biller_name;
				$service->country = $response->country;
				$service->info = $response;
				$service->label_name = $response->label_name;
				$service->amount = $response->amount;
				$service->currency = $this->getIsoCode($response->country);
				$service->save();
			}
		}
		return response()->json([
			'status' => 'success',
			'route' => route('bill.method.edit', $billMethod->id)
		]);
	}

	public function serviceList()
	{
		$data['services'] = BillService::latest()->paginate(config('basic.paginate'));
		$countries = BillService::groupBy('country')->get();
		$categories = BillService::groupBy('service')->get();
		$countryList = config('country');
		return view('admin.bill_payment.service.index', $data, compact('countries', 'categories', 'countryList'));
	}

	public function chargeLimitAdd(Request $request)
	{
		$purifiedData = Purify::clean($request->all());
		$validationRules = [
			'service' => 'required',
			'country' => 'required',
			'percent_charge' => 'required',
			'fixed_charge' => 'required',
			'currency' => 'required',
		];
		$validate = Validator::make($purifiedData, $validationRules);
		if ($validate->fails()) {
			return back()->withErrors($validate)->withInput();
		}
		$services = BillService::where('country', $request->country)->where('service', $request->service)->get();
		foreach ($services as $service) {
			$service->percent_charge = $request->percent_charge;
			$service->fixed_charge = $request->fixed_charge;
			$service->min_amount = $request->min_amount;
			$service->max_amount = $request->max_amount;
			$service->currency = $request->currency;
			$service->save();
		}

		return back()->with('success', 'Charge and Limit has been applied');
	}

	public function chargeLimitEdit(Request $request, $id)
	{
		$purifiedData = Purify::clean($request->all());
		$validationRules = [
			'percent_charge' => 'required',
			'fixed_charge' => 'required',
			'currency' => 'required',
		];
		$validate = Validator::make($purifiedData, $validationRules);
		if ($validate->fails()) {
			return back()->withErrors($validate)->withInput();
		}
		$service = BillService::findOrFail($id);
		$service->percent_charge = $request->percent_charge;
		$service->fixed_charge = $request->fixed_charge;
		$service->min_amount = $request->min_amount;
		$service->max_amount = $request->max_amount;
		$service->currency = $request->currency;
		$service->save();

		return back()->with('success', 'Charge and Limit has been updated');
	}

	public function billPayList(Request $request)
	{
		$search = $request->all();
		$created_date = isset($search['created_at']) ? preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']) : 0;

		$data['bills'] = BillPay::with(['user', 'method'])->when(isset($search['category']), function ($query) use ($search) {
			return $query->where('category_name', 'LIKE', "%{$search['category']}%");
		})
			->when(isset($search['username']), function ($query) use ($search) {
				$query->whereHas('user', function ($qq) use ($search) {
					$qq->where('username', 'LIKE', "%{$search['username']}%");
				});
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
		return view('admin.bill_payment.index', $data);
	}

	public function billPayView($utr)
	{
		$data['billDetails'] = BillPay::with(['user', 'baseCurrency'])->where('utr', $utr)->firstOrFail();
		return view('admin.bill_payment.show', $data);
	}

	public function statusChange($id)
	{
		$service = BillService::findOrFail($id);
		if ($service) {
			if ($service->status == 1) {
				$service->status = 0;
			} else {
				$service->status = 1;
			}
			$service->save();
			return back()->with('success', 'Updated Successfully');
		}
	}
}
