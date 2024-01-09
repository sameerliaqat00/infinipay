<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreShippingsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('store_shippings', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->index()->nullable();
			$table->foreignId('currency_id')->index()->nullable();
			$table->string('address')->nullable();
			$table->decimal('cost', 8, 8)->nullable();
			$table->boolean('status')->default(1);
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
		Schema::dropIfExists('store_shippings');
	}
}
