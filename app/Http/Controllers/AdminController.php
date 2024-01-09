<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Deposit;
use App\Models\FirebaseNotify;
use App\Models\Payout;
use App\Models\RedeemCode;
use App\Models\RequestMoney;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Voucher;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
	public function index()
	{

		$basicControl = basicControl();
		$last30 = date('Y-m-d', strtotime('-30 days'));
		$last7 = date('Y-m-d', strtotime('-7 days'));
		$today = today();
		$dayCount = date('t', strtotime($today));

		$users = User::selectRaw('COUNT(id) AS totalUser')
			->selectRaw('COUNT((CASE WHEN created_at >= CURDATE()  THEN id END)) AS todayJoin')
			->selectRaw('COUNT((CASE WHEN status = 1  THEN id END)) AS activeUser')
			->selectRaw('COUNT((CASE WHEN email_verified_at IS NOT NULL  THEN id END)) AS verifiedUser')
			->get()->makeHidden(['mobile', 'profile'])->toArray();

		$data['userRecord'] = collect($users)->collapse();

		$data['wallets'] = Wallet::with('currency:id,name,code,logo,symbol')
			->groupBy('currency_id')
			->selectRaw('SUM(balance) as totalBalance, currency_id')
			->get()->toArray();

		$transfer = Transfer::where('status', 1)
			->with('currency:id,name,code,logo,symbol,exchange_rate')
			->groupBy('currency_id')
			->selectRaw("SUM((CASE WHEN created_at >= $last30 THEN amount END)) as transfer_30_days, currency_id")
			->selectRaw("SUM((CASE WHEN created_at >= $last7 THEN amount END)) as transfer_7_days, currency_id")
			->selectRaw("SUM((CASE WHEN created_at >= CURDATE() THEN amount END)) as transfer_today, currency_id")
			->selectRaw("SUM((CASE WHEN created_at >= $last30 THEN charge END)) as transfer_income_30_days, currency_id")
			->get()->map(function ($item) {
				return [
					'transfer_30_days' => $item->transfer_30_days / $item->currency->exchange_rate,
					'transfer_7_days' => $item->transfer_7_days / $item->currency->exchange_rate,
					'transfer_today' => $item->transfer_today / $item->currency->exchange_rate,
					'transfer_income_30_days' => $item->transfer_income_30_days / $item->currency->exchange_rate,
				];
			});
		$data['transfer'] = [
			'transfer_30_days' => $transfer->sum('transfer_30_days'),
			'transfer_7_days' => $transfer->sum('transfer_7_days'),
			'transfer_today' => $transfer->sum('transfer_today'),
			'transfer_income_30_days' => $transfer->sum('transfer_income_30_days'),
		];

		$requestMoney = RequestMoney::where('status', 1)
			->with('currency:id,name,code,logo,symbol,exchange_rate')
			->groupBy('currency_id')
			->selectRaw("SUM((CASE WHEN created_at >= $last30 THEN amount END)) as request_money_30_days, currency_id")
			->selectRaw("SUM((CASE WHEN created_at >= $last7 THEN amount END)) as request_money_7_days, currency_id")
			->selectRaw("SUM((CASE WHEN created_at >= CURDATE() THEN amount END)) as request_money_today, currency_id")
			->selectRaw("SUM((CASE WHEN created_at >= $last30 THEN charge END)) as request_money_income_30_days, currency_id")
			->get()->map(function ($item) {
				return [
					'request_money_30_days' => $item->request_money_30_days / $item->currency->exchange_rate,
					'request_money_7_days' => $item->request_money_7_days / $item->currency->exchange_rate,
					'request_money_today' => $item->request_money_today / $item->currency->exchange_rate,
					'request_money_income_30_days' => $item->request_money_income_30_days / $item->currency->exchange_rate,
				];
			});
		$data['requestMoney'] = [
			'request_money_30_days' => $requestMoney->sum('request_money_30_days'),
			'request_money_7_days' => $requestMoney->sum('request_money_7_days'),
			'request_money_today' => $requestMoney->sum('request_money_today'),
			'request_money_income_30_days' => $requestMoney->sum('request_money_income_30_days'),
		];

		$redeemCode = RedeemCode::where('status', 1)
			->with('currency:id,name,code,logo,symbol,exchange_rate')
			->groupBy('currency_id')
			->selectRaw("SUM((CASE WHEN created_at >= $last30 THEN amount END)) as redeemCode_30_days, currency_id")
			->selectRaw("SUM((CASE WHEN created_at >= $last30 THEN charge END)) as redeemCode_income_30_days, currency_id")
			->get()->map(function ($item) {
				return [
					'redeemCode_30_days' => $item->redeemCode_30_days / $item->currency->exchange_rate,
					'redeemCode_income_30_days' => $item->redeemCode_income_30_days / $item->currency->exchange_rate,
				];
			});
		$data['redeemCode'] = [
			'redeemCode_30_days' => $redeemCode->sum('redeemCode_30_days'),
			'redeemCode_income_30_days' => $redeemCode->sum('redeemCode_income_30_days'),
		];

		$voucher = Voucher::where('status', 1)
			->with('currency:id,name,code,logo,symbol,exchange_rate')
			->groupBy('currency_id')
			->selectRaw("SUM((CASE WHEN created_at >= $last30 THEN amount END)) as voucher_30_days, currency_id")
			->selectRaw("SUM((CASE WHEN created_at >= $last30 THEN charge END)) as voucher_income_30_days, currency_id")
			->get()
			->map(function ($item) {
				return [
					'voucher_30_days' => $item->voucher_30_days / $item->currency->exchange_rate,
					'voucher_income_30_days' => $item->voucher_income_30_days / $item->currency->exchange_rate,
				];
			});
		$data['voucher'] = [
			'voucher_30_days' => $voucher->sum('voucher_30_days'),
			'voucher_income_30_days' => $voucher->sum('voucher_income_30_days'),
		];

		$data['users'] = User::with('profile')->latest()->limit(5)->get();

		$transactions = Transaction::select('created_at', 'currency_id')
			->whereMonth('created_at', $today)
			->groupBy([DB::raw("DATE_FORMAT(created_at, '%j')"), 'currency_id'])
			->selectRaw("SUM(CASE WHEN transactional_type like '%Deposit' THEN amount ELSE 0 END) as Deposit")
			->selectRaw("SUM(CASE WHEN transactional_type like '%Fund' THEN amount ELSE 0 END) as Fund")
			->selectRaw("SUM(CASE WHEN transactional_type like '%Transfer' THEN amount ELSE 0 END) as Transfer")
			->selectRaw("SUM(CASE WHEN transactional_type like '%RequestMoney' THEN amount ELSE 0 END) as RequestMoney")
			->selectRaw("SUM(CASE WHEN transactional_type like '%Voucher' THEN amount ELSE 0 END) as Voucher")
			->selectRaw("SUM(CASE WHEN transactional_type like '%Invoice' THEN amount ELSE 0 END) as Invoice")
			->selectRaw("SUM(CASE WHEN transactional_type like '%QRCode' THEN amount ELSE 0 END) as QRPaymentAmount")
			->selectRaw("SUM(CASE WHEN transactional_type like '%Redeem' THEN amount ELSE 0 END) as Redeem")
			->selectRaw("SUM(CASE WHEN transactional_type like '%Escrow' THEN amount ELSE 0 END) as Escrow")
			->selectRaw("SUM(CASE WHEN transactional_type like '%Payout' THEN amount ELSE 0 END) as Payout")
			->selectRaw("SUM(CASE WHEN transactional_type like '%Exchange' THEN amount ELSE 0 END) as Exchange")
			->selectRaw("SUM(CASE WHEN transactional_type like '%CommissionEntry' THEN amount ELSE 0 END) as CommissionEntry")
			->get()
			->groupBy([function ($query) {
				return $query->created_at->format('j');
			}, 'currency_id']);

		$labels = [];
		$dataDeposit = [];
		$dataFund = [];
		$dataTransfer = [];
		$dataRequestMoney = [];
		$dataVoucher = [];
		$dataInvoice = [];
		$dataQRPaymentAmount = [];
		$dataRedeem = [];
		$dataEscrow = [];
		$dataPayout = [];
		$dataExchange = [];
		$dataDispute = [];
		$dataCommissionEntry = [];
		for ($i = 1; $i <= $dayCount; $i++) {
			$labels[] = date('jS M', strtotime(date('Y/m/') . $i));
			$currentDeposit = 0;
			$currentFund = 0;
			$currentTransfer = 0;
			$currentRequestMoney = 0;
			$currentVoucher = 0;
			$currentInvoice = 0;
			$currentQRPaymentAmount = 0;
			$currentRedeem = 0;
			$currentEscrow = 0;
			$currentPayout = 0;
			$currentExchange = 0;
			$currentDispute = 0;
			$currentCommissionEntry = 0;
			if (isset($transactions[$i])) {
				foreach ($transactions[$i] as $key => $transaction) {
					$currency = Currency::find($key);
					if ($currency) {
						$currentDeposit += $transactions[$i][$key][0]->Deposit / $currency->exchange_rate;
						$currentFund += $transactions[$i][$key][0]->Fund / $currency->exchange_rate;
						$currentTransfer += $transactions[$i][$key][0]->Transfer / $currency->exchange_rate;
						$currentRequestMoney += $transactions[$i][$key][0]->RequestMoney / $currency->exchange_rate;
						$currentVoucher += $transactions[$i][$key][0]->Voucher / $currency->exchange_rate;
						$currentInvoice += $transactions[$i][$key][0]->Invoice / $currency->exchange_rate;
						$currentQRPaymentAmount += $transactions[$i][$key][0]->QRPaymentAmount / $currency->exchange_rate;
						$currentRedeem += $transactions[$i][$key][0]->Redeem / $currency->exchange_rate;
						$currentEscrow += $transactions[$i][$key][0]->Escrow / $currency->exchange_rate;
						$currentPayout += $transactions[$i][$key][0]->Payout / $currency->exchange_rate;
						$currentExchange += $transactions[$i][$key][0]->Exchange / $currency->exchange_rate;
						$currentDispute += $transactions[$i][$key][0]->Dispute / $currency->exchange_rate;
						$currentCommissionEntry += $transactions[$i][$key][0]->CommissionEntry / $currency->exchange_rate;
					}
				}
			}
			$dataDeposit[] = round($currentDeposit, $basicControl->fraction_number);
			$dataFund[] = round($currentFund, $basicControl->fraction_number);
			$dataTransfer[] = round($currentTransfer, $basicControl->fraction_number);
			$dataRequestMoney[] = round($currentRequestMoney, $basicControl->fraction_number);
			$dataVoucher[] = round($currentVoucher, $basicControl->fraction_number);
			$dataInvoice[] = round($currentInvoice, $basicControl->fraction_number);
			$dataQRPaymentAmount[] = round($currentQRPaymentAmount, $basicControl->fraction_number);
			$dataRedeem[] = round($currentRedeem, $basicControl->fraction_number);
			$dataEscrow[] = round($currentEscrow, $basicControl->fraction_number);
			$dataPayout[] = round($currentPayout, $basicControl->fraction_number);
			$dataExchange[] = round($currentExchange, $basicControl->fraction_number);
			$dataDispute[] = round($currentDispute, $basicControl->fraction_number);
			$dataCommissionEntry[] = round($currentCommissionEntry, $basicControl->fraction_number);
		}

		$data['labels'] = $labels;
		$data['dataDeposit'] = $dataDeposit;
		$data['dataFund'] = $dataFund;
		$data['dataTransfer'] = $dataTransfer;
		$data['dataRequestMoney'] = $dataRequestMoney;
		$data['dataVoucher'] = $dataVoucher;
		$data['dataInvoice'] = $dataInvoice;
		$data['dataQRPaymentAmount'] = $dataQRPaymentAmount;
		$data['dataRedeem'] = $dataRedeem;
		$data['dataEscrow'] = $dataEscrow;
		$data['dataPayout'] = $dataPayout;
		$data['dataExchange'] = $dataExchange;
		$data['dataDispute'] = $dataDispute;
		$data['dataCommissionEntry'] = $dataCommissionEntry;

		$deposits = Deposit::select('currency_id', 'created_at')
			->where('status', 1)
			->whereYear('created_at', $today)
			->groupBy([DB::raw("DATE_FORMAT(created_at, '%m')"), 'currency_id'])
			->selectRaw("SUM(amount) as Deposit")
			->get()
			->groupBy([function ($query) {
				return $query->created_at->format('F');
			}, 'currency_id']);

		$payouts = Payout::select('currency_id', 'created_at')
			->whereYear('created_at', $today)
			->groupBy([DB::raw("DATE_FORMAT(created_at, '%m')"), 'currency_id'])
			->selectRaw("SUM(amount) as Payout")
			->get()
			->groupBy([function ($query) {
				return $query->created_at->format('F');
			}, 'currency_id']);

		$data['yearLabels'] = ['January', 'February', 'March', 'April', 'May', 'June', 'July ', 'August', 'September', 'October', 'November', 'December'];

		$yearDeposit = [];
		$yearPayout = [];

		foreach ($data['yearLabels'] as $yearLabel) {
			$currentYearDeposit = 0;
			$currentYearPayout = 0;

			if (isset($deposits[$yearLabel])) {
				foreach ($deposits[$yearLabel] as $key => $deposit) {
					$currency = Currency::find($key);
					if ($currency) {
						$currentYearDeposit += $deposit[0]->Deposit / $currency->exchange_rate;
					}
				}
			}
			if (isset($payouts[$yearLabel])) {
				foreach ($payouts[$yearLabel] as $key => $payout) {
					$currency = Currency::find($key);
					if ($currency) {
						$currentYearPayout += $payout[0]->Payout / $currency->exchange_rate;
					}
				}
			}

			$yearDeposit[] = round($currentYearDeposit, $basicControl->fraction_number);
			$yearPayout[] = round($currentYearPayout, $basicControl->fraction_number);
		}

		$data['yearDeposit'] = $yearDeposit;
		$data['yearPayout'] = $yearPayout;

		$paymentMethods = Deposit::with('gateway:id,name')
			->whereYear('created_at', $today)
			->where('status', 1)
			->groupBy(['payment_method_id', 'currency_id'])
			->selectRaw("SUM(amount) as totalAmount, payment_method_id, currency_id")
			->get()
			->groupBy(['payment_method_id', 'currency_id']);

		$paymentMethodeLabel = [];
		$paymentMethodeData = [];

		foreach ($paymentMethods as $paymentMethode) {
			$currentPaymentMethodeLabel = 0;
			$currentPaymentMethodeData = 0;

			foreach ($paymentMethode as $currency_id => $value) {
				$currency = Currency::find($currency_id);
				if ($currency) {

					$currentPaymentMethodeLabel = optional($value[0]->gateway)->name ?? 'N/A';
					$currentPaymentMethodeData += $value[0]->totalAmount / $currency->exchange_rate;

				}
			}
			$paymentMethodeLabel[] = $currentPaymentMethodeLabel;
			$paymentMethodeData[] = round($currentPaymentMethodeData, $basicControl->fraction_number);
		}

		$data['paymentMethodeLabel'] = $paymentMethodeLabel;
		$data['paymentMethodeData'] = $paymentMethodeData;
		$data['basicControl'] = $basicControl;
		$firebaseNotify = FirebaseNotify::first();
		return view('admin.home', $data, compact('firebaseNotify'));
	}

	public function changePassword(Request $request)
	{
		if ($request->isMethod('get')) {
			return view('admin.auth.passwords.change');
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());
			$validator = Validator::make($purifiedData, [
				'current_password' => 'required|min:5',
				'password' => 'required|min:5|confirmed',
			]);

			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}
			$user = Auth::user();
			$purifiedData = (object)$purifiedData;

			if (!Hash::check($purifiedData->current_password, $user->password)) {
				return back()->withInput()->withErrors(['current_password' => 'current password did not match']);
			}

			$user->password = bcrypt($purifiedData->password);
			$user->save();
			return back()->with('success', 'Password changed successfully');
		}
	}

	public function saveToken(Request $request)
	{
		auth()->user()->update(['fcm_token' => $request->token]);
		return response()->json(['token saved successfully.']);
	}
}
