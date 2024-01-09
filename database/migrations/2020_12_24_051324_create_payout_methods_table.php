<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayoutMethodsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('payout_methods', function (Blueprint $table) {
			$table->id();
			$table->string('methodName')->nullable();
			$table->string('code')->nullable();
			$table->string('description')->nullable();
			$table->text('bank_name')->nullable()->comment('automatic payment for bank name');
			$table->text('banks')->nullable()->comment('admin bank permission');
			$table->text('parameters')->nullable()->comment('api parameters');
			$table->text('extra_parameters')->nullable();
			$table->text('inputForm')->nullable();
			$table->decimal('percentage_charge', 18, 8)->default(0);
			$table->decimal('fixed_charge', 18, 8)->default(0);
			$table->decimal('min_limit', 18, 8)->default(0);
			$table->decimal('max_limit', 18, 8)->default(0);
			$table->text('supported_currency')->nullable();
			$table->text('convert_rate')->nullable();
			$table->tinyInteger('is_active')->default(1)->comment('1 = active, 0 = inactive');
			$table->tinyInteger('is_automatic')->default(0);
			$table->string('logo')->nullable();
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
		Schema::dropIfExists('payout_methods');
	}
}
