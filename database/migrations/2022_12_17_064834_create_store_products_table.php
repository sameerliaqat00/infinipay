<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreProductsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('store_products', function (Blueprint $table) {
			$table->id();
			$table->foreignId('category_id')->index()->nullable();
			$table->foreignId('currency_id')->index()->nullable();
			$table->string('name')->nullable();
			$table->double('price')->default(0.00);
			$table->string('sku')->nullable();
			$table->boolean('status')->default(1)->comment('0=>inactive , 1=>active');
			$table->longText('description')->nullable();
			$table->longText('instruction')->nullable();
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
		Schema::dropIfExists('store_products');
	}
}
