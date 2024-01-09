<?php

namespace App\Services\Payout\paypal;

use App\Models\PayoutMethod;
use App\Models\RazorpayContact;
use Facades\App\Services\BasicCurl;

class Card
{
	public static function payouts($payout)
	{
		$method = PayoutMethod::where('code', 'paypal')->first();
		$info = json_decode($payout->withdraw_information);

		if ($method->environment == 'live') {
			$api = 'https://api-m.paypal.com/v1/';
		} else {
			$api = 'https://api-m.sandbox.paypal.com/v1/';
		}

		$CLIENT_ID = optional($method->parameters)->cleint_id;
		$KEY_SECRET = optional($method->parameters)->secret;

		$url = $api . 'payments/payouts';
		$recipient_type = $info->recipient_type->fieldValue;
		$value = $info->amount->fieldValue;
		$receiver = $info->receiver->fieldValue;

		$headers = [
			'Content-Type: application/json',
			'Authorization: Basic ' . base64_encode("{$CLIENT_ID}:{$KEY_SECRET}")
		];

		$postParam = [
			"sender_batch_header" => [
				"sender_batch_id" => substr(md5(mt_rand()), 0, 10),
				"email_subject" => "You have a payout!",
				"email_message" => "You have received a payout! Thanks for using our service!",
			],
			"items" => [
				[
					"recipient_type" => $recipient_type,
					"amount" => [
						"value" => $value,
						"currency" => $payout->currency_code
					],
					"receiver" => $receiver,
				]
			]
		];

		$response = BasicCurl::payoutCurlPostRequestWithHeaders($url, $headers, $postParam);
		$result = json_decode($response);

		if (isset($result->batch_header)) {
			return [
				'status' => 'success',
				'response_id' => $result->batch_header->payout_batch_id
			];
		} else {
			return [
				'status' => 'error',
				'data' => $result->details[0]->issue
			];
		}
	}
}
