<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferralBonusesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('referral_bonuses', function (Blueprint $table) {
			$table->id();
			$table->string('referral_on', 50)->nullable();
			$table->integer('level')->nullable();
			$table->decimal('amount', 18, 8)->default(0);
			$table->decimal('minAmount', 18, 8)->default(0);
			$table->boolean('calcType')->default(1)->comment('1 = fixed, 0 = percent');
			$table->boolean('status')->default(0)->comment('1 = active, 0 = inactive');
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
		Schema::dropIfExists('referral_bonuses');
	}
}
