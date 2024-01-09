<?php

namespace App\Console\Commands;

use App\Models\Currency;
use Facades\App\Services\BasicCurl;
use Illuminate\Console\Command;

class FiatCurrencyUpdate extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'fiat-currency:update';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Fiat currency exchange rate update from api';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle()
	{
		$currency_layer_auto_update = basicControl()->currency_layer_auto_update;


		if ($currency_layer_auto_update == 1) {
			$endpoint = 'live';
			$currency_layer_access_key = basicControl()->currency_layer_access_key;

			$currency_layer_url = "https://api.apilayer.com/currency_data";
			$baseCurrency = basicControl()->base_currency_code;
			$source = 'USD';
			$currencies = implode(',', Currency::pluck('code')->toArray());


			$baseCurrencyAPIUrl = "$currency_layer_url/$endpoint?source=$source&currencies=$baseCurrency";
			$allCurrencyAPIUrl = "$currency_layer_url/$endpoint?source=$source&currencies=$currencies";


			$headers = array(
				"Content-Type: application/json",
				"apikey: $currency_layer_access_key"
			);

			$baseCurrencyConvert = BasicCurl::curlGetRequestWithHeaders($baseCurrencyAPIUrl, $headers);
			$allCurrencyConvert = BasicCurl::curlGetRequestWithHeaders($allCurrencyAPIUrl, $headers);


			$baseCurrencyConvert = json_decode($baseCurrencyConvert);
			$allCurrencyConvert = json_decode($allCurrencyConvert);



			if ($baseCurrencyConvert->success && $allCurrencyConvert->success) {
				if (empty($baseCurrencyConvert->quotes)) {
					$usdToBase = 1.00;
				} else {
					$usdToBase = (array)$baseCurrencyConvert->quotes;
					$usdToBase = $usdToBase["$source$baseCurrency"];
				}

				foreach ($allCurrencyConvert->quotes as $key => $rate) {
					$curCode = substr($key, -3);
					$curRate = round($rate / $usdToBase, 2);
					Currency::where(['code' => $curCode, 'currency_type' => 1])->update(['exchange_rate' => $curRate]);
				}
			}
		}
	}
}
