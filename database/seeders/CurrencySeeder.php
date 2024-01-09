<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		DB::table('currencies')->insert([
			'name'=>'US Doller',
			'symbol'=>'$',
			'code'=>'USD',
			'logo'=>'usd.jpeg',
			'exchange_rate'=>'0.01000000',
			'currency_type'=>1,
			'is_active'=>1,
		]);
    }
}
