<?php

namespace App\Console\Commands;

use App\Models\Currency;
use Facades\App\Services\BasicCurl;
use Illuminate\Console\Command;

class CryptoCurrencyUpdate extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'crypto-currency:update';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'crypto currency exchange rate update from api';

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
		$basicControl = basicControl();
		$coin_market_cap_auto_update = $basicControl->coin_market_cap_auto_update;
		$endpoint = 'live';
		$source = 'USD';
		$currency_layer_access_key = $basicControl->currency_layer_access_key;
		$currency_layer_url = "http://api.currencylayer.com";

		if ($coin_market_cap_auto_update == 1) {
			$coin_market_cap_app_key = $basicControl->coin_market_cap_app_key;
			$baseCurrency = $basicControl->base_currency_code;
			$baseCurrencyAPIUrl = "$currency_layer_url/$endpoint?access_key=$currency_layer_access_key&source=$source&currencies=$baseCurrency";
			$baseCurrencyConvert = BasicCurl::curlGetRequest($baseCurrencyAPIUrl);
			$baseCurrencyConvert = json_decode($baseCurrencyConvert);

			if (empty($baseCurrencyConvert->quotes)) {
				$usdToBase = 1.00;
			} else {
				$usdToBase = $baseCurrencyConvert->quotes->{$source . $baseCurrency};
			}

			$symbol = implode(',', Currency::where('currency_type', 0)->pluck('code')->toArray());
			$url = "https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest?symbol=$symbol";
//			$url = "https://sandbox-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest?symbol=$symbol";

			$headers = [
				'Accepts: application/json',
				'X-CMC_PRO_API_KEY:' . $coin_market_cap_app_key
			];
			$allCryptoConvert = BasicCurl::curlGetRequestWithHeaders($url, $headers);
			$allCryptoConvert = json_decode($allCryptoConvert, true);

			if (@$allCryptoConvert['data'] == '') {
				return 'error';
			}
			$coins = $allCryptoConvert['data'];
			foreach ($coins as $coin) {
				$symbol = $coin['symbol'];
				$curRate = $usdToBase / $coin['quote']['USD']['price'];
				Currency::where(['code' => $symbol, 'currency_type' => 0])->update(['exchange_rate' => $curRate]);
			}

		}
	}
}
