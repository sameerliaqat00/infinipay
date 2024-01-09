<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductOrdersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('product_orders', function (Blueprint $table) {
			$table->id();
			$table->text('order_number')->nullable();
			$table->foreignId('gateway_id')->index()->nullable();
			$table->string('fullname')->nullable();
			$table->string('email')->nullable();
			$table->string('phone')->nullable();
			$table->string('alt_phone')->nullable();
			$table->foreignId('shipping_id')->index()->nullable();
			$table->string('detailed_address')->nullable();
			$table->string('order_note')->nullable();
			$table->tinyInteger('status')->default(0)->comment('0=>pending,1=>complete');
			$table->tinyInteger('stage')->nullable();
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
		Schema::dropIfExists('product_orders');
	}
}
