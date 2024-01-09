<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('invoices', function (Blueprint $table) {
			$table->id();
			$table->foreignId('sender_id')->index()->nullable();
			$table->foreignId('recuring_invoice_id')->index()->nullable();
			$table->string('customer_email')->nullable();
			$table->string('invoice_number')->nullable();
			$table->double('subtotal')->nullable();
			$table->double('tax')->nullable();
			$table->double('vat')->nullable();
			$table->double('tax_rate')->nullable();
			$table->double('vat_rate')->nullable();
			$table->decimal('grand_total', 8, 2)->nullable();
			$table->string('frequency')->nullable()->comment('1=>one Time, 2=>weekly, 3=>monthly');
			$table->string('has_slug')->nullable()->comment();
			$table->timestamp('due_date')->nullable();
			$table->longText('note')->nullable();
			$table->string('status')->nullable()->comment('paid , pending , rejected');
			$table->timestamp('seen_at')->nullable();
			$table->timestamp('paid_at')->nullable();
			$table->timestamp('rejected_at')->nullable();
			$table->timestamp('mark_as_paid_at')->nullable();
			$table->timestamp('reminder_at')->nullable();
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
		Schema::dropIfExists('invoices');
	}
}
