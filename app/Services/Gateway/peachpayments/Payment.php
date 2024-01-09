<?php

namespace App\Services\Gateway\peachpayments;

use App\Models\ApiOrder;
use App\Models\ApiOrderTest;
use App\Models\Invoice;
use App\Models\ProductOrder;
use App\Models\QRCode;
use App\Models\Voucher;
use Facades\App\Services\BasicService;


class Payment
{
	public static function prepareData($deposit, $gateway)
	{
		$Entity_ID = trim($gateway->parameters->Entity_ID);
		$amount = trim(round($deposit->payable_amount));
		$currency = trim(strtoupper($deposit->payment_method_currency));
		if ($gateway->environment == 'test' || $deposit->mode == 1) {
			$url = "https://test.oppwa.com/v1/checkouts";
		} else {
			$url = "https://oppwa.com/v1/checkouts";
		}
		$data = "entityId=$Entity_ID" .
			"&amount=$amount" .
			"&currency=$currency" .
			"&paymentType=DB";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization:Bearer ' . $gateway->parameters->Authorization_Bearer)); // client
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// this should be set to true in production
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$responseData = curl_exec($ch);
		if (curl_errno($ch)) {
			return curl_error($ch);
		}
		curl_close($ch);

		$result = json_decode($responseData);

		$deposit->btc_wallet = $result->id;
		$deposit->save();
		$send['view'] = $deposit->depositable_type == Voucher::class || $deposit->depositable_type == Invoice::class || $deposit->depositable_type == QRCode::class || $deposit->depositable_type == ApiOrder::class || $deposit->depositable_type == ApiOrderTest::class ? 'frontend.payment.peachpayments' : ($deposit->depositable_type == ProductOrder::class ? 'user.store.payment.peachpayments' : 'user.payment.peachpayments');

		$send['checkoutId'] = $result->id;
		$send['environment'] = $gateway->environment;
		$send['mode'] = $deposit->mode;

		$send['url'] = route('ipn', [$gateway->code, $deposit->utr]);
		return json_encode($send);
	}

	public static function ipn($request, $gateway, $deposit = null, $trx = null, $type = null)
	{

		$id = $request->id;
		if ($gateway->environment == 'test') {
			$url = "https://test.oppwa.com/v1/checkouts";
		} else {
			$url = "https://oppwa.com/v1/checkouts";
		}
		$url .= "?entityId=" . $gateway->parameters->Entity_ID;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization:Bearer ' . $gateway->parameters->Authorization_Bearer));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// this should be set to true in production
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$responseData = curl_exec($ch);
		if (curl_errno($ch)) {
			return curl_error($ch);
		}
		curl_close($ch);
		$response = json_decode($responseData);
		//$deposit->response = $response;
		//$deposit->save();

		if (@$response->result->code == '000.100.110') {
			BasicService::prepareOrderUpgradation($deposit);
			$data['status'] = 'success';
			$data['msg'] = 'Transaction was successful.';
			$data['redirect'] = route('success');
		} else {
			$data['status'] = 'error';
			$data['msg'] = @$response->result->description;
			$data['redirect'] = route('failed');
		}
		return $data;

	}
}
