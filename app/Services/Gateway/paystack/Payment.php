<?php

namespace App\Services\Gateway\paystack;

use App\Models\ApiOrder;
use App\Models\ApiOrderTest;
use App\Models\Deposit;
use App\Models\Invoice;
use App\Models\ProductOrder;
use App\Models\QRCode;
use App\Models\Voucher;
use Facades\App\Services\BasicCurl;
use Facades\App\Services\BasicService;
use Illuminate\Support\Facades\Auth;

class Payment
{
	public static function prepareData($deposit, $gateway)
	{
		$send['key'] = $gateway->parameters->public_key ?? '';
		$send['email'] = optional($deposit->receiver)->email ?? optional($deposit->depositable)->email ?? basicControl()->sender_email;
		$send['amount'] = round($deposit->payable_amount, 2) * 100;
		$send['currency'] = $deposit->payment_method_currency;
		$send['ref'] = $deposit->utr;
		$send['view'] = $deposit->depositable_type == Voucher::class || $deposit->depositable_type == Invoice::class || $deposit->depositable_type == QRCode::class || $deposit->depositable_type == ApiOrder::class || $deposit->depositable_type == ApiOrderTest::class ? 'frontend.payment.paystack' : ($deposit->depositable_type == ProductOrder::class ? 'user.store.payment.paystack' : 'user.payment.paystack');
		return json_encode($send);
	}

	public static function ipn($request, $gateway, $deposit = null, $trx = null, $type = null)
	{
		$secret_key = $gateway->parameters->secret_key ?? '';
		$url = 'https://api.paystack.co/transaction/verify/' . $trx;
		$headers = [
			"Authorization: Bearer {$secret_key}"
		];
		$response = BasicCurl::curlGetRequestWithHeaders($url, $headers);
		$response = json_decode($response, true);
		if ($response) {
			if ($response['data']) {
				if ($response['data']['status'] == 'success') {
					$deposit = Deposit::with('receiver')->where('utr', $trx)->first();

					$depositAmount = round($deposit->payable_amount, 2) * 100;
					if (round($response['data']['amount'], 2) == $depositAmount && $response['data']['currency'] == $deposit->payment_method_currency) {
						BasicService::prepareOrderUpgradation($deposit);
						$data['status'] = 'success';
						$data['msg'] = 'Transaction was successful.';
						$data['redirect'] = route('success');
					} else {
						$data['status'] = 'error';
						$data['msg'] = 'invalid amount.';
						$data['redirect'] = route('failed');
					}
				} else {
					$data['status'] = 'error';
					$data['msg'] = $response['data']['gateway_response'];
					$data['redirect'] = route('failed');
				}
			} else {
				$data['status'] = 'error';
				$data['msg'] = $response['message'];
				$data['redirect'] = route('failed');
			}
		} else {
			$data['status'] = 'error';
			$data['msg'] = 'unexpected error!';
			$data['redirect'] = route('failed');
		}

		return $data;
	}
}
