<?php

namespace App\Http\Controllers;

use App\Models\ApiOrder;
use App\Models\BillPay;
use App\Models\CommissionEntry;
use App\Models\Currency;
use App\Models\Escrow;
use App\Models\Exchange;
use App\Models\Fund;
use App\Models\Invoice;
use App\Models\ProductOrder;
use App\Models\QRCode;
use App\Models\RedeemCode;
use App\Models\RequestMoney;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\VirtualCardOrder;
use App\Models\VirtualCardTransaction;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
	public function index()
	{
		$user = Auth::user();
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();

		$transactions = Transaction::with(['transactional' => function (MorphTo $morphTo) {
			$morphTo->morphWith([
				CommissionEntry::class => ['sender', 'receiver', 'currency'],
				Transfer::class => ['sender', 'receiver', 'currency'],
				RequestMoney::class => ['sender', 'receiver', 'currency'],
				RedeemCode::class => ['sender', 'receiver', 'currency'],
				Escrow::class => ['sender', 'receiver', 'currency'],
				Voucher::class => ['sender', 'receiver', 'currency'],
				Fund::class => ['sender', 'receiver', 'currency'],
				Exchange::class => ['user', 'fromWallet', 'toWallet'],
				Invoice::class => ['sender', 'currency'],
				QRCode::class => ['user', 'currency'],
				ProductOrder::class => ['user'],
				VirtualCardTransaction::class => ['user', 'currency'],
				VirtualCardOrder::class => ['user', 'chargeCurrency'],
				BillPay::class => ['user', 'baseCurrency'],
				ApiOrder::class => ['user', 'currency'],
			]);
		}])
			->whereHasMorph('transactional',
				[
					CommissionEntry::class,
					Transfer::class,
					RequestMoney::class,
					RedeemCode::class,
					Escrow::class,
					Voucher::class,
					Fund::class,
					Exchange::class,
					Invoice::class,
					QRCode::class,
					ProductOrder::class,
					VirtualCardTransaction::class,
					VirtualCardOrder::class,
					BillPay::class,
					ApiOrder::class,
				], function ($query, $type) use ($user) {
					if ($type == CommissionEntry::class) {
						$query->where(function ($query) use ($user) {
							$query->where('from_user', '=', $user->id);
							$query->orWhere('to_user', '=', $user->id);
						});
					} elseif ($type === Transfer::class || $type === RequestMoney::class || $type === RedeemCode::class || $type === Escrow::class || $type === Voucher::class) {
						$query->where(function ($query) use ($user) {
							$query->where('sender_id', '=', $user->id);
							$query->orWhere('receiver_id', '=', $user->id);
						});
					} elseif ($type === Fund::class || $type === Exchange::class || $type === ProductOrder::class || $type === QRCode::class || $type === VirtualCardTransaction::class || $type === VirtualCardOrder::class
						|| $type === BillPay::class || $type === ApiOrder::class) {
						$query->where('user_id', $user->id);
					} elseif ($type === Invoice::class) {
						$query->where('sender_id', $user->id);
					}
				})
			->latest()
			->paginate();

		return view('user.transaction.index', compact('currencies', 'transactions'));
	}

	public function search(Request $request)
	{
		$filterData = $this->_filter($request);
		$search = $filterData['search'];
		$currencies = $filterData['currencies'];
		$user = $filterData['user'];
		$transactions = $filterData['transactions']
			->latest()
			->paginate();
		$transactions->appends($filterData['search']);
		return view('user.transaction.index', compact('search', 'currencies', 'user', 'transactions'));
	}

	public function _filter($request)
	{
		$user = Auth::user();
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$search = $request->all();
		$created_date = isset($search['created_at']) ? preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']) : 0;

		if (isset($search['type'])) {
			if ($search['type'] == 'Transfer') {
				$morphWith = [Transfer::class => ['sender', 'receiver', 'currency']];
				$whereHasMorph = [Transfer::class];
			} elseif ($search['type'] == 'RequestMoney') {
				$morphWith = [RequestMoney::class => ['sender', 'receiver', 'currency']];
				$whereHasMorph = [RequestMoney::class];
			} elseif ($search['type'] == 'RedeemCode') {
				$morphWith = [RedeemCode::class => ['sender', 'receiver', 'currency']];
				$whereHasMorph = [RedeemCode::class];
			} elseif ($search['type'] == 'Escrow') {
				$morphWith = [Escrow::class => ['sender', 'receiver', 'currency']];
				$whereHasMorph = [Escrow::class];
			} elseif ($search['type'] == 'Voucher') {
				$morphWith = [Voucher::class => ['sender', 'receiver', 'currency']];
				$whereHasMorph = [Voucher::class];
			} elseif ($search['type'] == 'Invoice') {
				$morphWith = [Invoice::class => ['sender', 'currency']];
				$whereHasMorph = [Invoice::class];
			} elseif ($search['type'] == 'QRCode') {
				$morphWith = [QRCode::class => ['user', 'currency']];
				$whereHasMorph = [QRCode::class];
			} elseif ($search['type'] == 'ProductOrder') {
				$morphWith = [ProductOrder::class => ['user']];
				$whereHasMorph = [ProductOrder::class];
			} elseif ($search['type'] == 'VirtualCardTransaction') {
				$morphWith = [VirtualCardTransaction::class => ['user', 'currency']];
				$whereHasMorph = [VirtualCardTransaction::class];
			} elseif ($search['type'] == 'Fund') {
				$morphWith = [Fund::class => ['sender', 'receiver', 'currency']];
				$whereHasMorph = [Fund::class];
			} elseif ($search['type'] == 'BillPay') {
				$morphWith = [BillPay::class => ['user', 'baseCurrency']];
				$whereHasMorph = [BillPay::class];
			} elseif ($search['type'] == 'Exchange') {
				$morphWith = [Exchange::class => ['user', 'fromWallet', 'toWallet']];
				$whereHasMorph = [Exchange::class];
			} elseif ($search['type'] == 'CommissionEntry') {
				$morphWith = [CommissionEntry::class => ['sender', 'receiver', 'currency']];
				$whereHasMorph = [CommissionEntry::class];
			}
		} else {
			$morphWith = [
				CommissionEntry::class => ['sender', 'receiver', 'currency'],
				Transfer::class => ['sender', 'receiver', 'currency'],
				RequestMoney::class => ['sender', 'receiver', 'currency'],
				RedeemCode::class => ['sender', 'receiver', 'currency'],
				Escrow::class => ['sender', 'receiver', 'currency'],
				Voucher::class => ['sender', 'receiver', 'currency'],
				Fund::class => ['sender', 'receiver', 'currency'],
				Exchange::class => ['user', 'fromWallet', 'toWallet'],
				Invoice::class => ['sender', 'currency'],
				QRCode::class => ['user', 'currency'],
				ProductOrder::class => ['user'],
				VirtualCardTransaction::class => ['user', 'currency'],
				BillPay::class => ['user', 'baseCurrency'],
			];
			$whereHasMorph = [
				CommissionEntry::class,
				Transfer::class,
				RequestMoney::class,
				RedeemCode::class,
				Escrow::class,
				Voucher::class,
				Fund::class,
				Exchange::class,
				Invoice::class,
				QRCode::class,
				ProductOrder::class,
				VirtualCardTransaction::class,
				BillPay::class,
			];
		}

		$transactions = Transaction::with(['transactional' => function (MorphTo $morphTo) use ($morphWith, $whereHasMorph) {
			$morphTo->morphWith($morphWith);
		}])
			->whereHasMorph('transactional', $whereHasMorph, function ($query, $type) use ($search, $created_date, $user) {

				if ($type == CommissionEntry::class) {
					$query->where(function ($query) use ($user) {
						$query->where('from_user', '=', $user->id);
						$query->orWhere('to_user', '=', $user->id);
					});
				} elseif ($type === Transfer::class || $type === RequestMoney::class || $type === RedeemCode::class || $type === Escrow::class || $type === Voucher::class) {
					$query->where(function ($query) use ($user) {
						$query->where('sender_id', '=', $user->id);
						$query->orWhere('receiver_id', '=', $user->id);
					});
				} elseif ($type === Fund::class || $type === Exchange::class || $type === ProductOrder::class || $type === QRCode::class || $type === VirtualCardTransaction::class || $type === BillPay::class) {
					$query->where('user_id', $user->id);
				} elseif ($type === Invoice::class) {
					$query->where('sender_id', $user->id);
				}

				$query->when(isset($search['utr']), function ($query) use ($search) {
					return $query->where('utr', 'LIKE', $search['utr']);
				})
					->when(isset($search['email']), function ($query) use ($search, $type) {
						if ($type !== Exchange::class) {
							return $query->where('email', 'LIKE', "%{$search['email']}%");
						}
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
					->when($created_date == 1, function ($query) use ($search) {
						return $query->whereDate("created_at", $search['created_at']);
					});
			}
			);

		$data = [
			'user' => $user,
			'transactions' => $transactions,
			'search' => $search,
			'currencies' => $currencies,
		];
		return $data;
	}
}
