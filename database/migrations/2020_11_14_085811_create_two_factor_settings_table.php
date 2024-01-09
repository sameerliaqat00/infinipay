<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTwoFactorSettingsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('two_factor_settings', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained('users');
			$table->foreignId('security_question_id')->nullable()->constrained('security_questions');
			$table->string('answer')->nullable()->comment('Security question answer');
			$table->string('hints')->nullable()->comment('Security question answer hints');
			$table->string('security_pin')->nullable();
			$table->string('enable_for')->nullable();
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
		Schema::dropIfExists('two_factor_settings');
	}
}
