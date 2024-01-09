<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransfersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('transfers', function (Blueprint $table) {
			$table->id();
			$table->foreignId('sender_id')->nullable()->constrained('users');
			$table->foreignId('receiver_id')->nullable()->constrained('users');
			$table->foreignId('currency_id')->nullable()->constrained('currencies');
			$table->decimal('percentage', 18, 8)->default(0)->comment('Percent of charge');
			$table->decimal('charge_percentage',  18, 8)->default(0)->comment('After adding percent of charge');
			$table->decimal('charge_fixed',  18, 8)->default(0);
			$table->decimal('charge',  18, 8)->default(0);
			$table->decimal('amount',  18, 8)->default(0);
			$table->decimal('transfer_amount',  18, 8)->default(0)->comment('Amount deduct from sender');
			$table->decimal('received_amount',  18, 8)->default(0)->comment('Amount add to receiver');
			$table->tinyInteger('charge_from')->default(0)->comment('0 = Sender, 1 = Receiver');
			$table->text('note')->nullable();
			$table->string('email')->nullable();
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
		Schema::dropIfExists('transfers');
	}
}
