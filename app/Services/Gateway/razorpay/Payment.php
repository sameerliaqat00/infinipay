<?php

namespace App\Services\Gateway\razorpay;

use App\Models\ApiOrder;
use App\Models\ApiOrderTest;
use App\Models\Invoice;
use App\Models\ProductOrder;
use App\Models\QRCode;
use App\Models\Voucher;
use Facades\App\Services\BasicService;
use Razorpay\Api\Api;

require_once('razorpay-php/Razorpay.php');

class Payment
{
	public static function prepareData($deposit, $gateway)
	{
		$basic = basicControl();
		$api_key = $gateway->parameters->key_id ?? '';
		$api_secret = $gateway->parameters->key_secret ?? '';
		$razorPayApi = new Api($api_key, $api_secret);
		$finalAmount = round($deposit->payable_amount, 2) * 100;
		$gatewayCurrency = $deposit->payment_method_currency;
		$trx = $deposit->utr;

		$razorOrder = $razorPayApi->order->create(
			array(
				'receipt' => $trx,
				'amount' => $finalAmount,
				'currency' => $gatewayCurrency,
				'payment_capture' => '0'
			)
		);

		$val['key'] = $api_key;
		$val['amount'] = $finalAmount;
		$val['currency'] = $gatewayCurrency;
		$val['order_id'] = $razorOrder['id'];
		$val['buttontext'] = "Pay Now";
		$val['name'] = optional($deposit->receiver)->name ?? $basic->site_title;
		$val['description'] = "Payment By Razorpay";
		$val['image'] = getFile(config('location.logo.path') . 'logo.png');
		$val['prefill.name'] = optional($deposit->receiver)->name ?? $basic->site_title;
		$val['prefill.email'] = optional($deposit->receiver)->email ?? optional($deposit->depositable)->email ?? $basic->sender_email;
		$val['prefill.contact'] = optional($deposit->receiver)->phone ?? '';
		$val['theme.color'] = "#2ecc71";
		$send['val'] = $val;

		$send['method'] = 'POST';
		$send['url'] = route('ipn', [$gateway->code, $deposit->trx]);
		$send['custom'] = $trx;
		$send['checkout_js'] = "https://checkout.razorpay.com/v1/checkout.js";
		$send['view'] = $deposit->depositable_type == Voucher::class || $deposit->depositable_type == Invoice::class || $deposit->depositable_type == QRCode::class || $deposit->depositable_type == ApiOrder::class || $deposit->depositable_type == ApiOrderTest::class ? 'frontend.payment.razorpay' : ($deposit->depositable_type == ProductOrder::class ? 'user.store.payment.razorpay' : 'user.payment.razorpay');
		return json_encode($send);
	}

	public static function ipn($request, $gateway, $order = null, $trx = null, $type = null)
	{
		$api_secret = $gateway->parameters->key_secret ?? '';
		$signature = hash_hmac('sha256', $request->razorpay_order_id . "|" . $request->razorpay_payment_id, $api_secret);

		if ($signature == $request->razorpay_signature) {
			BasicService::prepareOrderUpgradation($order);

			$data['status'] = 'success';
			$data['msg'] = 'Transaction was successful.';
			$data['redirect'] = route('success');
		} else {
			$data['status'] = 'error';
			$data['msg'] = 'unexpected error!';
			$data['redirect'] = route('failed');
		}

		return $data;
	}
}
