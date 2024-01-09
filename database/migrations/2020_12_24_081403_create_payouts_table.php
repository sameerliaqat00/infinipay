<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayoutsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('payouts', function (Blueprint $table) {
			$table->id();
			$table->foreignId('admin_id')->nullable()->constrained('admins');
			$table->foreignId('user_id')->nullable()->constrained('users');
			$table->foreignId('currency_id')->nullable()->constrained('currencies');
			$table->foreignId('payout_method_id')->nullable()->constrained('payout_methods');
			$table->decimal('percentage', 16, 8)->default(0)->comment('Percent of charge');
			$table->decimal('charge_percentage', 16, 8)->default(0)->comment('After adding percent of charge');
			$table->decimal('charge_fixed', 16, 8)->default(0);
			$table->decimal('charge', 16, 8)->default(0);
			$table->decimal('amount', 16, 8)->default(0);
			$table->decimal('transfer_amount', 16, 8)->default(0)->comment('Amount deduct from sender');
			$table->decimal('received_amount', 16, 8)->default(0)->comment('Amount add to receiver');
			$table->tinyInteger('charge_from')->default(0)->comment('0 = Sender, 1 = Receiver');
			$table->text('note')->nullable();
			$table->text('withdraw_information')->nullable();
			$table->string('email')->nullable();
			$table->tinyInteger('status')->default(0)->comment('0=Pending, 1=generate, 2 = payment done, 5 = cancel');
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
		Schema::dropIfExists('payouts');
	}
}
