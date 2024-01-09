<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExchangesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('exchanges', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->nullable()->constrained('users');
			$table->foreignId('from_wallet')->nullable()->comment('Wallet table id')->constrained('wallets');
			$table->foreignId('to_wallet')->nullable()->comment('Wallet table id')->constrained('wallets');
			$table->decimal('percentage', 16, 8)->default(0)->comment('Percent of charge');
			$table->decimal('charge_percentage', 16, 8)->default(0)->comment('After adding percent of charge');
			$table->decimal('charge_fixed', 16, 8)->default(0);
			$table->decimal('charge', 16, 8)->default(0)->comment('After adding percent & fixed charge');
			$table->decimal('exchange_rate', 16, 8)->default(0);
			$table->decimal('amount', 16, 8)->default(0);
			$table->decimal('transfer_amount', 16, 8)->default(0)->comment('Amount deduct from exchange currency wallet');
			$table->decimal('received_amount', 16, 8)->default(0)->comment('Amount add to exchange currency wallet');
			$table->tinyInteger('status')->default(0)->comment('1=Success,0=Pending');
			$table->uuid('utr')->nullable();
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
		Schema::dropIfExists('exchanges');
	}
}
