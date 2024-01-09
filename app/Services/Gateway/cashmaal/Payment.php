<?php

namespace App\Services\Gateway\cashmaal;

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
		$val['pay_method'] = " ";
		$val['amount'] = round($deposit->payable_amount, 2);
		$val['currency'] = $deposit->payment_method_currency;
		$val['succes_url'] = route('success');
		$val['cancel_url'] = twoStepPrevious($deposit);
		$val['client_email'] = $deposit->email;
		$val['web_id'] = $gateway->parameters->web_id;
		$val['order_id'] = $deposit->utr;
		$val['addi_info'] = "Payment";
		$send['url'] = 'https://www.cashmaal.com/Pay/';
		$send['method'] = 'post';
		$send['view'] = $deposit->depositable_type == Voucher::class || $deposit->depositable_type == Invoice::class || $deposit->depositable_type == QRCode::class || $deposit->depositable_type == ApiOrder::class || $deposit->depositable_type == ApiOrderTest::class ? 'frontend.payment.redirect' : ($deposit->depositable_type == ProductOrder::class ? 'user.store.payment.redirect' : 'user.payment.redirect');;
		$send['val'] = $val;

		return json_encode($send);
	}

	public static function ipn($request, $gateway, $deposit = null, $trx = null, $type = null)
	{
		$deposit = Deposit::where('utr', $request->order_id)->orderBy('id', 'desc')->first();
		if ($deposit) {
			if ($request->currency == $deposit->payment_method_currency && ($request->Amount == round($deposit->payable_amount, 2)) && $deposit->status == 0) {
				BasicService::prepareOrderUpgradation($deposit);
			}
		}
	}
}
