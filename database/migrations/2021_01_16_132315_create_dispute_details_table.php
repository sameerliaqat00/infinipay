<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDisputeDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('dispute_details', function (Blueprint $table) {
			$table->id();
			$table->foreignId('dispute_id')->nullable()->constrained('disputes');
			$table->foreignId('user_id')->nullable()->constrained('users');
			$table->foreignId('admin_id')->nullable()->constrained('admins');
			$table->tinyInteger('status')->nullable()->comment('0 = user replied ,1 = admin replied');
			$table->text('message')->nullable();
			$table->text('files')->nullable();
			$table->tinyInteger('action')->nullable()->comment('0 = solved,1 = closed,2 = mute,3 = unmute');
			$table->uuid('utr')->nullable();
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
		Schema::dropIfExists('dispute_details');
	}
}
