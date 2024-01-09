<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoresTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('stores', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->index()->nullable();
			$table->string('name')->nullable();
			$table->text('image')->nullable();
			$table->boolean('shipping_status')->default(1);
			$table->enum('product_type', ['physical', 'virtual'])->nullable();
			$table->boolean('status')->default(1)->comment('0=>inactive , 1=>active');
			$table->text('link')->nullable();
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
		Schema::dropIfExists('stores');
	}
}
