<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserProfilesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_profiles', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->nullable()->constrained('users');
			$table->string('city', 32)->nullable();
			$table->string('state', 32)->nullable();
			$table->string('phone', 32)->nullable();
			$table->string('phone_code', 8)->nullable();
			$table->text('address')->nullable();
			$table->string('profile_picture')->nullable();
			$table->timestamp('last_login_at')->nullable();
			$table->string('last_login_ip', 16)->nullable();
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
		Schema::dropIfExists('user_profiles');
	}
}
