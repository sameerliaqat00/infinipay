<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVirtualCardOrdersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('virtual_card_orders', function (Blueprint $table) {
			$table->id();
			$table->foreignId('virtual_card_method_id')->index()->nullable();
			$table->foreignId('user_id')->index()->nullable();
			$table->string('currency')->nullable();
			$table->text('form_input')->nullable();
			$table->tinyInteger('status')->default(0)->comment("0=>pending,1=>approve,2=>rejected,3=>hold");
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
		Schema::dropIfExists('virtual_card_orders');
	}
}
