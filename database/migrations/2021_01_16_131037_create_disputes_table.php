<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDisputesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('disputes', function (Blueprint $table) {
			$table->id();
			$table->bigInteger('disputable_id')->nullable();
			$table->string('disputable_type')->nullable();
			$table->uuid('utr')->nullable();
			$table->tinyInteger('status')->default(0)->comment('0 = open,1 = solved,2 = closed');
			$table->tinyInteger('defender_reply_yn')->nullable()->comment('0 = No, 1 = Yes');
			$table->tinyInteger('claimer_reply_yn')->nullable()->comment('0 = No, 1 = Yes');
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
		Schema::dropIfExists('disputes');
	}
}
