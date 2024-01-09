<?php

namespace App\Http\Controllers;

use App\Models\ChargesLimit;
use App\Models\Deposit;
use App\Models\Gateway;
use App\Models\ProductOrder;
use App\Models\ProductOrderDetail;
use App\Models\Store;
use App\Models\StoreProductStock;
use App\Models\StoreShipping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Stevebauman\Purify\Facades\Purify;

class PublicChekoutController extends Controller
{
	public function productCheckout($link)
	{
		$store = Store::with('user')->where('link', $link)->firstOrFail();
		$data['link'] = $link;
		$data['shippingAdds'] = StoreShipping::where('status', 1)->where('store_id', $store->id)->orderBy('address', 'asc')->get();
		$data['gateways'] = Gateway::where('status', 1)->get();
		return view('user.store.shop.checkout', $data, compact('store'));
	}

	public function productCheckoutStore(Request $request)
	{
		$purifiedData = Purify::clean($request->all());
		$validator = Validator::make($purifiedData, [
			'name' => 'required',
			'email' => 'required',
			'phone' => 'required',
			'shippingId' => 'required',
			'detailAddress' => 'required',
			'methodId' => 'required',
		]);
		if ($validator->fails()) {
			return response()->json([
				'status' => 'error'
			]);
		}

		$cartItem = $request->cartItem;
		if (count($cartItem) < 1) {
			return response()->json([
				'status' => 'emptyCart'
			]);
		}

		foreach ($cartItem as $item) {
			$stock = StoreProductStock::with('product')->where('product_id', $item['id'])->whereJsonContains('product_attr_lists_id', $item['attributes'])->first();
			if ($item['count'] > $stock->quantity) {
				$productName = optional($stock->product)->name ?? 'Unknown Item';
				return response()->json([
					'status' => 'stockOut',
					'product_name' => $productName
				]);
			}
		}

		$totalAmount = 0;
		$shipping = StoreShipping::with('store.user', 'user')->where('id', $request->shippingId)->first();

		foreach ($cartItem as $item) {
			$totalAmount += $item['price'] * $item['quantity'];
		}

		$amount = $totalAmount;
		$currency_id = optional($shipping->store->user)->store_currency_id;
		$methodId = $request->methodId;
		$shippingCharge = 0;
		if (optional($shipping->store)->shipping_charge == 1) {
			$shippingCharge = $shipping->charge;
		}

		$checkAmountValidate = $this->checkAmountValidate($amount, $currency_id, config('transactionType.deposit'), $methodId, $shippingCharge);//7 = deposit
		if (!$checkAmountValidate['status']) {
			return response()->json([
				'status' => 'fail'
			]);
		}

		DB::beginTransaction();
		$order = new ProductOrder();
		$order->order_number = mt_rand(10000000, 99999999);
		$order->gateway_id = $request->methodId;
		$order->store_id = $shipping->store_id;
		$order->user_id = $shipping->user_id;
		$order->currency_id = optional($shipping->user)->store_currency_id;
		$order->fullname = $request->name;
		$order->email = $request->email;
		$order->phone = $request->phone;
		$order->alt_phone = $request->altPhone;
		$order->total_amount = $totalAmount;
		$order->shipping_id = $shipping->id;
		$order->shipping_charge = $shippingCharge;
		$order->detailed_address = $request->detailAddress;
		$order->order_note = $request->orderNote;
		$order->save();

		foreach ($cartItem as $item) {
			$orderDetails = new ProductOrderDetail();
			$orderDetails->order_id = $order->id;
			$orderDetails->product_id = $item['id'];
			$orderDetails->attributes_id = $item['attributes'];
			$orderDetails->quantity = $item['count'];
			$orderDetails->price = $item['price'];
			$orderDetails->total_price = $item['quantity'] * $item['price'];
			$orderDetails->save();
		}

		$method = Gateway::find($methodId);

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
		$deposit->email = $request->email;
		$deposit->payment_method_currency = $method->currency;
		$deposit->depositable_id = $order->id;
		$deposit->depositable_type = ProductOrder::class;
		$deposit->save();

		$order->utr = $deposit->utr;
		$order->save();

		$checkAmountValidate = (new DepositController())->checkAmountValidate($deposit->amount, $deposit->currency_id, config('transactionType.deposit'), $deposit->payment_method_id);
		if (!$checkAmountValidate['status']) {
			return response()->json([
				'status' => 'fail'
			]);
		}
		DB::commit();
		return response()->json([
			'status' => 'success',
			'route' => route('payment.process', $deposit->utr)
		]);

	}

	public function checkAmountValidate($amount, $currency_id, $transaction_type_id, $methodId, $shippingCharge = null)
	{
		$chargesLimit = ChargesLimit::where(['currency_id' => $currency_id, 'transaction_type_id' => $transaction_type_id, 'payment_method_id' => $methodId, 'is_active' => 1])->firstOrFail();

		$balance = 0;


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

		$payable_amount = $amount + $charge + $shippingCharge;

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

}
