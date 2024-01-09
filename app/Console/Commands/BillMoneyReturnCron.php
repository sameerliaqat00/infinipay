<?php

namespace App\Console\Commands;

use App\Models\BillPay;
use App\Models\Transaction;
use App\Traits\Notify;
use Illuminate\Console\Command;

class BillMoneyReturnCron extends Command
{
	use Notify;

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'bill:return';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description';

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
		$billPays = BillPay::with(['baseCurrency'])->where('status', 3)->get();
		if ($billPays) {
			foreach ($billPays as $bill) {
				updateWallet($bill->user_id, $bill->base_currency_id, $bill->pay_amount_in_base, 1);
				$bill->status = 4;

				$transaction = new Transaction();
				$transaction->amount = $billPays->pay_amount_in_base;
				$transaction->charge = 0;
				$transaction->currency_id = $billPays->base_currency_id;
				$bill->transactional()->save($transaction);
				$bill->save();

				$params = [
					'amount' => getAmount($bill->payable_amount, 2),
					'currency' => $bill->currency,
					'return_currency_amount' => getAmount($bill->pay_amount_in_base, 2),
					'return_currency' => optional($bill->baseCurrency)->code ?? config('basic.base_currency_code'),
					'transaction' => $bill->utr,
				];
				$action = [
					"link" => "",
					"icon" => "fa fa-money-bill-alt text-white"
				];

				$this->sendMailSms($bill->user, 'BILL_PAYMENT_RETURN', $params);
				$this->userPushNotification($bill->user, 'BILL_PAYMENT_RETURN', $params, $action);
				$this->userFirebasePushNotification($bill->user, 'BILL_PAYMENT_RETURN', $params);
			}
		}
		return 0;
	}
}
