<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEscrowsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('escrows', function (Blueprint $table) {
			$table->id();
			$table->foreignId('sender_id')->nullable()->constrained('users');
			$table->foreignId('receiver_id')->nullable()->constrained('users');
			$table->foreignId('currency_id')->nullable()->constrained('currencies');
			$table->decimal('percentage', 16, 8)->default(0)->comment('Percent of charge');
			$table->decimal('charge_percentage', 16, 8)->default(0)->comment('After adding percent of charge');
			$table->decimal('charge_fixed', 16, 8)->default(0);
			$table->decimal('charge', 16, 8)->default(0);
			$table->decimal('amount', 16, 8)->default(0);
			$table->decimal('transfer_amount', 16, 8)->default(0)->comment('Amount deduct from sender');
			$table->decimal('received_amount', 16, 8)->default(0)->comment('Amount add to receiver');
			$table->tinyInteger('charge_from')->default(0)->comment('0 = Sender, 1 = Receiver');
			$table->text('note')->nullable();
			$table->string('email')->nullable();
			$table->tinyInteger('status')->default(0)
				->comment('0=Pending, 1=generated, 2 = payment done, 3 = sender request to payment disburse, 4 = payment disbursed,5 = cancel, 6= dispute');
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
		Schema::dropIfExists('escrows');
	}
}
