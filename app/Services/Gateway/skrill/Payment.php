<?php

namespace App\Services\Gateway\skrill;

use App\Models\ApiOrder;
use App\Models\ApiOrderTest;
use App\Models\Invoice;
use App\Models\ProductOrder;
use App\Models\QRCode;
use App\Models\Voucher;
use Facades\App\Services\BasicService;

class Payment
{
	/*
	 * Skrill Gateway
	 */
	public static function prepareData($deposit, $gateway)
	{
		$basic = basicControl();
		$val['pay_to_email'] = trim($gateway->parameters->pay_to_email);
		$val['transaction_id'] = "$deposit->trx";
		$val['return_url'] = route('success');
		$val['return_url_text'] = "Return $basic->site_title";
		$val['cancel_url'] = route('fund.index');
		$val['status_url'] = route('ipn', [$gateway->code, $deposit->utr]);
		$val['language'] = 'EN';
		$val['amount'] = round($deposit->payable_amount, 2);
		$val['currency'] = "$deposit->payment_method_currency";
		$val['detail1_description'] = "$basic->site_title";
		$val['detail1_text'] = "Pay To $basic->site_title";
		$val['logo_url'] = getFile(config('location.logo.path') . 'logo.png');
		$send['val'] = $val;
		$send['view'] = $deposit->depositable_type == Voucher::class || $deposit->depositable_type == Invoice::class || $deposit->depositable_type == QRCode::class || $deposit->depositable_type == ApiOrder::class || $deposit->depositable_type == ApiOrderTest::class ? 'frontend.payment.redirect' : ($deposit->depositable_type == ProductOrder::class ? 'user.store.payment.redirect' : 'user.payment.redirect');
		$send['method'] = 'post';
		$send['url'] = 'https://www.moneybookers.com/app/payment.pl';
		return json_encode($send);
	}

	public static function ipn($request, $gateway, $deposit = null, $trx = null, $type = null)
	{
		$concatFields = $request->merchant_id
			. $request->transaction_id
			. strtoupper(md5(trim($gateway->parameters->secret_key)))
			. $request->mb_amount
			. $request->mb_currency
			. $request->status;

		if (strtoupper(md5($concatFields)) == $request->md5sig && $request->status == 2 && $request->pay_to_email == trim($gateway->parameters->pay_to_email) && $deposit->status = '0') {
			BasicService::prepareOrderUpgradation($deposit);
		}
	}
}
