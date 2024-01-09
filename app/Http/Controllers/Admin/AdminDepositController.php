<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\Fund;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Validator;

class AdminDepositController extends Controller
{
	use Notify;

	public function index()
	{
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$deposits = Deposit::with(['sender', 'receiver', 'currency'])
			->latest()->paginate();
		return view('admin.deposit.index', compact('currencies', 'deposits'));
	}

	public function search(Request $request)
	{
		$filterData = $this->_filter($request);
		$search = $filterData['search'];
		$currencies = $filterData['currencies'];
		$deposits = $filterData['deposits']
			->latest()
			->paginate();
		$deposits->appends($filterData['search']);
		return view('admin.deposit.index', compact('search', 'currencies', 'deposits'));
	}

	public function _filter($request)
	{
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$search = $request->all();
		$created_date = isset($search['created_at']) ? preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']) : 0;

		$deposits = Deposit::with('sender', 'receiver', 'currency')
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
			->when(isset($search['status']), function ($query) use ($search) {
				return $query->where('status', $search['status']);
			})
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
			->when($created_date == 1, function ($query) use ($search) {
				return $query->whereDate("created_at", $search['created_at']);
			});

		$data = [
			'currencies' => $currencies,
			'search' => $search,
			'deposits' => $deposits,
		];
		return $data;
	}

	public function showByUser($userId)
	{
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$deposits = Deposit::with(['sender', 'receiver', 'currency'])
			->where('user_id', $userId)
			->latest()
			->paginate();
		return view('admin.deposit.index', compact('currencies', 'deposits', 'userId'));
	}

	public function searchByUser(Request $request, $userId)
	{
		$filterData = $this->_filter($request);
		$search = $filterData['search'];
		$currencies = $filterData['currencies'];
		$deposits = $filterData['deposits']
			->where('user_id', $userId)
			->latest()
			->paginate();
		$deposits->appends($filterData['search']);
		return view('admin.deposit.index', compact('search', 'currencies', 'deposits', 'userId'));
	}

	public function addBalanceUser(Request $request, $userId)
	{
		$user = User::find($userId);
		if ($request->isMethod('get')) {
			$currencies = Currency::select('id', 'code', 'name')->where('is_active', 1)->get();
			return view('admin.deposit.addBalance', compact('currencies', 'user'));
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());
			$validationRules = [
				'amount' => 'required|numeric|min:1|not_in:0',
				'currency' => 'required|integer|min:1|not_in:0',
				'note' => 'nullable|string|min:5|max:250',
			];

			$validate = Validator::make($purifiedData, $validationRules);
			if ($validate->fails()) {
				return back()->withErrors($validate)->withInput();
			}
			$purifiedData = (object)$purifiedData;

			$amount = $purifiedData->amount;
			$currency_id = $purifiedData->currency;
			$note = $purifiedData->note;

			$fund = new Fund();
			$fund->user_id = $userId;
			$fund->currency_id = $currency_id;
			$fund->percentage = 0;
			$fund->charge_percentage = 0;
			$fund->charge_fixed = 0;
			$fund->charge = 0;
			$fund->amount = $amount;
			$fund->email = $user->email;
			$fund->status = 1;
			$fund->note = $note;
			$fund->utr = (string)Str::uuid();
			$fund->save();

			$mainBalance = updateWallet($fund->user_id, $fund->currency_id, $amount, 1);

			$transaction = new Transaction();
			$transaction->amount = $fund->amount;
			$transaction->charge = $fund->charge;
			$transaction->currency_id = $fund->currency_id;
			$fund->transactional()->save($transaction);

			$msg = [
				'amount' => getAmount($fund->amount),
				'currency' => Currency::find($fund->currency_id)->code ?? 'USD',
				'main_balance' => getAmount($mainBalance),
				'transaction' => $transaction->trx_id
			];
			$action = [
				"link" => route('user.transaction'),
				"icon" => "fa fa-money-bill-alt text-white"
			];
			$firebaseAction = route('user.transaction');
			$this->userFirebasePushNotification($user, 'ADD_BALANCE', $msg, $firebaseAction);
			$this->userPushNotification($user, 'ADD_BALANCE', $msg, $action);
			$this->sendMailSms($user, 'ADD_BALANCE', $msg);
			return redirect(route('user.edit', $user))->with('success', 'Balance add successfully');
		}
	}
}
