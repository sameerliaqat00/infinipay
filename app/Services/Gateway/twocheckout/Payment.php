<?php

namespace App\Services\Gateway\twocheckout;

use App\Models\ApiOrder;
use App\Models\ApiOrderTest;
use App\Models\Deposit;
use App\Models\Invoice;
use App\Models\ProductOrder;
use App\Models\QRCode;
use App\Models\Voucher;
use Facades\App\Services\BasicService;

class Payment
{
	public static function prepareData($deposit, $gateway)
	{
		$basic = basicControl();
		$send['val'] = [
			'sid' => $gateway->parameters->merchant_code,
			'mode' => '2CO',
			'li_0_type' => 'product',
			'li_0_name' => "Pay to $basic->site_title",
			'li_0_product_id' => "{$deposit->utr}",
			'li_0_price' => round($deposit->payable_amount, 2),
			'li_0_quantity' => "1",
			'li_0_tangible' => "N",
			'currency_code' => $deposit->payment_method_currency,
			'demo' => "Y",
		];
		$send['view'] = $deposit->depositable_type == Voucher::class || $deposit->depositable_type == Invoice::class || $deposit->depositable_type == QRCode::class || $deposit->depositable_type == ApiOrder::class || $deposit->depositable_type == ApiOrderTest::class ? 'frontend.payment.redirect' : ($deposit->depositable_type == ProductOrder::class ? 'user.store.payment.redirect' : 'user.payment.redirect');
		$send['method'] = 'post';
		$send['url'] = 'https://www.2checkout.com/checkout/purchase';
		return json_encode($send);
	}

	public static function ipn($request, $gateway, $deposit = null, $trx = null, $type = null)
	{
		$hashSecretWord = $gateway->parameters->secret_key;
		$hashSid = $gateway->parameters->merchant_code;
		$deposit = Deposit::where('utr', $request->li_0_product_id)->first();
		$hashTotal = round($deposit->payable_amount, 2);
		$hashOrder = $request->order_number;
		$StringToHash = strtoupper(md5($hashSecretWord . $hashSid . $hashOrder . $hashTotal));

		if ($StringToHash != $request->key) {
			BasicService::prepareOrderUpgradation($deposit);

			$data['status'] = 'success';
			$data['msg'] = 'Transaction was successful.';
			$data['redirect'] = route('success');
		} else {
			$data['status'] = 'error';
			$data['msg'] = 'unsuccessful';
			$data['redirect'] = route('failed');
		}
		return $data;
	}
}
