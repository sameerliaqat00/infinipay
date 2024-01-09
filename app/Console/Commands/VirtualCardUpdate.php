<?php

namespace App\Console\Commands;

use App\Models\VirtualCardOrder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class VirtualCardUpdate extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'virtual:cron';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'A command to check virtual card status.';

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
		$now = Carbon::now();
		$cards = VirtualCardOrder::with(['cardMethod'])->whereDate('expiry_date', '>=', $now)->get();
		foreach ($cards as $card) {
			$methodObj = 'App\\Services\\VirtualCard\\' . optional($card->cardMethod)->code . '\\Card';
			$data = $methodObj::cardRequest($card, 'statusUpdate');

			if ($data) {
				if ($data['status'] == 'success') {
					$cardStatus = $data['card_status'];
					if ($cardStatus == 'inactive') {
						$card->status = 9;
						$card->save();
					}
				}
			}

		}
		return 0;
	}
}
