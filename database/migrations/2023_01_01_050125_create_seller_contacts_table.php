<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellerContactsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('seller_contacts', function (Blueprint $table) {
			$table->id();
			$table->foreignId('store_id')->index()->nullable();
			$table->foreignId('user_id')->index()->nullable();
			$table->string('sender_name')->nullable();
			$table->string('message')->nullable();
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
		Schema::dropIfExists('seller_contacts');
	}
}
