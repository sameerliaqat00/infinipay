<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillMethodsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bill_methods', function (Blueprint $table) {
			$table->id();
			$table->string('methodName')->nullable();
			$table->string('code')->nullable();
			$table->string('description')->nullable();
			$table->text('parameters')->nullable()->comment('api parameters');
			$table->text('extra_parameters')->nullable();
			$table->text('inputForm')->nullable();
			$table->decimal('percentage_charge', 18, 8)->default(0);
			$table->decimal('fixed_charge', 18, 8)->default(0);
			$table->decimal('min_limit', 18, 8)->default(0);
			$table->decimal('max_limit', 18, 8)->default(0);
			$table->tinyInteger('is_active')->default(1)->comment('1 = active, 0 = inactive');
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
		Schema::dropIfExists('bill_methods');
	}
}
