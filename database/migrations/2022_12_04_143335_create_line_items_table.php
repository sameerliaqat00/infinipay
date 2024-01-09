<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLineItemsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('line_items', function (Blueprint $table) {
			$table->id();
			$table->morphs('line_item');
			$table->string('title')->nullable();
			$table->decimal('price', 8, 2)->default(0.00);
			$table->longText('description')->nullable();
			$table->integer('quantity')->nullable();
			$table->decimal('subtotal', 8, 2)->default(0.00);
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
		Schema::dropIfExists('line_items');
	}
}
