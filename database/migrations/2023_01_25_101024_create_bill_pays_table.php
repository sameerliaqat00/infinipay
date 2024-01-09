<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillPaysTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bill_pays', function (Blueprint $table) {
			$table->id();
			$table->foreignId('method_id')->index();
			$table->foreignId('user_id')->index();
			$table->foreignId('service_id')->index();
			$table->string('category_name')->nullable();
			$table->string('country_name')->nullable();
			$table->double('amount')->default(0.00);
			$table->double('charge')->default(0.00);
			$table->tinyInteger('status')->comment('0=>generate,1=>pending,2=>completed');
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
		Schema::dropIfExists('bill_pays');
	}
}
