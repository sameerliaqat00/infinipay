<?php

namespace App\Services\Gateway\blockchain;

use App\Models\ApiOrder;
use App\Models\ApiOrderTest;
use App\Models\Invoice;
use App\Models\ProductOrder;
use App\Models\QRCode;
use App\Models\Voucher;
use Facades\App\Services\BasicCurl;
use Facades\App\Services\BasicService;

class Payment
{
	public static function prepareData($deposit, $gateway)
	{
		$apiKey = $gateway->parameters->api_key ?? '';
		$xpubCode = $gateway->parameters->xpub_code ?? '';

		$btcPriceUrl = "https://blockchain.info/ticker";
		$btcPriceResponse = BasicCurl::curlGetRequest($btcPriceUrl);
		$btcPriceResponse = json_decode($btcPriceResponse);
		$btcRate = $btcPriceResponse->USD->last;

		$usd = round($deposit->payable_amount, 2);
		$btcamount = $usd / $btcRate;
		$btc = round($btcamount, 8);
		if ($deposit->btc_amount == 0 || $deposit->btc_wallet == "") {
			$secret = $deposit->utr;
			$callback_url = route('ipn', [$gateway->code, $deposit->utr]) . "?invoice_id=" . $deposit->utr . "&secret=" . $secret;
			$url = "https://api.blockchain.info/v2/receive?key={$apiKey}&callback=" . urlencode($callback_url) . "&xpub={$xpubCode}";
			$response = BasicCurl::curlGetRequest($url);
			$response = json_decode($response);
			if (@$response->address == '') {
				$send['error'] = true;
				$send['message'] = 'BLOCKCHAIN API HAVING ISSUE. PLEASE TRY LATER. ' . $response->message;
			} else {
				$deposit['btc_wallet'] = $response->address;
				$deposit['btc_amount'] = $btc;
				$deposit->update();
			}
		}

		$send['amount'] = $deposit->btc_amount;
		$send['sendto'] = $deposit->btc_wallet;
		$send['img'] = BasicService::cryptoQR($deposit->btc_wallet, $deposit->btc_amount);
		$send['currency'] = $deposit->payment_method_currency ?? 'BTC';
		$send['view'] = $deposit->depositable_type == Voucher::class || $deposit->depositable_type == Invoice::class || $deposit->depositable_type == QRCode::class || $deposit->depositable_type == ApiOrder::class || $deposit->depositable_type == ApiOrderTest::class ? 'frontend.payment.crypto' : ($deposit->depositable_type == ProductOrder::class ? 'user.store.payment.crypto' : 'user.payment.crypto');;
		return json_encode($send);
	}

	public static function ipn($request, $gateway, $deposit = null, $trx = null, $type = null)
	{
		$btc = $request->value / 100000000;
		if ($deposit->btc_amount == $btc && $request->address == $deposit->btc_wallet && $request->secret == $deposit->utr && $request->confirmations > 2 && $deposit->status == 0) {
			BasicService::prepareOrderUpgradation($deposit);

			$data['status'] = 'success';
			$data['msg'] = 'Transaction was successful.';
			$data['redirect'] = route('success');
		} else {
			$data['status'] = 'error';
			$data['msg'] = 'Invalid response.';
			$data['redirect'] = route('failed');
		}
		return $data;
	}
}
