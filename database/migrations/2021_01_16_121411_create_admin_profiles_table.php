<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminProfilesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('admin_profiles', function (Blueprint $table) {
			$table->id();
			$table->foreignId('admin_id')->nullable()->constrained('admins');
			$table->string('city', 32)->nullable();
			$table->string('state', 32)->nullable();
			$table->string('phone', 32)->nullable();
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
		Schema::dropIfExists('admin_profiles');
	}
}
