<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVirtualCardTransactionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('virtual_card_transactions', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->index()->nullable();
			$table->foreignId('card_order_id')->index()->nullable();
			$table->text('data')->nullable();
			$table->double('amount')->default(0.00);
			$table->string('currency')->nullable();
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
		Schema::dropIfExists('virtual_card_transactions');
	}
}
