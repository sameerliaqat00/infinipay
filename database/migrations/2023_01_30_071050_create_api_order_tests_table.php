<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiOrderTestsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('api_order_tests', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->index()->nullable();
			$table->foreignId('currency_id')->index()->nullable();
			$table->foreignId('gateway_id')->index()->nullable();

			$table->integer('order_id')->nullable();
			$table->string('utr')->nullable();
			$table->double('percentage')->default(0.00);
			$table->double('charge_percentage')->default(0.00);
			$table->double('charge_fixed')->default(0.00);
			$table->double('amount')->nullable();
			$table->double('charge')->nullable();
			$table->double('rate')->nullable();
			$table->text('meta');
			$table->timestamp('paid_at', 0)->nullable();
			$table->tinyInteger('status')->default(0);
			$table->mediumText('ipn_url')->nullable();
			$table->mediumText('redirect_url')->nullable();
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
		Schema::dropIfExists('api_order_tests');
	}
}
