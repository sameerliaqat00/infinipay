<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiOrdersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('api_orders', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->index()->nullable();
			$table->foreignId('currency_id')->index()->nullable();
			$table->foreignId('gateway_id')->index()->nullable();

			$table->string('utr')->nullable();
			$table->string('trx')->nullable(); //payment id
			$table->double('amount')->nullable();
			$table->double('charge')->nullable();
			$table->double('rate')->nullable();
			$table->text('meta');
			$table->timestamp('paid_at', 0)->nullable();
			$table->tinyInteger('status')->default(0);
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
		Schema::dropIfExists('api_orders');
	}
}
