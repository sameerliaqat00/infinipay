<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('wallets', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->nullable()->constrained('users');
			$table->foreignId('currency_id')->nullable()->constrained('currencies');
			$table->decimal('balance', 16, 8)->default(0);
			$table->tinyInteger('is_active')->default(1)->comment('1 = active, 0 = inactive');
			$table->tinyInteger('is_default')->default(0)->comment('1 = default, 0 = not default');
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
		Schema::dropIfExists('wallets');
	}
}
