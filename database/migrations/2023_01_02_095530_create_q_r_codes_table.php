<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQRCodesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('q_r_codes', function (Blueprint $table) {
			$table->id();
			$table->foreignId('gateway_id')->index()->nullable();
			$table->foreignId('currency_id')->index()->nullable();
			$table->string('email');
			$table->double('charge')->default(0.00);
			$table->double('amount')->default(0.00);
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('q_r_codes');
	}
}
