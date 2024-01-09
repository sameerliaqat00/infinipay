<?php

namespace App\Services\Gateway\securionpay;

use App\Models\ApiOrder;
use App\Models\ApiOrderTest;
use App\Models\Invoice;
use App\Models\ProductOrder;
use App\Models\QRCode;
use App\Models\Voucher;
use Facades\App\Services\BasicService;
use SecurionPay\Exception\SecurionPayException;
use SecurionPay\SecurionPayGateway;

class Payment
{
	public static function prepareData($deposit, $gateway)
	{
		$send['view'] = $deposit->depositable_type == Voucher::class || $deposit->depositable_type == Invoice::class || $deposit->depositable_type == QRCode::class || $deposit->depositable_type == ApiOrder::class || $deposit->depositable_type == ApiOrderTest::class ? 'frontend.payment.card' : ($deposit->depositable_type == ProductOrder::class ? 'user.store.payment.card' : 'user.payment.card');
		return json_encode($send);
	}

	public static function ipn($request, $gateway, $deposit = null, $trx = null, $type = null)
	{
		$prepareGateway = new SecurionPayGateway($gateway->parameters->secret_key);
		$finalAmount = ceil($deposit->payable_amount);
		$request = array(
			'amount' => $finalAmount,
			'currency' => $deposit->payment_method_currency,
			'card' => array(
				'number' => $request->card_number,
				'expMonth' => $request->expiry_month,
				'expYear' => $request->expiry_year
			)
		);

		try {
			$charge = $prepareGateway->createCharge($request);

			if ($charge->getAmount() == $finalAmount && $charge->getCurrency() == $deposit->payment_method_currency) {
				BasicService::prepareOrderUpgradation($deposit);

				$data['status'] = 'success';
				$data['msg'] = 'Transaction was successful.';
				$data['redirect'] = route('success');
			} else {
				$data['status'] = 'error';
				$data['msg'] = 'unsuccessful transaction.';
				$data['redirect'] = route('failed');
			}

		} catch (SecurionPayException $e) {
			$data['status'] = 'error';
			$data['msg'] = $e->getMessage();
			$data['redirect'] = route('failed');
		}
		return $data;
	}
}
