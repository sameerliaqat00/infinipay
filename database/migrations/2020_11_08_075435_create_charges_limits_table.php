<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChargesLimitsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('charges_limits', function (Blueprint $table) {
			$table->id();
			$table->foreignId('currency_id')->nullable()->constrained('currencies');
			$table->foreignId('transaction_type_id')->nullable();
			$table->foreignId('payment_method_id')->nullable();//bank/gateway ID
			$table->decimal('percentage_charge',  18, 8)->default(0);
			$table->decimal('fixed_charge',  18, 8)->default(0);
			$table->decimal('min_limit',  18, 8)->default(1);
			$table->decimal('max_limit',  18, 8)->default(500);
			$table->decimal('convention_rate',  18, 8)->default(1);
			$table->tinyInteger('is_active')->default(1)->comment('1 = active, 0 = inactive');
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
		Schema::dropIfExists('charges_limits');
	}
}
