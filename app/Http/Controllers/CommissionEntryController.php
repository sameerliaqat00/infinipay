<?php

namespace App\Http\Controllers;

use App\Models\CommissionEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommissionEntryController extends Controller
{
	public function index()
	{
		$userId = Auth::id();
		$commissionEntries = CommissionEntry::with(['sender', 'sender.profile','receiver', 'receiver.profile', 'currency'])
			->where(function ($query) use ($userId) {
				$query->where('to_user', $userId);
				$query->orWhere('from_user', $userId);
			})
			->latest()
			->paginate();
		return view('user.referralBonus.index', compact('commissionEntries'));
	}

	public function search(Request $request)
	{
		$filterData = $this->_filter($request);
		$search = $filterData['search'];
		$commissionEntries = $filterData['commissionEntries']
			->latest()
			->paginate();
		$commissionEntries->appends($filterData['search']);
		return view('user.referralBonus.index', compact('search', 'commissionEntries'));
	}

	public function _filter($request)
	{
		$userId = Auth::id();
		$search = $request->all();
		$created_date = isset($search['created_at']) ? preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']) : 0;

		$commissionEntries = CommissionEntry::with(['sender', 'sender.profile','receiver', 'receiver.profile', 'currency'])
			->where(function ($query) use ($userId) {
				$query->where('sender_id', $userId);
				$query->orWhere('receiver_id', $userId);
			})
			->when(isset($search['sender']), function ($query) use ($search) {
				return $query->whereHas('sender', function ($qry) use ($search) {
					$qry->where('name', 'LIKE', "%{$search['sender']}%")
					->orWhere('username', 'LIKE', "%{$search['sender']}%");
				});
			})
			->when(isset($search['receiver']), function ($query) use ($search) {
				return $query->whereHas('receiver', function ($qry) use ($search) {
					$qry->where('name', 'LIKE', "%{$search['receiver']}%")
						->orWhere('username', "%{$search['receiver']}%");
				});
			})
			->when(isset($search['email']), function ($query) use ($search) {
				return $query->where('email', 'LIKE', "%{$search['email']}%");
			})
			->when(isset($search['utr']), function ($query) use ($search) {
				return $query->where('utr', 'LIKE', "%{$search['utr']}%");
			})
			->when(isset($search['min']), function ($query) use ($search) {
				return $query->where('commission_amount', '>=', $search['min']);
			})
			->when(isset($search['max']), function ($query) use ($search) {
				return $query->where('commission_amount', '<=', $search['max']);
			})
			->when($created_date == 1, function ($query) use ($search) {
				return $query->whereDate("created_at", $search['created_at']);
			});

		$data = [
			'search' => $search,
			'commissionEntries' => $commissionEntries,
		];
		return $data;
	}


	public function indexAdmin()
	{
		$commissionEntries = CommissionEntry::with(['sender', 'sender.profile','receiver', 'receiver.profile', 'currency'])
			->latest()->paginate();
		return view('admin.referralBonus.index', compact('commissionEntries'));
	}

	public function searchAdmin(Request $request)
	{
		$filterData = $this->_filterAdmin($request);
		$search = $filterData['search'];
		$commissionEntries = $filterData['commissionEntries']
			->latest()
			->paginate();
		$commissionEntries->appends($filterData['search']);
		return view('admin.referralBonus.index', compact('search', 'commissionEntries'));
	}

	public function _filterAdmin($request)
	{
		$search = $request->all();
		$created_date = isset($search['created_at']) ? preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']) : 0;

		$commissionEntries = CommissionEntry::with(['sender', 'sender.profile','receiver', 'receiver.profile', 'currency'])
			->when(isset($search['sender']), function ($query) use ($search) {
				return $query->whereHas('sender', function ($qry) use ($search) {
					$qry->where('name', 'LIKE', "%{$search['sender']}%");
				});
			})
			->when(isset($search['receiver']), function ($query) use ($search) {
				return $query->whereHas('receiver', function ($qry) use ($search) {
					$qry->where('name', 'LIKE', "%{$search['receiver']}%");
				});
			})
			->when(isset($search['email']), function ($query) use ($search) {
				return $query->where('email', 'LIKE', "%{$search['email']}%");
			})
			->when(isset($search['utr']), function ($query) use ($search) {
				return $query->where('utr', 'LIKE', "%{$search['utr']}%");
			})
			->when(isset($search['min']), function ($query) use ($search) {
				return $query->where('commission_amount', '>=', $search['min']);
			})
			->when(isset($search['max']), function ($query) use ($search) {
				return $query->where('commission_amount', '<=', $search['max']);
			})
			->when($created_date == 1, function ($query) use ($search) {
				return $query->whereDate("created_at", $search['created_at']);
			});

		$data = [
			'search' => $search,
			'commissionEntries' => $commissionEntries,
		];
		return $data;
	}
}
