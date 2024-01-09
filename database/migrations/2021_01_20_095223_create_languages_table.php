<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class CreateLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
			$table->string('name', 100)->nullable();
			$table->string('short_name', 10)->nullable();
			$table->string('flag', 100)->nullable();
			$table->boolean('is_active')->default(true)->comment('1 = active, 0 = inactive');
			$table->boolean('rtl')->default(false)->comment('1 = active, 0 = inactive');
            $table->boolean('default_status')->default(false);
			$table->timestamps();
        });

		Artisan::call('db:seed', [
			'--class' => 'LanguageSeeder',
			'--force' => true
		]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('languages');
    }
}
