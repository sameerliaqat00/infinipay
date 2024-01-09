<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvoiceStoreRequest;
use App\Mail\ReminderMail;
use App\Mail\SendInvoiceMail;
use App\Models\BasicControl;
use App\Models\ChargesLimit;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\Gateway;
use App\Models\Invoice;
use App\Models\RecuringInvoice;
use App\Models\Template;
use App\Models\Voucher;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PDF;
use Illuminate\Support\Facades\App;
use Stevebauman\Purify\Facades\Purify;
use App\Http\Controllers\VoucherController;

class InvoiceController extends Controller
{
	public function index()
	{
		$userId = Auth::id();
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$invoices = Invoice::with(['sender', 'currency'])
			->where(function ($query) use ($userId) {
				$query->where('sender_id', $userId);
			})
			->latest()
			->paginate();
		return view('user.invoice.index', compact('currencies', 'invoices'));
	}

	public function search(Request $request)
	{
		$filterData = $this->_filter($request);
		$search = $filterData['search'];
		$currencies = $filterData['currencies'];
		$userId = $filterData['userId'];

		$invoices = $filterData['invoices']
			->where(function ($query) use ($userId) {
				$query->where('sender_id', $userId);
			})
			->latest()
			->paginate();
		$invoices->appends($filterData['search']);
		return view('user.invoice.index', compact('search', 'invoices', 'currencies'));
	}

	public function create()
	{
		$data['basicControl'] = basicControl();
		$data['template'] = Template::where('section_name', 'invoice-payment')->first();
		$data['currencies'] = Currency::select('id', 'code', 'name', 'symbol')->orderBy('code', 'ASC')->get();
		return view('user.invoice.create', $data);
	}

	public function store(InvoiceStoreRequest $request)
	{
		DB::beginTransaction();

		if ($request->payment == 2 || $request->payment == 3) {
			$recuringInvoice = new RecuringInvoice();

			if ($request->payment == 2) {
				$recuringInvoice->number_of_payments = $request->num_payment;
				$recuringInvoice->first_arrival_date = Carbon::createFromFormat('d/m/Y', $request->first_pay_date);
				$recuringInvoice->last_arrival_date = Carbon::createFromFormat('d/m/Y', $request->first_pay_date)->addWeeks(($request->num_payment - 1));

			} elseif ($request->payment == 3) {

				$recuringInvoice->number_of_payments = $request->num_payment;
				$recuringInvoice->first_arrival_date = Carbon::createFromFormat('d/m/Y', $request->first_pay_date);
				$recuringInvoice->last_arrival_date = Carbon::createFromFormat('d/m/Y', $request->first_pay_date)->addMonths(($request->num_payment - 1));
			}

			$recuringInvoice->subtotal = @$request->subtotal;
			$recuringInvoice->tax = $request->tax;
			$recuringInvoice->vat = $request->vat;
			$recuringInvoice->tax_rate = $request->taxRate;
			$recuringInvoice->vat_rate = $request->vatRate;
			$recuringInvoice->grand_total = $request->garndtotal;

			$recuringInvoice->save();
		}


		$invoice = new Invoice();
		$invoice->sender_id = auth()->id();
		if ($request->payment == 2 || $request->payment == 3) {
			$invoice->recuring_invoice_id = $recuringInvoice->id;
		}
		$invoice->customer_email = $request->customer_email;
		$invoice->invoice_number = $request->invoice_number;
		$invoice->subtotal = $request->subtotal;
		$invoice->tax = $request->tax;
		$invoice->vat = $request->vat;
		$invoice->tax_rate = $request->taxRate;
		$invoice->vat_rate = $request->vatRate;
		$invoice->grand_total = $request->garndtotal;
		$invoice->frequency = $request->payment;

		if (isset($request->due_date)) {
			$invoice->due_date = Carbon::createFromFormat('d/m/Y', $request->due_date);
		} else {
			$invoice->due_date = $recuringInvoice->first_arrival_date;
		}
		$basicControl = basicControl();
		$invoice->charge_pay = $basicControl->invoice_charge;
		$invoice->currency_id = $request->currency;
		$charge = $this->checkInitiateAmountValidate($request->currency, $request->garndtotal);

		if ($charge == 'false') {
			return 0;
		}
		$invoice->percentage = $charge['percentage_charge'];
		$invoice->charge_percentage = $charge['valueAfterPercent'];
		$invoice->charge_fixed = $charge['fixed_charge'];
		$invoice->charge = $charge['charge'];

		$invoice->save();

		if ($request->button_name == 'send') {
			$data = $invoice->id . '|' . $request->customer_email;
			$invoice = Invoice::findOrFail($invoice->id);
			$invoice->has_slug = $this->encrypt($data);
			$invoice->note = $request->note;
			$invoice->save();
		}
		if (count($request->items)) {
			foreach ($request->items as $key => $item) {
				$invoice->items()->create([
					'title' => $item['title'] ?? 'N/A',
					'price' => $item['price'] ?? 0.00,
					'description' => $item['description'] ?? null,
					'quantity' => $item['quantity'] ?? 0.00,
					'subtotal' => $item['quantity'] * $item['price'],
				]);
			}
		}

		DB::commit();

		session()->flash('success', 'Invoice send successfully');

		if ($request->button_name == 'send') {
			if ($request->payment != 2 && $request->payment != 3) {

				Mail::to($request->customer_email)
					->queue(new SendInvoiceMail((object)$invoice));
			}
		}

		return response()->json([
			'status' => 'success',
			'url' => route('invoice.create'),
		]);
	}

	public function generatePdf(Request $request)
	{
		$res = json_decode($request->invoice);
		$data = (array)$res;
		$data['customer_email'] = @$data['customer']->email_address;

		if ($data['due_date']) {
			$data['due_date'] = Carbon::createFromFormat('d/m/Y', $data['due_date']);
		}

		if ($data['first_pay_date']) {
			$data['first_pay_date'] = Carbon::createFromFormat('d/m/Y', $data['first_pay_date']);
		}

		$data['email'] = auth()->user()->email;
		$data['phone'] = auth()->user()->mobile;

		$pdf = App::make('dompdf.wrapper');
		$pdf->loadView('user.invoice.invoicePdf.pdf', $data);

		if ($data['clickBtn'] == 0) {
			return $pdf->stream();
		} else {
			return $pdf->download();
		}
	}

	public function encrypt($data)
	{
		return implode(unpack("H*", $data));
	}

	public function currencyCheck(Request $request)
	{
		$data = $this->checkInitiateAmountValidate($request->id, null);
		return response()->json([
			'status' => 'success',
			'value' => $data
		]);
	}

	public function checkInitiateAmountValidate($currency_id, $gradTotal = null)
	{
		$chargesLimit = ChargesLimit::with('currency')->where(['currency_id' => $currency_id, 'transaction_type_id' => config('transactionType.invoice'), 'is_active' => 1])->first();
		$wallet = Wallet::firstOrCreate(['user_id' => Auth::id(), 'currency_id' => $currency_id]);


		$min_limit = 0;
		$max_limit = 0;
		$fixed_charge = 0;
		$percentage = 0;

		if ($chargesLimit) {
			$percentage = getAmount($chargesLimit->percentage_charge, 8);
			$valueAfterPercent = getAmount(($gradTotal * $percentage) / 100, 8);
			$fixed_charge = getAmount($chargesLimit->fixed_charge, 8);
			$min_limit = getAmount($chargesLimit->min_limit, 8);
			$max_limit = getAmount($chargesLimit->max_limit, 8);
			$charge = getAmount($valueAfterPercent + $fixed_charge, 8);
		}
		if ($gradTotal != null) {
			if (($gradTotal + $charge) > $max_limit || ($gradTotal + $charge) < $min_limit) {
				return "false";
			}
		}

		$data['fixed_charge'] = $fixed_charge;
		$data['percentage_charge'] = $percentage;
		$data['min_limit'] = $min_limit;
		$data['max_limit'] = $max_limit;
		$data['valueAfterPercent'] = $valueAfterPercent;
		$data['charge'] = $charge;

		return $data;
	}

	public function showPublicInvoice($hash_slug)
	{
		$data['invoice'] = Invoice::where('has_slug', $hash_slug)->firstOrFail();
		return view('user.invoice.publicPayment.payment-show', $data);
	}

	public function publicInvoicePaymentConfirm(Request $request, $hash_slug)
	{
		$reqStatus = $request->status;
		$invoice = Invoice::where('has_slug', $hash_slug)->firstOrFail();

		if ($invoice->status == null && ($reqStatus == 2 || $reqStatus == 5)) {
			if ($reqStatus == 5) {
				$invoice->status = 'rejected';
				$invoice->rejected_at = Carbon::now();
				$invoice->save();
				return redirect(route('home'))->with('success', 'Transaction canceled');
			} else {
				return redirect(route('invoice.public.payment', $hash_slug));
			}
		}
	}

	public function rejectInvoiceFromEmail($hash_slug)
	{
		$invoice = Invoice::where('has_slug', $hash_slug)->where('status', null)->firstOrFail();
		$invoice->status = 'rejected';
		$invoice->rejected_at = Carbon::now();
		$invoice->save();
		return redirect(route('invoice.public.payment', $hash_slug));
	}

	public function invoicePublicPayment(Request $request, $hash_slug)
	{
		$invoice = Invoice::doesntHave('successDepositable')->where('status', null)->where('has_slug', $hash_slug)->firstOrFail();

		if ($invoice->charge_pay == 1) {
			$invoiceCharge = $invoice->charge;
		} else {
			$invoiceCharge = 0;
		}

		if ($request->isMethod('get')) {
			$methods = Gateway::orderBy('sort_by', 'ASC')->get();
			return view('user.invoice.publicPayment.payment', compact('methods', 'invoice', 'invoiceCharge'));
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());
			$validationRules = [
				'amount' => 'required|numeric|min:1|not_in:0',
				'currency' => 'required|integer|min:1|not_in:0',
				'methodId' => 'required|integer|min:1|not_in:0',
			];

			$validate = Validator::make($purifiedData, $validationRules);
			if ($validate->fails()) {
				return back()->withErrors($validate)->withInput();
			}

			$purifiedData = (object)$purifiedData;
			$amount = $purifiedData->amount;
			$currency_id = $purifiedData->currency;
			$methodId = $purifiedData->methodId;

			$checkAmountValidate = $this->checkAmountValidate($amount, $currency_id, config('transactionType.deposit'), $methodId, $invoiceCharge);//7 = deposit

			if (!$checkAmountValidate['status']) {
				return back()->withInput()->with('alert', $checkAmountValidate['message']);
			}

			$method = Gateway::findOrFail($methodId);

			$deposit = new Deposit();
			$deposit->currency_id = $currency_id;
			$deposit->payment_method_id = $methodId;
			$deposit->amount = $amount;
			$deposit->charges_limit_id = $checkAmountValidate['charges_limit_id'];
			$deposit->percentage = $checkAmountValidate['percentage'];
			$deposit->charge_percentage = $checkAmountValidate['percentage_charge'];
			$deposit->charge_fixed = $checkAmountValidate['fixed_charge'];
			$deposit->charge = $checkAmountValidate['charge'];
			$deposit->payable_amount = $checkAmountValidate['payable_amount'] * $checkAmountValidate['convention_rate'];
			$deposit->utr = Str::random(16);
			$deposit->status = 0;// 1 = success, 0 = pending
			$deposit->email = $invoice->customer_email;
			$deposit->payment_method_currency = $method->currency;
			$deposit->depositable_id = $invoice->id;
			$deposit->depositable_type = Invoice::class;
			$deposit->save();

			$checkAmountValidate = (new DepositController())->checkAmountValidate($deposit->amount, $deposit->currency_id, config('transactionType.deposit'), $deposit->payment_method_id);
			if (!$checkAmountValidate['status']) {
				return back()->withInput()->with('alert', $checkAmountValidate['message']);
			}

			return redirect(route('payment.process', $deposit->utr));
		}
	}

	public function checkAmountValidate($amount, $currency_id, $transaction_type_id, $methodId, $invoiceCharge = null)
	{
		$chargesLimit = ChargesLimit::where(['currency_id' => $currency_id, 'transaction_type_id' => $transaction_type_id, 'payment_method_id' => $methodId, 'is_active' => 1])->firstOrFail();

		if (Auth::check()) {
			$wallet = Wallet::firstOrCreate(['user_id' => Auth::id(), 'currency_id' => $currency_id]);
			$balance = $wallet->balance;
		} else {
			$balance = 0;
		}


		$status = false;
		$charge = 0;
		$min_limit = 0;
		$max_limit = 0;
		$fixed_charge = 0;
		$percentage = 0;
		$percentage_charge = 0;

		if ($chargesLimit) {
			$percentage = getAmount($chargesLimit->percentage_charge);
			$percentage_charge = ($amount * $percentage) / 100;
			$fixed_charge = getAmount($chargesLimit->fixed_charge);
			$min_limit = getAmount($chargesLimit->min_limit);
			$max_limit = getAmount($chargesLimit->max_limit);
			$charge = $percentage_charge + $fixed_charge;
		}
		//Total amount with all fixed and percent charge for deduct

		$payable_amount = $amount + $charge + $invoiceCharge;

		$new_balance = $balance + $amount;

		//Currency inactive
		if ($min_limit == 0 && $max_limit == 0) {
			$message = "Payment method not available for this transaction";
		} elseif ($amount < $min_limit || $amount > $max_limit) {
			$message = "minimum payment $min_limit and maximum payment limit $max_limit";
		} else {
			$status = true;
			$message = "Updated balance : $new_balance";
		}

		$data['status'] = $status;
		$data['charges_limit_id'] = $chargesLimit->id;
		$data['message'] = $message;
		$data['fixed_charge'] = $fixed_charge;
		$data['percentage'] = $percentage;
		$data['percentage_charge'] = $percentage_charge;
		$data['min_limit'] = $min_limit;
		$data['max_limit'] = $max_limit;
		$data['balance'] = $balance;
		$data['payable_amount'] = $payable_amount;
		$data['new_balance'] = $new_balance;
		$data['charge'] = $charge;
		$data['amount'] = $amount;
		$data['convention_rate'] = $chargesLimit->convention_rate;
		$data['currency_id'] = $currency_id;

		return $data;
	}

	public function viewInvoice($id)
	{
		$data['invoice'] = Invoice::findOrFail($id);
		return view('user.invoice.details', $data);
	}

	public function downloadPDF($Id)
	{
		$invoice = Invoice::with(['items', 'recuring_invoice'])->findOrFail($Id);

		$data = [
			'invoice_number' => $invoice->invoice_number,
			'customer_email' => $invoice->customer_email ?? $invoice->customer->email_address,
			'email' => optional($invoice->sender)->email,
			'phone' => optional($invoice->sender)->mobile,
			'currency' => optional($invoice->currency)->symbol,
			'payment' => $invoice->frequency,
			'due_date' => $invoice->due_date,
			'first_pay_date' => optional($invoice->recuring_invoice)->first_arrival_date ?? '',
			'items' => $invoice->items,
			'subtotal' => $invoice->subtotal,
			'tax' => $invoice->tax,
			'taxRate' => $invoice->tax_rate,
			'vat' => $invoice->vat,
			'vatRate' => $invoice->vat_rate,
			'grandTotal' => $invoice->grand_total,
			'note' => $invoice->note,
		];

		$pdf = App::make('dompdf.wrapper');
		$pdf->loadView('user.invoice.invoicePdf.pdf', $data);

		return $pdf->stream();
	}

	public function invoiceReminder(Request $request)
	{
		$invoice = Invoice::where('id', $request->invoiceId)->firstOrFail();
		if ($invoice->status == 'paid' || $invoice->status == 'rejected') {
			return back()->with('error', 'you can not send reminder complete invoice');
		}

		Mail::to($invoice->customer_email)
			->queue(new ReminderMail((object)$invoice));

		$invoice->reminder_at = Carbon::now();
		$invoice->save();
		return back()->with('success', 'Reminder send');
	}

	public function _filter($request)
	{
		$userId = Auth::id();
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$search = $request->all();
		$created_date = isset($search['created_at']) ? preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']) : 0;

		$invoices = Invoice::with('sender', 'currency')
			->when(isset($search['email']), function ($query) use ($search) {
				return $query->where('customer_email', 'LIKE', "%{$search['email']}%");
			})
			->when(isset($search['hash_slug']), function ($query) use ($search) {
				return $query->where('has_slug', 'LIKE', "%{$search['hash_slug']}%");
			})
			->when(isset($search['min']), function ($query) use ($search) {
				return $query->where('grand_total', '>=', $search['min']);
			})
			->when(isset($search['max']), function ($query) use ($search) {
				return $query->where('grand_total', '<=', $search['max']);
			})
			->when(isset($search['currency_id']), function ($query) use ($search) {
				return $query->where('currency_id', $search['currency_id']);
			})
			->when($created_date == 1, function ($query) use ($search) {
				return $query->whereDate("created_at", $search['created_at']);
			})
			->when($search['status'] == 'paid', function ($query) use ($search) {
				return $query->where('status', 'paid');
			})
			->when($search['status'] == 'unpaid', function ($query) use ($search) {
				return $query->whereNull('status');
			})
			->when($search['status'] == 'rejected', function ($query) use ($search) {
				return $query->where('status', 'rejected');
			});


		$data = [
			'userId' => $userId,
			'currencies' => $currencies,
			'search' => $search,
			'invoices' => $invoices,
		];
		return $data;
	}
}
