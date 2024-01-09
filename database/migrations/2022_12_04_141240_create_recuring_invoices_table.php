<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecuringInvoicesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('recuring_invoices', function (Blueprint $table) {
			$table->id();
			$table->integer('number_of_payments')->nullable();
			$table->integer('current_number_of_payment')->nullable();
			$table->timestamp('first_arrival_date')->nullable();
			$table->timestamp('last_arrival_date')->nullable();
			$table->decimal('subtotal', 8, 2)->nullable();
			$table->decimal('tax', 8, 2)->nullable();
			$table->decimal('vat', 8, 2)->nullable();
			$table->decimal('tax_rate', 8, 2)->nullable();
			$table->decimal('vat_rate', 8, 2)->nullable();
			$table->decimal('grand_total', 8, 2)->nullable();
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
		Schema::dropIfExists('recuring_invoices');
	}
}
