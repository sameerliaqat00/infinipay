<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductOrderDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('product_order_details', function (Blueprint $table) {
			$table->id();
			$table->foreignId('order_id')->index()->nullable();
			$table->foreignId('product_id')->index()->nullable();
			$table->foreignId('attributes_id')->index()->nullable();
			$table->integer('quantity')->nullable();
			$table->double('price')->nullable();
			$table->double('total_price')->nullable();
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
		Schema::dropIfExists('product_order_details');
	}
}
