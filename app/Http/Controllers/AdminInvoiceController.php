<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Invoice;
use Illuminate\Http\Request;

class AdminInvoiceController extends Controller
{
	public function index()
	{
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$invoices = Invoice::with(['sender', 'sender.profile', 'currency'])
			->latest()
			->paginate();
		return view('admin.invoice.index', compact('currencies', 'invoices'));
	}

	public function search(Request $request)
	{
		$filterData = $this->_filter($request);
		$search = $filterData['search'];
		$currencies = $filterData['currencies'];
		$invoices = $filterData['invoices']
			->latest()
			->paginate();
		$invoices->appends($filterData['search']);
		return view('admin.invoice.index', compact('search', 'currencies', 'invoices'));
	}

	public function _filter($request)
	{
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
			->when(isset($search['sender']), function ($query) use ($search) {
				return $query->whereHas('sender', function ($qry) use ($search) {
					$qry->where('name', 'LIKE', "%{$search['sender']}%")
						->orWhere('username', 'LIKE', "{$search['sender']}");
				});
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
			'currencies' => $currencies,
			'search' => $search,
			'invoices' => $invoices,
		];
		return $data;
	}


	public function showByUser($userId)
	{
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$invoices = Invoice::with(['sender', 'sender.profile', 'currency'])
			->where(function ($query) use ($userId) {
				$query->where('sender_id', '=', $userId);
			})
			->latest()->paginate();
		return view('admin.invoice.index', compact('currencies', 'invoices', 'userId'));
	}

	public function searchByUser(Request $request, $userId)
	{
		$filterData = $this->_filter($request);
		$search = $filterData['search'];
		$currencies = $filterData['currencies'];
		$invoices = $filterData['invoices']
			->where(function ($query) use ($userId) {
				$query->where('sender_id', '=', $userId);
			})
			->latest()
			->paginate();
		$invoices->appends($filterData['search']);
		return view('admin.invoice.index', compact('search', 'invoices', 'currencies', 'userId'));
	}
}
