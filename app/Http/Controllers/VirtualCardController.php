<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\TwoFactorSetting;
use App\Models\VirtualCardMethod;
use App\Models\VirtualCardOrder;
use App\Models\VirtualCardTransaction;
use App\Models\Wallet;
use App\Traits\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class VirtualCardController extends Controller
{
	use Notify;

	public function index()
	{
		$basicControl = basicControl();
		$orderLock = 'false';
		$checkOrder = VirtualCardOrder::where('user_id', auth()->id())->whereIn('status', [0, 3, 4])->latest()->exists();

		if ($checkOrder) {
			$orderLock = 'true';
		}
		if ($basicControl->v_card_multiple == 0) {
			$checkOrder = VirtualCardOrder::where('user_id', auth()->id())->where('status', 1)->latest()->exists();
			if ($checkOrder) {
				$orderLock = 'true';
			}
		}

		$data['cardOrder'] = VirtualCardOrder::where('user_id', auth()->id())->where('status', '!=', 1)->latest()->first();
		$data['template'] = Template::where('section_name', 'virtual-card')->first();
		$data['approveCards'] = VirtualCardOrder::cards()->where('user_id', auth()->id())->latest()->get();
		return view('user.virtual_card.cardForm', $data, compact('orderLock'));
	}

	public function order()
	{
		$basicControl = basicControl();
		$checkOrder = VirtualCardOrder::where('user_id', auth()->id())->whereIn('status', [0, 3, 4])->latest()->exists();
		if ($checkOrder) {
			return back()->with('alert', 'You can not eligible for request card');
		}
		if ($basicControl->v_card_multiple == 0) {
			$checkOrder = VirtualCardOrder::where('user_id', auth()->id())->where('status', 1)->latest()->exists();
			if ($checkOrder) {
				return back()->with('alert', 'You can not eligible for multiple card');
			}
		}

		$data['virtualCardMethod'] = VirtualCardMethod::where('status', 1)->firstOrFail();
		return view('user.virtual_card.orderCard', $data);
	}

	public function orderSubmit(Request $request)
	{
		$purifiedData = Purify::clean($request->all());
		$validationRules = [
			'currency' => 'required',
		];

		$validate = Validator::make($purifiedData, $validationRules);
		if ($validate->fails()) {
			return back()->withErrors($validate)->withInput();
		}

		if ($this->checkUserBalance() == false) {
			return back()->withInput()->with('alert', 'Please add fund your ' . config('basic.base_currency_code') . ' wallet');
		}

		$virtualCardMethod = VirtualCardMethod::where('status', 1)->firstOrFail();
		$virtualCardOrder = new VirtualCardOrder();

		$purifiedData = (object)$purifiedData;

		$rulesSpecification = [];
		$inputFieldSpecification = [];
		if ($virtualCardMethod->form_field != null) {
			foreach ($virtualCardMethod->form_field as $key => $cus) {
				$rulesSpecification[$key] = [$cus->validation];
				if ($cus->type == 'file') {
					array_push($rulesSpecification[$key], 'image');
					array_push($rulesSpecification[$key], 'mimes:jpeg,jpg,png');
					array_push($rulesSpecification[$key], 'max:2048');
				}
				if ($cus->type == 'text') {
					array_push($rulesSpecification[$key], 'max:191');
				}
				if ($cus->type == 'textarea') {
					array_push($rulesSpecification[$key], 'max:300');
				}
				$inputFieldSpecification[] = $key;
			}
		}
		$this->validate($request, $rulesSpecification);

		$collectionSpecification = collect($request);
		$reqFieldSpecification = [];
		if ($virtualCardMethod->form_field != null) {
			foreach ($collectionSpecification as $k => $v) {
				foreach ($virtualCardMethod->form_field as $inKey => $inVal) {
					if ($k != $inKey) {
						continue;
					} else {
						if ($inVal->type == 'file') {
							if ($request->hasFile($inKey)) {

								try {
									$image = $request->file($inKey);
									$location = config('location.virtualCardOrder.path');
									$filename = $this->uploadImage($image, $location);;
									$reqField[$inKey] = [
										'field_name' => $inKey,
										'field_value' => $filename,
										'field_level' => $inVal->field_level,
										'type' => $inVal->type,
										'validation' => $inVal->validation,
									];

								} catch (\Exception $exp) {
									return back()->with('error', 'Image could not be uploaded.')->withInput();
								}

							}
						} else {
							$reqFieldSpecification[$inKey] = [
								'field_name' => $inKey,
								'field_value' => $v,
								'field_level' => $inVal->field_level,
								'type' => $inVal->type,
								'validation' => $inVal->validation,
							];
						}
					}
				}
			}
			$virtualCardOrder->form_input = $reqFieldSpecification;
		} else {
			$virtualCardOrder->form_input = null;
		}

		$virtualCardOrder->virtual_card_method_id = $virtualCardMethod->id;
		$virtualCardOrder->user_id = auth()->id();
		$virtualCardOrder->currency = $purifiedData->currency;
		$virtualCardOrder->status = 4;
		$virtualCardOrder->save();

		return redirect()->route('order.confirm', $virtualCardOrder->id)->with('success', 'Request initiated successfully');
	}

	public function confirmOrder(Request $request, $orderId)
	{
		$user = Auth::user();
		$order = VirtualCardOrder::with('user', 'cardMethod')->where('user_id', auth()->id())->where('id', $orderId)->firstOrFail();
		if (!$order || $order->status != 4) { //Check is transaction found and unpaid
			return redirect(route('user.virtual.card'))->with('success', 'Request already send');
		}

		$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
		$enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);

		if ($request->isMethod('get')) {
			return view('user.virtual_card.confirm', compact(['orderId', 'order', 'enable_for']));
		} elseif ($request->isMethod('post')) {
			// Security PIN check and validation
			if (in_array('virtual_card', $enable_for)) {
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
			$this->chargePay($order);
			$order->status = 0;
			$order->save();
			return redirect()->route('user.virtual.card')->with('success', 'Your virtual card request is send');
		}
	}

	public function orderReSubmit(Request $request)
	{
		$data['virtualCardMethod'] = VirtualCardMethod::where('status', 1)->firstOrFail();
		$data['cardOrder'] = VirtualCardOrder::where('user_id', auth()->id())->latest()->firstOrFail();
		if ($request->method() == 'GET') {
			return view('user.virtual_card.reOrderCard', $data);
		} else {
			$purifiedData = Purify::clean($request->except('image', '_token', '_method'));
			$rules = [
				'currency' => 'required',
			];
			$message = [
				'currency.required' => 'Currency field is required',
			];

			$validate = Validator::make($purifiedData, $rules, $message);

			if ($validate->fails()) {
				return back()->withInput()->withErrors($validate);
			}

			if ($this->checkUserBalance() == false) {
				return back()->withInput()->with('alert', 'Please add fund your ' . config('basic.base_currency_code') . ' wallet');
			}

			$rulesSpecification = [];
			$inputFieldSpecification = [];
			if ($data['virtualCardMethod']->form_field != null) {
				foreach ($data['virtualCardMethod']->form_field as $key => $cus) {
					$rulesSpecification[$key] = [$cus->validation];
					if ($cus->type == 'file') {
						array_push($rulesSpecification[$key], 'image');
						array_push($rulesSpecification[$key], 'mimes:jpeg,jpg,png');
						array_push($rulesSpecification[$key], 'max:2048');
					}
					if ($cus->type == 'text') {
						array_push($rulesSpecification[$key], 'max:191');
					}
					if ($cus->type == 'textarea') {
						array_push($rulesSpecification[$key], 'max:300');
					}
					$inputFieldSpecification[] = $key;
				}
			}
			$this->validate($request, $rulesSpecification);

			$collectionSpecification = collect($request);
			$reqFieldSpecification = [];
			if ($data['virtualCardMethod']->form_field != null) {
				foreach ($collectionSpecification as $k => $v) {
					foreach ($data['virtualCardMethod']->form_field as $inKey => $inVal) {
						if ($k != $inKey) {
							continue;
						} else {
							if ($inVal->type == 'file') {
								if ($request->hasFile($inKey)) {

									try {
										$image = $request->file($inKey);
										$location = config('location.virtualCardOrder.path');
										$filename = $this->uploadImage($image, $location);;
										$reqField[$inKey] = [
											'field_name' => $inKey,
											'field_value' => $filename,
											'field_level' => $inVal->field_level,
											'type' => $inVal->type,
											'validation' => $inVal->validation,
										];

									} catch (\Exception $exp) {
										return back()->with('error', 'Image could not be uploaded.')->withInput();
									}

								}
							} else {
								$reqFieldSpecification[$inKey] = [
									'field_name' => $inKey,
									'field_value' => $v,
									'field_level' => $inVal->field_level,
									'type' => $inVal->type,
									'validation' => $inVal->validation,
								];
							}
						}
					}
				}
				$data['cardOrder']->form_input = $reqFieldSpecification;
			} else {
				$data['cardOrder']->form_input = null;
			}

			$data['cardOrder']->currency = $purifiedData['currency'];
			$data['cardOrder']->status = 3;
			$data['cardOrder']->save();

			$this->chargePay($data['cardOrder']);

			return redirect()->route('user.virtual.card')->with('success', 'Re Submitted Successfully');
		}
	}

	public function checkUserBalance()
	{
		$basicControl = basicControl();
		$baseCurrency = $basicControl->base_currency;
		$virtualCardCharge = $basicControl->v_card_charge;

		$availableBalance = Wallet::select('balance')->where('user_id', auth()->id())->where('currency_id', $baseCurrency)->first();
		if ($availableBalance->balance > $virtualCardCharge) {
			return true;
		} else {
			return false;
		}
	}

	public function chargePay($cardOrder)
	{
		$basicControl = basicControl();
		$baseCurrency = $basicControl->base_currency;
		$virtualCardCharge = $basicControl->v_card_charge;
		$availableBalance = Wallet::where('user_id', auth()->id())->where('currency_id', $baseCurrency)->first();

		$newBalance = $availableBalance->balance - $virtualCardCharge;
		$availableBalance->balance = $newBalance;
		$availableBalance->save();

		$transaction = new Transaction();
		$transaction->amount = $virtualCardCharge;
		$transaction->charge = 0;
		$transaction->currency_id = $baseCurrency;
		$cardOrder->transactional()->save($transaction);

		$cardOrder->charge = $virtualCardCharge;
		$cardOrder->charge_currency = $baseCurrency;
		$cardOrder->save();

		$params = [
			'amount' => $virtualCardCharge,
			'currency' => config('basic.base_currency_code'),
		];
		$action = [
			"link" => "",
			"icon" => "fa fa-money-bill-alt text-white"
		];

		$this->sendMailSms(auth()->user(), 'VIRTUAL_CARD_APPLY', $params);
		$this->userPushNotification(auth()->user(), 'VIRTUAL_CARD_APPLY', $params, $action);
		$this->userFirebasePushNotification(auth()->user(), 'VIRTUAL_CARD_APPLY', $params);

		$params = [
			'username' => optional(auth()->user())->username ?? null,
			'amount' => $virtualCardCharge,
			'currency' => config('basic.base_currency_code'),
		];

		$action = [
			"link" => route('admin.virtual.cardOrderDetail', $cardOrder->id),
			"icon" => "fa fa-money-bill-alt text-white"
		];
		$firebaseAction = route('admin.virtual.cardOrderDetail', $cardOrder->id);
		$this->adminMail('ADMIN_VIRTUAL_CARD_APPLY', $params);
		$this->adminPushNotification('ADMIN_VIRTUAL_CARD_APPLY', $params, $action);
		$this->adminFirebasePushNotification('ADMIN_VIRTUAL_CARD_APPLY', $params, $firebaseAction);

		return 0;
	}

	public function cardBlock(Request $request, $id)
	{
		$purifiedData = Purify::clean($request->except('_token', '_method'));
		$rules = [
			'reason' => 'required',
		];
		$message = [
			'reason.required' => 'Reason field is required',
		];

		$validate = Validator::make($purifiedData, $rules, $message);

		if ($validate->fails()) {
			return back()->withInput()->withErrors($validate);
		}
		$card = VirtualCardOrder::findOrFail($id);
		if ($card->user_id != auth()->id()) {
			return back()->with('alert', 'You have not permission');
		}
		$card->status = 5;
		$card->reason = $purifiedData['reason'];
		$card->save();
		return back()->with('success', 'Block Request Send');
	}

	public function cardTransaction($card_id)
	{
		$data['cardTransactions'] = VirtualCardTransaction::where('user_id', auth()->id())->where('card_id', $card_id)->latest()->paginate(config('basic.paginate'));
		return view('user.virtual_card.transaction', $data, compact('card_id'));
	}
}
