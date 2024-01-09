<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exchange;
use App\Models\Wallet;
use Illuminate\Http\Request;

class AdminExchangeController extends Controller
{
	public function index()
	{
		$wallets = Wallet::with('currency')->get();
		$exchanges = Exchange::with(['fromWallet', 'toWallet', 'fromWallet.currency', 'toWallet.currency','user','user.profile'])
			->latest()
			->paginate();
		return view('admin.exchange.index', compact('wallets', 'exchanges'));
	}

	public function search(Request $request)
	{
		$filterData = $this->_filter($request);
		$search = $filterData['search'];
		$wallets = $filterData['wallets'];
		$exchanges = $filterData['exchanges']
			->latest()
			->paginate();
		$exchanges->appends($filterData['search']);
		return view('admin.exchange.index', compact('search', 'wallets', 'exchanges'));
	}

	public function _filter($request)
	{
		$wallets = Wallet::with('currency')->get();
		$search = $request->all();
		$created_date = isset($search['created_at']) ? preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']) : 0;

		$exchanges = Exchange::with('fromWallet', 'toWallet', 'fromWallet.currency', 'toWallet.currency','user','user.profile')
			->when(isset($search['utr']), function ($query) use ($search) {
				return $query->where('utr', 'LIKE', "%{$search['utr']}%");
			})
			->when(isset($search['min']), function ($query) use ($search) {
				return $query->where('amount', '>=', $search['min']);
			})
			->when(isset($search['max']), function ($query) use ($search) {
				return $query->where('amount', '<=', $search['max']);
			})
			->when($created_date == 1, function ($query) use ($search) {
				return $query->whereDate("created_at", $search['created_at']);
			})
			->when(isset($search['status']), function ($query) use ($search) {
				return $query->where('status', $search['status']);
			})
			->when(isset($search['from_wallet']), function ($query) use ($search) {
				return $query->where('from_wallet', $search['from_wallet']);
			})
			->when(isset($search['to_wallet']), function ($query) use ($search) {
				return $query->where('to_wallet', $search['to_wallet']);
			});

		$data = [
			'wallets' => $wallets,
			'search' => $search,
			'exchanges' => $exchanges,
		];
		return $data;
	}

	public function showByUser($userId)
	{
		$wallets = Wallet::with('currency')->get();
		$exchanges = Exchange::with(['fromWallet', 'toWallet', 'fromWallet.currency', 'toWallet.currency','user','user.profile'])->where(['user_id' => $userId])
			->latest()
			->paginate();
		return view('admin.exchange.index', compact('wallets','exchanges','userId'));
	}

	public function searchByUser(Request $request, $userId)
	{
		$filterData = $this->_filter($request);
		$search = $filterData['search'];
		$wallets = $filterData['wallets'];
		$exchanges = $filterData['exchanges']
			->where(['user_id' => $userId])
			->latest()
			->paginate();
		$exchanges->appends($filterData['search']);
		return view('admin.exchange.index', compact('search','wallets','exchanges','userId'));
	}
}
