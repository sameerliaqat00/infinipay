<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Voucher;
use Illuminate\Http\Request;

class AdminVoucherController extends Controller
{
	public function index()
	{
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$vouchers = Voucher::with(['sender', 'receiver','sender.profile', 'receiver.profile', 'currency'])
			->latest()
			->paginate();
		return view('admin.voucher.index', compact('currencies', 'vouchers'));
	}

	public function search(Request $request)
	{
		$filterData = $this->_filter($request);
		$search = $filterData['search'];
		$currencies = $filterData['currencies'];
		$vouchers = $filterData['vouchers']
			->latest()
			->paginate();
		$vouchers->appends($filterData['search']);
		return view('admin.voucher.index', compact('search', 'currencies', 'vouchers'));
	}

	public function _filter($request)
	{
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$search = $request->all();
		$created_date = isset($search['created_at']) ? preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']) : 0;

		$vouchers = Voucher::with(['sender', 'receiver','sender.profile', 'receiver.profile', 'currency'])
			->when(isset($search['email']), function ($query) use ($search) {
				return $query->where('email', 'LIKE', "%{$search['email']}%");
			})
			->when(isset($search['utr']), function ($query) use ($search) {
				return $query->where('utr', 'LIKE', "%{$search['utr']}%");
			})
			->when(isset($search['min']), function ($query) use ($search) {
				return $query->where('amount', '>=', $search['min']);
			})
			->when(isset($search['max']), function ($query) use ($search) {
				return $query->where('amount', '<=', $search['max']);
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
			->when(isset($search['receiver']), function ($query) use ($search) {
				return $query->whereHas('receiver', function ($qry) use ($search) {
					$qry->where('name', 'LIKE', "%{$search['receiver']}%")
						->orWhere('username', 'LIKE', "{$search['receiver']}");
				});
			})
			->when($created_date == 1, function ($query) use ($search) {
				return $query->whereDate("created_at", $search['created_at']);
			})
			->when(isset($search['status']), function ($query) use ($search) {
				return $query->where('status', $search['status']);
			});

		$data = [
			'currencies' => $currencies,
			'search' => $search,
			'vouchers' => $vouchers,
		];
		return $data;
	}


	public function showByUser($userId)
	{
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$vouchers = Voucher::with(['sender', 'receiver','sender.profile', 'receiver.profile', 'currency'])
			->where(function ($query) use ($userId) {
				$query->where('sender_id', '=', $userId);
				$query->orWhere('receiver_id', '=', $userId);
			})
			->latest()->paginate();
		return view('admin.voucher.index', compact('currencies', 'vouchers', 'userId'));
	}

	public function searchByUser(Request $request, $userId)
	{
		$filterData = $this->_filter($request);
		$search = $filterData['search'];
		$currencies = $filterData['currencies'];
		$vouchers = $filterData['vouchers']
			->where(function ($query) use ($userId) {
				$query->where('sender_id', '=', $userId);
				$query->orWhere('receiver_id', '=', $userId);
			})
			->latest()
			->paginate();
		$vouchers->appends($filterData['search']);
		return view('admin.voucher.index', compact('search', 'vouchers', 'currencies', 'userId'));
	}
}
