<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestMoneyTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('request_money', function (Blueprint $table) {
			$table->id();
			$table->foreignId('sender_id')->nullable()->comment('user id of request sender')->constrained('users');
			$table->foreignId('receiver_id')->nullable()->comment('user id of received receiver')->constrained('users');
			$table->foreignId('currency_id')->nullable()->constrained('currencies');
			$table->uuid('utr')->nullable()->comment('unique id for each payment request');
			$table->decimal('amount', 16, 8)->default(0)->comment('requested amount');
			$table->string('email')->nullable()->comment('request receiver id');
			$table->text('note')->nullable();
			$table->tinyInteger('status')->default(0)->comment('1=Success,0=Pending,2=Cancel');
			$table->decimal('percentage', 16, 8)->default(0)->comment('percent of charge');
			$table->decimal('charge_percentage', 16, 8)->default(0)->comment('after adding percent of charge');
			$table->decimal('charge_fixed', 16, 8)->default(0);
			$table->decimal('charge', 16, 8)->default(0);
			$table->decimal('transfer_amount', 16, 8)->default(0)->comment('amount deduct from user who (received request)');
			$table->decimal('received_amount', 16, 8)->default(0)->comment('amount add to user who send (request money)');
			$table->tinyInteger('charge_from')->default(0)->comment('0 = Sender, 1 = Receiver');
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
		Schema::dropIfExists('request_money');
	}
}
