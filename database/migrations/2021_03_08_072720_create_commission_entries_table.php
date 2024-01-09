<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommissionEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commission_entries', function (Blueprint $table) {
            $table->id();
			$table->foreignId('to_user')->nullable()->constrained('users');
			$table->foreignId('from_user')->nullable()->constrained('users');
			$table->foreignId('currency_id')->nullable()->constrained('currencies');
			$table->integer('level')->nullable();
			$table->decimal('commission_amount', 18, 8)->default(0);
			$table->string('title')->nullable();
			$table->string('type')->nullable();
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
        Schema::dropIfExists('commission_entries');
    }
}
