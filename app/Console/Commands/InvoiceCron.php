<?php

namespace App\Console\Commands;

use App\Mail\SendInvoiceMail;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class InvoiceCron extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'invoice:cron';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'A command to send recurring invoice';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle()
	{
		$invoices = Invoice::with('recuring_invoice', 'items')->whereNotNull('customer_email')
			->whereIn('frequency', ['2', '3'])
			->whereHas('recuring_invoice', function ($qq) {
				$qq->whereDate('first_arrival_date', '<=', today())
					->whereDate('last_arrival_date', '>=', today());
			})
			->get();

		foreach ($invoices as $invoice) {
			$recuringInvoice = $invoice->recuring_invoice;

			if ($recuringInvoice->current_number_of_payment < $recuringInvoice->number_of_payments) {

				if (isset($invoice->customer_email)) {
					Mail::to($invoice->customer_email)->queue(new SendInvoiceMail((object)$invoice));
				}
				if ($invoice->frequency == '3') {
					$recuringInvoice->first_arrival_date = Carbon::parse($recuringInvoice->first_arrival_date)->addMonth();
				} elseif ($invoice->frequency == '2') {
					$recuringInvoice->first_arrival_date = Carbon::parse($recuringInvoice->first_arrival_date)->addWeek();
				}

				++$recuringInvoice->current_number_of_payment;

				if ($recuringInvoice->current_number_of_payement != null) {
					$newInvoice = new Invoice();
					$newInvoice->sender_id = $invoice->sender_id;
					$newInvoice->currency_id = $invoice->currency_id;
					$newInvoice->recuring_invoice_id = $invoice->recuring_invoice_id;
					$newInvoice->customer_email = $invoice->customer_email;
					$newInvoice->invoice_number = $invoice->invoice_number;
					$newInvoice->subtotal = $invoice->subtotal;
					$newInvoice->tax = $invoice->tax;
					$newInvoice->vat = $invoice->vat;
					$newInvoice->tax_rate = $invoice->tax_rate;
					$newInvoice->vat_rate = $invoice->vat_rate;
					$newInvoice->grand_total = $invoice->grand_total;
					$newInvoice->frequency = $invoice->frequency;
					$newInvoice->due_date = Carbon::today();
					$newInvoice->note = $invoice->note;
					$newInvoice->save();
					$data = $newInvoice->id . '|' . $newInvoice->customer_email;
					$invoiceCron = new InvoiceCron();
					$newInvoice->has_slug = $invoiceCron->encrypt($data);
					$newInvoice->save();
				}
				$recuringInvoice->save();
			}
		}
		return 'send';
	}

	public function encrypt($data)
	{
		return implode(unpack("H*", $data));
	}
}
