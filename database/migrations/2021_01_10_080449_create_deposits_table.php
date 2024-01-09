<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepositsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('deposits', function (Blueprint $table) {
			$table->id();
			$table->integer('depositable_id')->nullable();
			$table->string('depositable_type')->nullable();
			$table->foreignId('user_id')->nullable()->constrained('users');
//			$table->foreignId('admin_id')->nullable()->comment('Deposit from admin')->constrained('admins');
			$table->foreignId('currency_id')->nullable()->constrained('currencies');
			$table->foreignId('charges_limit_id')->nullable()->constrained('charges_limits');
			$table->foreignId('payment_method_id')->nullable()->constrained('gateways');
			$table->string('payment_method_currency')->nullable();
			$table->decimal('amount', 18, 8)->default(0);
			$table->decimal('percentage', 18, 8)->default(0)->comment('Percent of charge');
			$table->decimal('charge_percentage', 18, 8)->default(0)->comment('After adding percent of charge');
			$table->decimal('charge_fixed', 18, 8)->default(0);
			$table->decimal('charge', 18, 8)->default(0)->comment('Total charge');
			$table->decimal('payable_amount', 18, 8)->default(0)->comment('Amount payed');
			$table->decimal('btc_amount', 18, 8)->nullable();
			$table->string('btc_wallet')->nullable();
			$table->uuid('utr')->nullable();
			$table->tinyInteger('status')->default(0)->comment('1=Success,0=Pending');
			$table->text('note')->nullable();
			$table->string('email')->nullable();
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
		Schema::dropIfExists('deposits');
	}
}
