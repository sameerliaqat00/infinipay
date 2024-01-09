<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFundsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('funds', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->nullable()->constrained('users');
			$table->foreignId('admin_id')->nullable()->comment('Deposit from admin')->constrained('admins');
			$table->foreignId('currency_id')->nullable()->constrained('currencies');
			$table->decimal('percentage', 18, 8)->default(0)->comment('Percent of charge');
			$table->decimal('charge_percentage', 18, 8)->default(0)->comment('After adding percent of charge');
			$table->decimal('charge_fixed', 18, 8)->default(0);
			$table->decimal('charge', 18, 8)->default(0);
			$table->decimal('amount', 18, 8)->default(0);
			$table->decimal('received_amount', 18, 8)->default(0)->comment('Amount add to receiver');
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
		Schema::dropIfExists('funds');
	}
}
