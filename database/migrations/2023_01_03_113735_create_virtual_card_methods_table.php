<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVirtualCardMethodsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('virtual_card_methods', function (Blueprint $table) {
			$table->id();
			$table->string('code')->unique();
			$table->string('name')->nullable();
			$table->text('image')->nullable();
			$table->boolean('status')->default(0);
			$table->text('parameters')->nullable();
			$table->text('currencies')->nullable();
			$table->text('extra_parameters')->nullable();
			$table->text('currency')->nullable();
			$table->text('symbol')->nullable();
			$table->double('min_amount')->default(0.00);
			$table->double('max_amount')->default(0.00);
			$table->double('percentage_charge')->default(0.00);
			$table->double('fixed_charge')->default(0.00);
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
		Schema::dropIfExists('virtual_card_methods');
	}
}
