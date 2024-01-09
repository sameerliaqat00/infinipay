<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\VirtualCardMethod;
use App\Models\VirtualCardOrder;
use App\Models\VirtualCardTransaction;
use App\Models\Wallet;
use App\Services\VirtualCardCurl;
use App\Traits\Notify;
use App\Traits\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class VirtualCardController extends Controller
{
	use Upload, Notify;

	public function index()
	{
		$data['virtualCardMethods'] = VirtualCardMethod::orderBy('name', 'ASC')->get();
		return view('admin.virtual_card.index', $data);
	}

	public function edit($id)
	{
		$data['virtualCardMethod'] = VirtualCardMethod::findOrFail($id);
		return view('admin.virtual_card.edit', $data);
	}

	public function update(Request $request, $id)
	{
		$rules = [
			'currency' => 'required|string',
			'debit_currency' => 'required|string',
		];

		$virtualCard = VirtualCardMethod::where('id', $id)->firstOr(function () {
			throw new \Exception('No virtual card method found.');
		});

		$validationRules = [
			'currency' => 'required',
			'debit_currency' => 'required',
		];

		$parameters = [];
		foreach ($request->except('_token', '_method', 'image') as $k => $v) {
			foreach ($virtualCard->parameters as $key => $cus) {
				if ($k != $key) {
					continue;
				} else {
					$rules[$key] = 'required|max:191';
					$parameters[$key] = $v;
				}
			}
		}
		$fund_params = [];
		foreach ($request->fund as $k => $v) {
			foreach ($virtualCard->add_fund_parameter as $key => $cus) {
				if ($k != $key) {
					continue;
				} else {
					foreach ($cus as $key1 => $cus1) {
						$rules[$key][$key1] = 'required|max:191';
						$fund_params[$key][$key1] = $cus1->field_value;
					}
				}
			}
		}

		$collectionSpecification = collect($request->fund);
		$fund_params = [];
		foreach ($request->fund as $k => $v) {
			foreach ($virtualCard->add_fund_parameter as $key => $cus) {
				if ($k != $key) {
					continue;
				} else {
					foreach ($cus as $key1 => $cus1) {
						$rules[$key][$key1] = 'required|min:0';
						if ($v[$cus1->field_name] < 0 || $v[$cus1->field_name] == null) {
							$v[$cus1->field_name] = 0;
						}
						$fund_params[$key][$key1] = [
							'field_name' => $cus1->field_name,
							'field_level' => $cus1->field_level,
							'field_value' => $v[$cus1->field_name],
							'type' => $cus1->type,
							'validation' => $cus1->validation,
						];
					}
				}
			}
		}

		$purifiedData = Purify::clean($request->all());
		$purifiedData['image'] = $request->image;
		$validate = Validator::make($purifiedData, $validationRules);
		if ($validate->fails()) {
			return back()->withErrors($validate)->withInput();
		}
		$purifiedData = (object)$purifiedData;
		if ($request->hasFile('image')) {
			try {
				$old = $virtualCard->image ?? null;
				$image = $this->uploadImage($request->image, config('location.virtualCardMethod.path'), config('location.virtualCardMethod.size'), $old);
			} catch (\Exception $exp) {
				return back()->with('alert', 'Image could not be uploaded.');
			}
		}


		try {
			$response = $virtualCard->update([
				'currency' => $purifiedData->currency,
				'debit_currency' => $purifiedData->debit_currency,
				'parameters' => $parameters,
				'add_fund_parameter' => $fund_params,
				'info_box' => $purifiedData->info_box,
				'image' => $image ?? $virtualCard->image,
			]);

			if (!$response) {
				throw new \Exception('Unexpected error! Please try again.');
			}
			return back()->with('success', 'Virtual card data has been updated.');
		} catch (\Exception $exception) {
			return back()->with('alert', $exception->getMessage());
		}
	}

	public function statusChange(Request $request, $id)
	{
		try {
			DB::beginTransaction();
			$virtualMethod = VirtualCardMethod::findOrFail($id);
			$virtualMethod->status = 1;
			$virtualMethod->save();
			$virtualMethods = VirtualCardMethod::where('id', '!=', $id)->get();
			foreach ($virtualMethods as $virtualMethod) {
				$virtualMethod->status = 0;
				$virtualMethod->save();
			}
			DB::commit();
			return back()->with('success', 'Activated Successfully');
		} catch (\Exception $e) {
			DB::rollBack();
			return back()->with('alert', 'Something Went Wrong');
		}
	}

	public function cardOrder(Request $request)
	{
		$search = $request->all();
		$data['virtualCardMethods'] = VirtualCardMethod::orderBy('name', 'ASC')->get();
		$dateSearch = $request->created_at;
		$date = preg_match("/^[0-9]{2,4}\-[0-9]{1,2}\-[0-9]{1,2}$/", $dateSearch);

		$data['cardOrders'] = VirtualCardOrder::with(['user', 'cardMethod'])->whereIn('status', [0, 2, 3])
			->when(isset($search['user']), function ($query) use ($search) {
				$query->whereHas('user', function ($qq) use ($search) {
					$qq->where('username', 'LIKE', '%' . $search['user'] . '%');
					$qq->orWhere('name', 'LIKE', '%' . $search['user'] . '%');
				});
			})
			->when(isset($search['method_id']), function ($query) use ($search) {
				return $query->where("virtual_card_method_id", $search['method_id']);
			})
			->when(isset($search['status']), function ($query) use ($search) {
				if ($search['status'] == 'approved') {
					return $query->where("status", 1);
				} elseif ($search['status'] == 'pending') {
					return $query->where("status", 0);
				} elseif ($search['status'] == 're-submitted') {
					return $query->where("status", 3);
				} elseif ($search['status'] == 'rejected') {
					return $query->where("status", 2);
				}
			})
			->when($date == 1, function ($query) use ($dateSearch) {
				return $query->whereDate("created_at", $dateSearch);
			})
			->orderBy('id', 'desc')->paginate(config('basic.paginate'));
		return view('admin.virtual_card.cardOrder', $data);
	}

	public function cardOrderDetail($id)
	{
		$data['cardOrderDetail'] = VirtualCardOrder::findOrFail($id);
		return view('admin.virtual_card.cardOrderDetail', $data);
	}

	public function cardOrderRejected(Request $request, $id)
	{
		$purifiedData = Purify::clean($request->all());
		$validator = Validator::make($purifiedData, [
			'reason' => 'required',
		]);

		if ($validator->fails()) {
			return back()->withErrors($validator)->withInput();
		}

		DB::beginTransaction();
		$cardOrder = VirtualCardOrder::where('status', '!=', '2')->findOrFail($id);
		$cardOrder->status = 2;
		$cardOrder->reason = $purifiedData['reason'];
		$cardOrder->resubmitted = $purifiedData['resubmitted'];
		$cardOrder->save();

		if ($this->chargeReturn($cardOrder) == true) {
			DB::commit();
		} else {
			DB::rollBack();
		}
		return back()->with('success', 'Request has been Rejected');
	}

	public function chargeReturn($cardOrder)
	{
		$basicControl = basicControl();
		$chargeCurrency = $cardOrder->charge_currency;
		$virtualCardCharge = $cardOrder->charge;
		$availableBalance = Wallet::where('user_id', $cardOrder->user_id)->where('currency_id', $chargeCurrency)->first();
		if ($availableBalance == null) {
			return false;
		}
		$newBalance = $availableBalance->balance + $virtualCardCharge;
		$availableBalance->balance = $newBalance;
		$availableBalance->save();

		$transaction = new Transaction();
		$transaction->amount = $virtualCardCharge;
		$transaction->charge = 0;
		$transaction->currency_id = $chargeCurrency;
		$cardOrder->transactional()->save($transaction);

		$params = [
			'amount' => $virtualCardCharge,
			'currency' => optional($cardOrder->currency)->code ?? null,
		];
		$action = [
			"link" => "",
			"icon" => "fa fa-money-bill-alt text-white"
		];

		$this->sendMailSms($cardOrder->user, 'VIRTUAL_CARD_REJECTED', $params);
		$this->userPushNotification($cardOrder->user, 'VIRTUAL_CARD_REJECTED', $params, $action);
		$this->userFirebasePushNotification($cardOrder->user, 'VIRTUAL_CARD_REJECTED', $params);

		return true;
	}

	public function cardOrderApprove($id)
	{
		$cardOrder = VirtualCardOrder::with(['cardMethod'])->findOrFail($id);

		$methodObj = 'App\\Services\\VirtualCard\\' . optional($cardOrder->cardMethod)->code . '\\Card';

		$data = $methodObj::cardRequest($cardOrder, 'create');
		if (!$data) {
			return back()->with('alert', 'Method not available or unknown errors occur');
		}

		if ($data['status'] == 'error') {
			$cardOrder->last_error = $data['data'];
			$cardOrder->save();
			return back()->with('alert', $data['data']);
		} elseif ($data['status'] == 'success') {
			$this->cardCreate($cardOrder, $data['data'], $data['name_on_card'], $data['card_id'], $data['cvv'], $data['card_number'], $data['brand'], $data['expiry_date'], $data['balance']);
			$this->cardCreateNotify($cardOrder, $data['name_on_card'], $data['card_id'], $data['cvv'], $data['card_number'], $data['brand'], $data['expiry_date'], $data['balance']);
			return redirect()->route('admin.virtual.cardList')->with('success', 'Card Approved');
		}
	}

	public function cardCreateNotify($cardOrder, $name_on_card, $card_id, $cvv, $card_number, $brand, $expiry_date, $balance)
	{
		$params = [
			'name_on_card' => $name_on_card ?? null,
			'card_id' => $card_id ?? null,
			'cvv' => $cvv ?? null,
			'card_number' => $card_number ?? null,
			'brand' => $brand ?? null,
			'expiry_date' => $expiry_date ?? null,
			'balance' => $balance ?? null,
			'currency' => $cardOrder->currency ?? null,
		];
		$action = [
			"link" => "",
			"icon" => "fa fa-money-bill-alt text-white"
		];

		$this->sendMailSms($cardOrder->user, 'VIRTUAL_CARD_APPROVE', $params);
		$this->userPushNotification($cardOrder->user, 'VIRTUAL_CARD_APPROVE', $params, $action);
		$this->userFirebasePushNotification($cardOrder->user, 'VIRTUAL_CARD_APPROVE', $params);
	}

	public function cardCreate($cardOrder, $data, $name_on_card, $card_id, $cvv, $card_number, $brand, $expiry_date, $balance)
	{
		$reqFieldSpecification = [];
		foreach ($data as $inKey => $inVal) {
			$reqFieldSpecification[$inKey] = [
				'field_name' => $inKey,
				'field_value' => $inVal,
			];
		}
		$cardOrder->card_info = $reqFieldSpecification;
		$cardOrder->balance = $balance;
		$cardOrder->card_id = $card_id;
		$cardOrder->cvv = $cvv;
		$cardOrder->card_number = $card_number;
		$cardOrder->expiry_date = $expiry_date;
		$cardOrder->brand = $brand;
		$cardOrder->name_on_card = $name_on_card;
		$cardOrder->status = 1;
		$cardOrder->save();

		return 0;
	}

	public function cardList($type = null, Request $request)
	{
		$search = $request->all();
		$data['virtualCardMethods'] = VirtualCardMethod::orderBy('name', 'ASC')->get();
		$dateSearch = $request->expiry_date;
		$date = preg_match("/^[0-9]{2,4}\-[0-9]{1,2}\-[0-9]{1,2}$/", $dateSearch);

		$data['cardLists'] = VirtualCardOrder::cards()->with(['cardMethod'])
			->when($type == 'block', function ($query) {
				return $query->where("status", 5);
			})
			->when($type == 'add-fund', function ($query) {
				return $query->where("status", 8);
			})
			->when(isset($search['user']), function ($query) use ($search) {
				$query->whereHas('user', function ($qq) use ($search) {
					$qq->where('username', 'LIKE', '%' . $search['user'] . '%');
					$qq->orWhere('name', 'LIKE', '%' . $search['user'] . '%');
				});
			})
			->when(isset($search['method_id']), function ($query) use ($search) {
				return $query->where("virtual_card_method_id", $search['method_id']);
			})
			->when(isset($search['card_number']), function ($query) use ($search) {
				return $query->where("card_number", $search['card_number']);
			})
			->when(isset($search['status']), function ($query) use ($search) {
				if ($search['status'] == 'approved') {
					return $query->where("status", 1);
				} elseif ($search['status'] == 'rejected') {
					return $query->where("status", 2);
				} elseif ($search['status'] == 'block-request') {
					return $query->where("status", 5);
				} elseif ($search['status'] == 'fund-rejected') {
					return $query->where("status", 6);
				} elseif ($search['status'] == 'block') {
					return $query->where("status", 7);
				} elseif ($search['status'] == 'add-fund-request') {
					return $query->where("status", 8);
				} elseif ($search['status'] == 'inactive') {
					return $query->where("status", 9);
				}
			})
			->when($date == 1, function ($query) use ($dateSearch) {
				return $query->whereDate("expiry_date", $dateSearch);
			})
			->latest()
			->paginate(config('basic . paginate'));
		return view('admin.virtual_card.card.index', $data);
	}

	public function cardView($id)
	{
		$data['cardView'] = VirtualCardOrder::with(['cardMethod'])->findOrFail($id);
		return view('admin.virtual_card.card.view', $data);
	}

	public function cardBlock(Request $request, $id)
	{
		$purifiedData = Purify::clean($request->all());
		$validator = Validator::make($purifiedData, [
			'reason' => 'required',
		]);

		if ($validator->fails()) {
			return back()->withErrors($validator)->withInput();
		}

		$cardOrder = VirtualCardOrder::with(['cardMethod'])->findOrFail($id);

		$methodObj = 'App\\Services\\VirtualCard\\' . optional($cardOrder->cardMethod)->code . '\\Card';
		$data = $methodObj::cardRequest($cardOrder, 'block');

		if (!$data) {
			return back()->with('alert', 'Method not available or unknown errors occur');
		}

		if ($data['status'] == 'error') {
			$cardOrder->last_error = $data['data'];
			$cardOrder->save();
			return back()->with('alert', $data['data']);
		} elseif ($data['status'] == 'success') {

			$cardOrder->status = 5;
			$cardOrder->save();

			$params = [
				'cardNumber' => $cardOrder->card_number ?? null,
			];
			$action = [
				"link" => "",
				"icon" => "fa fa-money-bill-alt text-white"
			];

			$this->sendMailSms($cardOrder->user, 'VIRTUAL_CARD_BLOCK', $params);
			$this->userPushNotification($cardOrder->user, 'VIRTUAL_CARD_BLOCK', $params, $action);
			$this->userFirebasePushNotification($cardOrder->user, 'VIRTUAL_CARD_BLOCK', $params);

			return back()->with('success', 'Card has been blocked');
		}
	}

	public function cardUnBlock($id)
	{
		$cardOrder = VirtualCardOrder::with(['cardMethod'])->where('status', 7)->findOrFail($id);

		$methodObj = 'App\\Services\\VirtualCard\\' . optional($cardOrder->cardMethod)->code . '\\Card';
		$data = $methodObj::cardRequest($cardOrder, 'unblock');

		if (!$data) {
			return back()->with('alert', 'Method not available or unknown errors occur');
		}

		if ($data['status'] == 'error') {
			$cardOrder->last_error = $data['data'];
			$cardOrder->save();
			return back()->with('alert', $data['data']);
		} elseif ($data['status'] == 'success') {
			$cardOrder->status = 1;
			$cardOrder->save();

			$params = [
				'cardNumber' => $cardOrder->card_number ?? null,
			];
			$action = [
				"link" => "",
				"icon" => "fa fa-money-bill-alt text-white"
			];

			$this->sendMailSms($cardOrder->user, 'VIRTUAL_CARD_UNBLOCK', $params);
			$this->userPushNotification($cardOrder->user, 'VIRTUAL_CARD_UNBLOCK', $params, $action);
			$this->userFirebasePushNotification($cardOrder->user, 'VIRTUAL_CARD_UNBLOCK', $params);

			return back()->with('success', 'Card has been blocked');
		}
	}

	public function cardFundApprove($id)
	{
		$cardOrder = VirtualCardOrder::with(['cardMethod'])->where('status', 8)->findOrFail($id);

		$methodObj = 'App\\Services\\VirtualCard\\' . optional($cardOrder->cardMethod)->code . '\\Card';
		$data = $methodObj::cardRequest($cardOrder, 'fundApprove');

		if (!$data) {
			return back()->with('alert', 'Method not available or unknown errors occur');
		}

		if ($data['status'] == 'error') {
			$cardOrder->last_error = $data['data'];
			$cardOrder->save();
			return back()->with('alert', $data['data']);
		} elseif ($data['status'] == 'success') {
			$cardOrder->status = 1;
			$cardOrder->balance = $data['balance'];
			$cardOrder->save();

			$params = [
				'amount' => $cardOrder->fund_amount ?? 0.00,
				'currency' => $cardOrder->currency ?? null,
				'cardNumber' => $cardOrder->card_number ?? null,
			];
			$action = [
				"link" => "",
				"icon" => "fa fa-money-bill-alt text-white"
			];

			$this->sendMailSms($cardOrder->user, 'VIRTUAL_CARD_FUND_APPROVE', $params);
			$this->userPushNotification($cardOrder->user, 'VIRTUAL_CARD_FUND_APPROVE', $params, $action);
			$this->userFirebasePushNotification($cardOrder->user, 'VIRTUAL_CARD_FUND_APPROVE', $params);
			return back()->with('success', 'Card has been funded');
		}
	}

	public function cardFundReturn($id)
	{
		$cardOrder = VirtualCardOrder::where('status', 8)->findOrFail($id);
		$currency = Currency::select('id')->where('code', $cardOrder->currency)->firstOrFail();
		$cardOrder->status = 6;
		$cardOrder->save();

		$userId = $cardOrder->user_id;
		$currencyId = $currency->id;
		$amount = $cardOrder->fund_amount + $cardOrder->fund_charge;
		updateWallet($userId, $currencyId, $amount, 1);

		$transaction = new Transaction();
		$transaction->amount = $amount;
		$transaction->charge = 0;
		$transaction->currency_id = $currencyId;
		$cardOrder->transactional()->save($transaction);

		$params = [
			'amount' => $amount,
			'currency' => $cardOrder->currency ?? null,
			'cardNumber' => $cardOrder->card_number ?? null,
		];
		$action = [
			"link" => "",
			"icon" => "fa fa-money-bill-alt text-white"
		];

		$this->sendMailSms($cardOrder->user, 'VIRTUAL_CARD_FUND_RETURN', $params);
		$this->userPushNotification($cardOrder->user, 'VIRTUAL_CARD_FUND_RETURN', $params, $action);
		$this->userFirebasePushNotification($cardOrder->user, 'VIRTUAL_CARD_FUND_RETURN', $params);

		return back()->with('success', 'Fund has been return');
	}

	public function cardTransaction($id)
	{
		$data['transactions'] = VirtualCardTransaction::with(['user', 'cardOrder.cardMethod'])->where('card_order_id', $id)->orderBy('id', 'desc')->paginate(config('basic.paginate'));
		return view('admin.virtual_card.card.transaction', $data);
	}

	public function ufitpayCallBack(Request $request)
	{
		$apiResponse = json_decode($request->all());
		if ($apiResponse) {
			$card_id = $apiResponse->card_id;
			$cardOrder = VirtualCardOrder::where('card_id', $card_id)->first();
			if ($cardOrder) {
				$virtualCardTran = new VirtualCardTransaction();
				$virtualCardTran->user_id = $cardOrder->user_id;
				$virtualCardTran->card_order_id = $cardOrder->id;
				$virtualCardTran->card_id = $apiResponse->card_id;
				$virtualCardTran->data = $apiResponse;
				$virtualCardTran->amount = $apiResponse->amount;
				$virtualCardTran->currency_code = $apiResponse->currency;
				$virtualCardTran->save();

				$cardOrder->balance = $apiResponse->balance;
				$cardOrder->save();

				$currency = Currency::select('id')->where('code', $apiResponse->currency)->first();
				if ($currency) {
					$transaction = new Transaction();
					$transaction->amount = $apiResponse->amount;
					$transaction->charge = 0.00;
					$transaction->currency_id = $currency->id;
					$virtualCardTran->transactional()->save($transaction);
				}
			}
			return 0;
		}
	}

	public function flutterwavedCallBack(Request $request)
	{
		$apiResponse = json_decode($request->all());
		if ($apiResponse) {
			if ($apiResponse->event == 'charge.completed') {
				$card_id = $apiResponse->data->account_id;
				$cardOrder = VirtualCardOrder::where('card_id', $card_id)->first();
				if ($cardOrder) {
					$virtualCardTran = new VirtualCardTransaction();
					$virtualCardTran->user_id = $cardOrder->user_id;
					$virtualCardTran->card_order_id = $cardOrder->id;
					$virtualCardTran->card_id = $apiResponse->data->account_id;
					$virtualCardTran->data = $apiResponse->data;
					$virtualCardTran->amount = $apiResponse->data->amount;
					$virtualCardTran->currency_code = $apiResponse->data->currency;
					$virtualCardTran->save();

					$methodObj = 'App\\Services\\VirtualCard\\flutterwave\\Card';
					$data = $methodObj::getBalance($apiResponse->data->account_id);

					if ($data) {
						$cardOrder->balance = $data['balance'];
						$cardOrder->save();
					}

					$currency = Currency::select('id')->where('code', $apiResponse->data->currency)->first();
					if ($currency) {
						$transaction = new Transaction();
						$transaction->amount = $apiResponse->data->amount;
						$transaction->charge = $apiResponse->data->charged_amount;
						$transaction->currency_id = $currency->id;
						$virtualCardTran->transactional()->save($transaction);
					}
				}
				return response(200);
			}
		}
	}
}
