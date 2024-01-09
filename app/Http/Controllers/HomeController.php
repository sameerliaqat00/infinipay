<?php

namespace App\Http\Controllers;

use App\Models\ApiOrder;
use App\Models\BillPay;
use App\Models\Deposit;
use App\Models\FirebaseNotify;
use App\Models\Invoice;
use App\Models\Kyc;
use App\Models\PayoutMethod;
use App\Models\ProductOrder;
use App\Models\QRCode;
use App\Models\Store;
use App\Models\StoreProduct;
use App\Models\StoreShipping;
use App\Models\User;
use App\Models\UserKyc;
use App\Traits\Notify;
use Binance\Exception\ClientException;
use Binance\Exception\ServerException;
use Binance\Util\Url;
use Carbon\Carbon;
use DateTime;
use App\Models\Fund;
use App\Models\Escrow;
use App\Models\Wallet;
use App\Traits\Upload;
use App\Models\Content;
use App\Models\Gateway;
use App\Models\Voucher;
use App\Models\Currency;
use App\Models\Exchange;
use App\Models\Language;
use App\Models\Template;
use App\Models\Transfer;
use App\Models\Subscribe;
use App\Models\RedeemCode;
use App\Models\Transaction;
use App\Models\RequestMoney;
use Facades\App\Services\BasicCurl;
use Illuminate\Http\Request;
use App\Models\ContentDetails;
use App\Models\CommissionEntry;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Services\Payout\binance\Card;
use App\Console\Commands\BinanceCron;

class HomeController extends Controller
{
	use Upload;

	public function getTransactionChart(Request $request)
	{
		$start = $request->start;
		$end = $request->end;
		$user = Auth::user();

		$transactions = Transaction::select('created_at', 'currency_id')
			->whereBetween('created_at', [$start, $end])
			->with(['transactional' => function (MorphTo $morphTo) {
				$morphTo->morphWith([
					Transfer::class => ['sender', 'receiver', 'currency'],
					RequestMoney::class => ['sender', 'receiver', 'currency'],
					RedeemCode::class => ['sender', 'receiver', 'currency'],
					Escrow::class => ['sender', 'receiver', 'currency'],
					Voucher::class => ['sender', 'receiver', 'currency'],
					Invoice::class => ['sender', 'currency'],
					Fund::class => ['sender', 'receiver', 'currency'],
					Exchange::class => ['user', 'currency'],
					CommissionEntry::class => ['sender', 'receiver', 'currency'],
					QRCode::class => ['user', 'currency'],
				]);
			}])
			->whereHasMorph('transactional',
				[
					Transfer::class,
					RequestMoney::class,
					RedeemCode::class,
					Escrow::class,
					Voucher::class,
					Invoice::class,
					Fund::class,
					Exchange::class,
					CommissionEntry::class,
					QRCode::class,
				], function ($query, $type) use ($user) {
					if ($type === CommissionEntry::class) {
						$query->where(function ($query) use ($user) {
							$query->where('from_user', '=', $user->id);
							$query->orWhere('to_user', '=', $user->id);
						});
					}
					if ($type === Transfer::class) {
						$query->where(function ($query) use ($user) {
							$query->where('sender_id', '=', $user->id);
							$query->orWhere('receiver_id', '=', $user->id);
						});
					}
					if ($type === RequestMoney::class) {
						$query->where(function ($query) use ($user) {
							$query->where('sender_id', '=', $user->id);
							$query->orWhere('receiver_id', '=', $user->id);
						});
					}
					if ($type === RedeemCode::class) {
						$query->where(function ($query) use ($user) {
							$query->where('sender_id', '=', $user->id);
							$query->orWhere('receiver_id', '=', $user->id);
						});
					}
					if ($type === Escrow::class) {
						$query->where(function ($query) use ($user) {
							$query->where('sender_id', '=', $user->id);
							$query->orWhere('receiver_id', '=', $user->id);
						});
					}
					if ($type === Voucher::class) {
						$query->where(function ($query) use ($user) {
							$query->where('sender_id', '=', $user->id);
							$query->orWhere('receiver_id', '=', $user->id);
						});
					}
					if ($type === Invoice::class) {
						$query->where(function ($query) use ($user) {
							$query->where('sender_id', '=', $user->id);
						});
					}
					if ($type === Fund::class) {
						$query->where('user_id', $user->id);
					}
					if ($type === Exchange::class || $type === QRCode::class) {
						$query->where('user_id', $user->id);
					}
				})
			->groupBy([DB::raw("DATE_FORMAT(created_at, '%j')"), 'currency_id'])
			->selectRaw("SUM(CASE WHEN transactional_type like '%Deposit' THEN amount ELSE 0 END) as Deposit")
			->selectRaw("SUM(CASE WHEN transactional_type like '%Fund' THEN amount ELSE 0 END) as Fund")
			->selectRaw("SUM(CASE WHEN transactional_type like '%Transfer' THEN amount ELSE 0 END) as Transfer")
			->selectRaw("SUM(CASE WHEN transactional_type like '%RequestMoney' THEN amount ELSE 0 END) as RequestMoney")
			->selectRaw("SUM(CASE WHEN transactional_type like '%Voucher' THEN amount ELSE 0 END) as Voucher")
			->selectRaw("SUM(CASE WHEN transactional_type like '%Invoice' THEN grand_total ELSE 0 END) as Invoice")
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
		$start = new DateTime($start);
		$end = new DateTime($end);

		for ($day = $start; $day <= $end; $day->modify('+1 day')) {
			$i = $day->format('j');
			$labels[] = $day->format('jS M');
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
						$currentQRPaymentAmount = $transactions[$i][$key][0]->QRPaymentAmount / $currency->exchange_rate;
						$currentRedeem += $transactions[$i][$key][0]->Redeem / $currency->exchange_rate;
						$currentEscrow += $transactions[$i][$key][0]->Escrow / $currency->exchange_rate;
						$currentPayout += $transactions[$i][$key][0]->Payout / $currency->exchange_rate;
						$currentExchange += $transactions[$i][$key][0]->Exchange / $currency->exchange_rate;
						$currentDispute += $transactions[$i][$key][0]->Dispute / $currency->exchange_rate;
						$currentCommissionEntry += $transactions[$i][$key][0]->CommissionEntry / $currency->exchange_rate;
					}
				}
			}
			$dataDeposit[] = round($currentDeposit, basicControl()->fraction_number);
			$dataFund[] = round($currentFund, basicControl()->fraction_number);
			$dataTransfer[] = round($currentTransfer, basicControl()->fraction_number);
			$dataRequestMoney[] = round($currentRequestMoney, basicControl()->fraction_number);
			$dataVoucher[] = round($currentVoucher, basicControl()->fraction_number);
			$dataInvoice[] = round($currentInvoice, basicControl()->fraction_number);
			$dataQRPaymentAmount[] = round($currentQRPaymentAmount, basicControl()->fraction_number);
			$dataRedeem[] = round($currentRedeem, basicControl()->fraction_number);
			$dataEscrow[] = round($currentEscrow, basicControl()->fraction_number);
			$dataPayout[] = round($currentPayout, basicControl()->fraction_number);
			$dataExchange[] = round($currentExchange, basicControl()->fraction_number);
			$dataDispute[] = round($currentDispute, basicControl()->fraction_number);
			$dataCommissionEntry[] = round($currentCommissionEntry, basicControl()->fraction_number);
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

		return response()->json($data);
	}

	public function index()
	{
		$basic = basicControl();
		$fraction = optional($basic->currency)->currency_type == 1 ? 2 : 8;
		$user = Auth::user();
		$last30 = date('Y-m-d', strtotime('-30 days'));
		$last7 = date('Y-m-d', strtotime('-7 days'));
		$today = today();
		$dayCount = date('t', strtotime($today));

		$data['stores'] = Store::own()->get();
		$data['totalStores'] = count($data['stores']);
		$storeId = array();
		foreach ($data['stores'] as $store) {
			$storeId[] = $store->id;
		}
		$data['totalProducts'] = StoreProduct::own()->count();
		$data['totalOrders'] = ProductOrder::whereIn('store_id', $storeId)->count();
		$data['totalShippings'] = StoreShipping::own()->count();

		$data['wallets'] = Wallet::with('currency:id,name,code,logo,symbol')
			->select('id', 'balance as totalBalance', 'user_id', 'currency_id')
			->where('user_id', $user->id)
			->get()->toArray();

		$transfer = Transfer::with('currency:id,name,code,logo,symbol,exchange_rate')
			->where(['status' => 1, 'sender_id' => $user->id])
			->selectRaw("SUM((CASE WHEN YEAR(created_at) = YEAR(CURDATE()) THEN amount END)) as transfer_1_year, currency_id")
			->selectRaw("SUM((CASE WHEN created_at >= $last30 THEN amount END)) as transfer_30_days, currency_id")
			->selectRaw("SUM((CASE WHEN created_at >= $last7 THEN amount END)) as transfer_7_days, currency_id")
			->selectRaw("SUM((CASE WHEN created_at = CURDATE() THEN amount END)) as transfer_today, currency_id")
			->get()
			->map(function ($item) {
				return [
					'transfer_1_year' => ($item->transfer_1_year) ? $item->transfer_1_year / optional($item->currency)->exchange_rate : 0,
					'transfer_30_days' => ($item->transfer_30_days) ? $item->transfer_30_days / optional($item->currency)->exchange_rate : 0,
					'transfer_7_days' => ($item->transfer_7_days) ? $item->transfer_7_days / optional($item->currency)->exchange_rate : 0,
					'transfer_today' => ($item->transfer_today) ? $item->transfer_today / optional($item->currency)->exchange_rate : 0,
				];
			});

		$data['transfer'] = [
			'transfer_1_year' => $transfer->sum('transfer_1_year'),
			'transfer_30_days' => $transfer->sum('transfer_30_days'),
			'transfer_7_days' => $transfer->sum('transfer_7_days'),
			'transfer_today' => $transfer->sum('transfer_today')
		];

		$requestMoney = RequestMoney::where(['status' => 1, 'sender_id' => $user->id])
			->with('currency:id,name,code,logo,symbol,exchange_rate')
			->selectRaw("SUM((CASE WHEN YEAR(created_at) = YEAR(CURDATE()) THEN amount END)) as request_money_1_year, currency_id")
			->selectRaw("SUM((CASE WHEN created_at >= $last30 THEN amount END)) as request_money_30_days, currency_id")
			->selectRaw("SUM((CASE WHEN created_at >= $last7 THEN amount END)) as request_money_7_days, currency_id")
			->selectRaw("SUM((CASE WHEN created_at = CURDATE() THEN amount END)) as request_money_today, currency_id")
			->get()
			->map(function ($item) {
				return [
					'request_money_1_year' => ($item->request_money_1_year) ? $item->request_money_1_year / optional($item->currency)->exchange_rate : 0,
					'request_money_30_days' => ($item->request_money_30_days) ? $item->request_money_30_days / optional($item->currency)->exchange_rate : 0,
					'request_money_7_days' => ($item->request_money_7_days) ? $item->request_money_7_days / optional($item->currency)->exchange_rate : 0,
					'request_money_today' => ($item->request_money_today) ? $item->request_money_today / optional($item->currency)->exchange_rate : 0
				];
			});
		$data['requestMoney'] = [
			'request_money_1_year' => $requestMoney->sum('request_money_1_year'),
			'request_money_30_days' => $requestMoney->sum('request_money_30_days'),
			'request_money_7_days' => $requestMoney->sum('request_money_7_days'),
			'request_money_today' => $requestMoney->sum('request_money_today')
		];

		$voucher = Voucher::where(['status' => 1, 'sender_id' => $user->id])
			->with('currency:id,name,code,logo,symbol,exchange_rate')
			->selectRaw("SUM((CASE WHEN YEAR(created_at) = YEAR(CURDATE()) THEN amount END)) as voucher_1_year, currency_id")
			->selectRaw("SUM((CASE WHEN created_at >= $last30 THEN amount END)) as voucher_30_days, currency_id")
			->selectRaw("SUM((CASE WHEN created_at >= $last7 THEN amount END)) as voucher_7_days, currency_id")
			->selectRaw("SUM((CASE WHEN created_at = CURDATE() THEN amount END)) as voucher_today, currency_id")
			->get()
			->map(function ($item) {
				return [
					'voucher_1_year' => ($item->voucher_1_year) ? $item->voucher_1_year / optional($item->currency)->exchange_rate : 0,
					'voucher_30_days' => ($item->voucher_30_days) ? $item->voucher_30_days / optional($item->currency)->exchange_rate : 0,
					'voucher_7_days' => ($item->voucher_7_days) ? $item->voucher_7_days / optional($item->currency)->exchange_rate : 0,
					'voucher_today' => ($item->voucher_today) ? $item->voucher_today / optional($item->currency)->exchange_rate : 0
				];
			});
		$data['voucher'] = [
			'voucher_1_year' => $voucher->sum('voucher_1_year'),
			'voucher_30_days' => $voucher->sum('voucher_30_days'),
			'voucher_7_days' => $voucher->sum('voucher_7_days'),
			'voucher_today' => $voucher->sum('voucher_today')
		];

		$invoice = Invoice::where(['status' => 'paid', 'sender_id' => $user->id])
			->with('currency:id,name,code,logo,symbol,exchange_rate')
			->selectRaw("SUM((CASE WHEN YEAR(created_at) = YEAR(CURDATE()) THEN grand_total END)) as invoice_1_year, currency_id")
			->selectRaw("SUM((CASE WHEN created_at >= $last30 THEN grand_total END)) as invoice_30_days, currency_id")
			->selectRaw("SUM((CASE WHEN created_at >= $last7 THEN grand_total END)) as invoice_7_days, currency_id")
			->selectRaw("SUM((CASE WHEN created_at = CURDATE() THEN grand_total END)) as invoice_today, currency_id")
			->get()
			->map(function ($item) {
				return [
					'invoice_1_year' => ($item->invoice_1_year) ? $item->invoice_1_year / optional($item->currency)->exchange_rate : 0,
					'invoice_30_days' => ($item->invoice_30_days) ? $item->invoice_30_days / optional($item->currency)->exchange_rate : 0,
					'invoice_7_days' => ($item->invoice_7_days) ? $item->invoice_7_days / optional($item->currency)->exchange_rate : 0,
					'invoice_today' => ($item->invoice_today) ? $item->invoice_today / optional($item->currency)->exchange_rate : 0
				];
			});
		$data['invoice'] = [
			'invoice_1_year' => $invoice->sum('invoice_1_year'),
			'invoice_30_days' => $invoice->sum('invoice_30_days'),
			'invoice_7_days' => $invoice->sum('invoice_7_days'),
			'invoice_today' => $invoice->sum('invoice_today')
		];

		$transactions = Transaction::select('created_at', 'currency_id')
			->whereMonth('created_at', $today)
			->with(['transactional' => function (MorphTo $morphTo) {
				$morphTo->morphWith([
					Transfer::class => ['sender', 'receiver', 'currency'],
					RequestMoney::class => ['sender', 'receiver', 'currency'],
					RedeemCode::class => ['sender', 'receiver', 'currency'],
					Escrow::class => ['sender', 'receiver', 'currency'],
					Voucher::class => ['sender', 'receiver', 'currency'],
					Invoice::class => ['sender', 'currency'],
					Fund::class => ['sender', 'receiver', 'currency'],
					Exchange::class => ['user', 'currency'],
					CommissionEntry::class => ['sender', 'receiver', 'currency'],
					QRCode::class => ['user', 'currency'],
					ApiOrder::class => ['user', 'currency'],
				]);
			}])
			->whereHasMorph('transactional',
				[
					Transfer::class,
					RequestMoney::class,
					RedeemCode::class,
					Escrow::class,
					Voucher::class,
					Invoice::class,
					Fund::class,
					Exchange::class,
					CommissionEntry::class,
					QRCode::class,
					ApiOrder::class,
				], function ($query, $type) use ($user) {
					if ($type === CommissionEntry::class) {
						$query->where(function ($query) use ($user) {
							$query->where('from_user', '=', $user->id);
							$query->orWhere('to_user', '=', $user->id);
						});
					}
					if ($type === Transfer::class) {
						$query->where(function ($query) use ($user) {
							$query->where('sender_id', '=', $user->id);
							$query->orWhere('receiver_id', '=', $user->id);
						});
					}
					if ($type === RequestMoney::class) {
						$query->where(function ($query) use ($user) {
							$query->where('sender_id', '=', $user->id);
							$query->orWhere('receiver_id', '=', $user->id);
						});
					}
					if ($type === RedeemCode::class) {
						$query->where(function ($query) use ($user) {
							$query->where('sender_id', '=', $user->id);
							$query->orWhere('receiver_id', '=', $user->id);
						});
					}
					if ($type === Escrow::class) {
						$query->where(function ($query) use ($user) {
							$query->where('sender_id', '=', $user->id);
							$query->orWhere('receiver_id', '=', $user->id);
						});
					}
					if ($type === Voucher::class) {
						$query->where(function ($query) use ($user) {
							$query->where('sender_id', '=', $user->id);
							$query->orWhere('receiver_id', '=', $user->id);
						});
					}
					if ($type === Invoice::class) {
						$query->where(function ($query) use ($user) {
							$query->where('sender_id', '=', $user->id);
						});
					}
					if ($type === Fund::class || $type === ApiOrder::class) {
						$query->where('user_id', $user->id);
					}
					if ($type === Exchange::class || $type === QRCode::class) {
						$query->where('user_id', $user->id);
					}
				})
			->groupBy([DB::raw("DATE_FORMAT(created_at, '%j')"), 'currency_id'])
			->selectRaw("SUM(CASE WHEN transactional_type like '%Deposit' THEN amount ELSE 0 END) as Deposit")
			->selectRaw("SUM(CASE WHEN transactional_type like '%Fund' THEN amount ELSE 0 END) as Fund")
			->selectRaw("SUM(CASE WHEN transactional_type like '%Transfer' THEN amount ELSE 0 END) as Transfer")
			->selectRaw("SUM(CASE WHEN transactional_type like '%RequestMoney' THEN amount ELSE 0 END) as RequestMoney")
			->selectRaw("SUM(CASE WHEN transactional_type like '%Voucher' THEN amount ELSE 0 END) as Voucher")
			->selectRaw("SUM(CASE WHEN transactional_type like '%Invoice' THEN amount ELSE 0 END) as Invoice")
			->selectRaw("SUM(CASE WHEN transactional_type like '%QRCode' THEN amount ELSE 0 END) as QRPaymentAmount")
			->selectRaw("SUM(CASE WHEN transactional_type like '%ApiOrder' THEN amount ELSE 0 END) as ApiOrder")
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
		$dataApiOrder = [];
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
			$currentApiOrder = 0;
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
						$currentApiOrder += $transactions[$i][$key][0]->ApiOrder / $currency->exchange_rate;
						$currentRedeem += $transactions[$i][$key][0]->Redeem / $currency->exchange_rate;
						$currentEscrow += $transactions[$i][$key][0]->Escrow / $currency->exchange_rate;
						$currentPayout += $transactions[$i][$key][0]->Payout / $currency->exchange_rate;
						$currentExchange += $transactions[$i][$key][0]->Exchange / $currency->exchange_rate;
						$currentDispute += $transactions[$i][$key][0]->Dispute / $currency->exchange_rate;
						$currentCommissionEntry += $transactions[$i][$key][0]->CommissionEntry / $currency->exchange_rate;
					}
				}
			}
			$dataDeposit[] = round($currentDeposit, $fraction);
			$dataFund[] = round($currentFund, $fraction);
			$dataTransfer[] = round($currentTransfer, $fraction);
			$dataRequestMoney[] = round($currentRequestMoney, $fraction);
			$dataVoucher[] = round($currentVoucher, $fraction);
			$dataInvoice[] = round($currentInvoice, $fraction);
			$dataQRPaymentAmount[] = round($currentQRPaymentAmount, $fraction);
			$dataApiOrder[] = round($currentApiOrder, $fraction);
			$dataRedeem[] = round($currentRedeem, $fraction);
			$dataEscrow[] = round($currentEscrow, $fraction);
			$dataPayout[] = round($currentPayout, $fraction);
			$dataExchange[] = round($currentExchange, $fraction);
			$dataDispute[] = round($currentDispute, $fraction);
			$dataCommissionEntry[] = round($currentCommissionEntry, $fraction);
		}

		$data['basic'] = $basic;
		$data['labels'] = $labels;
		$data['dataDeposit'] = $dataDeposit;
		$data['dataFund'] = $dataFund;
		$data['dataTransfer'] = $dataTransfer;
		$data['dataRequestMoney'] = $dataRequestMoney;
		$data['dataVoucher'] = $dataVoucher;
		$data['dataInvoice'] = $dataInvoice;
		$data['dataQRPaymentAmount'] = $dataQRPaymentAmount;
		$data['dataApiOrder'] = $dataApiOrder;
		$data['dataRedeem'] = $dataRedeem;
		$data['dataEscrow'] = $dataEscrow;
		$data['dataPayout'] = $dataPayout;
		$data['dataExchange'] = $dataExchange;
		$data['dataDispute'] = $dataDispute;
		$data['dataCommissionEntry'] = $dataCommissionEntry;

		$data['kyc'] = Kyc::first();
		$firebaseNotify = FirebaseNotify::first();
		return view('user.home', $data, compact('firebaseNotify'));
	}


	public function home()
	{
		$templates = Template::get()->groupBy('section_name');
		$contentDetails = ContentDetails::with('content', 'content.contentMedia')->get()->groupBy('content.name');
		$gateways = Gateway::where('status', 1)->orderBy('sort_by', 'ASC')->get()->pluck('image');
		return view('frontend.index', compact('templates', 'contentDetails', 'gateways'));
	}

	public function about()
	{
		$templates = Template::get()->groupBy('section_name');
		$contentDetails = ContentDetails::with('content', 'content.contentMedia')->get()->groupBy('content.name');
		$gateways = Gateway::where('status', 1)->orderBy('sort_by', 'ASC')->get()->pluck('image');
		return view('frontend.about', compact('templates', 'contentDetails', 'gateways'));
	}

	public function faq()
	{
		$templates = Template::get()->groupBy('section_name');
		$contentDetails = ContentDetails::with('content', 'content.contentMedia')->get()->groupBy('content.name');
		return view('frontend.faq', compact('templates', 'contentDetails'));
	}

	public function blog()
	{
		$contentDetails = ContentDetails::with(['content', 'content.contentMedia'])
			->whereHas('content', function ($query) {
				$query->where('name', 'blog');
			})->orderByDesc('content_id')->get();
		return view('frontend.blog', compact('contentDetails'));
	}

	public function blogDetails($id)
	{
		$blogDetail = ContentDetails::findOrFail($id);
		$latestBlogs = ContentDetails::with(['content', 'content.contentMedia'])
			->whereHas('content', function ($query) {
				$query->where('name', 'blog');
			})->orderByDesc('content_id')->get()->take(4);

		return view('frontend.blog-details', compact('blogDetail', 'latestBlogs'));
	}

	public function contact(Request $request)
	{
		if ($request->isMethod('get')) {
			$contact = Template::where('section_name', 'contact')->get();
			$contentDetails = ContentDetails::with(['content', 'content.contentMedia'])
				->whereHas('content', function ($query) {
					$query->where('name', 'contact');
				})->orderByDesc('content_id')->get()->take(3);
			return view('frontend.contact', compact('contact', 'contentDetails'));

		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());
			$validationRules = [
				'name' => 'required|min:4|max:50',
				'email' => 'required|email|min:8|max:50',
				'subject' => 'required|min:4|max:100',
				'message' => 'required|min:10|max:1000',
			];
			$validate = Validator::make($purifiedData, $validationRules);
			if ($validate->fails()) {
				return back()->withErrors($validate)->withInput();
			}
			$purifiedData = (object)$purifiedData;

			$basic = basicControl();
			$basicEmail = $basic->sender_email;
			$name = $purifiedData->name;
			$email_from = $purifiedData->email;
			$requestMessage = $purifiedData->message;
			$subject = $purifiedData->subject;


			$message = $requestMessage . "\n $name";
			$from = $email_from;

			$headers = "From: <$from> \r\n";
			$headers .= "Reply-To: <$from> \r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

			$to = $basicEmail;

			if (@mail($to, $subject, $message, $headers)) {
				// echo 'Your message has been sent.';
			} else {
				//echo 'There was a problem sending the email.';
			}

			return back()->with('success', 'Message sent successfully');
		}
	}

	public function subscribe(Request $request)
	{
		$purifiedData = Purify::clean($request->all());
		$validationRules = [
			'email' => 'required|email|min:8|max:100|unique:subscribes',
		];
		$validate = Validator::make($purifiedData, $validationRules);
		if ($validate->fails()) {
			return back()->withErrors($validate)->withInput();
		}
		$purifiedData = (object)$purifiedData;

		$subscribe = new Subscribe();
		$subscribe->email = $purifiedData->email;
		$subscribe->save();

		return back()->with('success', 'Subscribed successfully');
	}

	public function logoUpdate(Request $request)
	{
		if ($request->isMethod('get')) {
			return view('admin.control_panel.logo');
		} elseif ($request->isMethod('post')) {

			if ($request->hasFile('logo')) {
				try {
					$old = 'logo.png';
					$this->uploadImage($request->logo, config('location.logo.path'), null, $old, $old);
				} catch (\Exception $exp) {
					return back()->with('error', 'Logo could not be uploaded.');
				}
			}
			if ($request->hasFile('footer_logo')) {
				try {
					$old = 'footer-logo.png';
					$this->uploadImage($request->footer_logo, config('location.logo.path'), null, $old, $old);
				} catch (\Exception $exp) {
					return back()->with('error', 'Footer Logo could not be uploaded.');
				}
			}
			if ($request->hasFile('admin_logo')) {
				try {
					$old = 'admin-logo.png';
					$this->uploadImage($request->admin_logo, config('location.logo.path'), null, $old, $old);
				} catch (\Exception $exp) {
					return back()->with('error', 'Logo could not be uploaded.');
				}
			}
			if ($request->hasFile('favicon')) {
				try {
					$old = 'favicon.png';
					$this->uploadImage($request->favicon, config('location.logo.path'), null, $old, $old);
				} catch (\Exception $exp) {
					return back()->with('error', 'Favicon could not be uploaded.');
				}
			}
			if ($request->hasFile('breadcrumb')) {
				try {
					$old = 'breadcrumb.png';
					$this->uploadImage($request->breadcrumb, config('location.breadcrumb.path'), config('location.breadcrumb.size'), $old, $old);
				} catch (\Exception $exp) {
					return back()->with('error', 'Breadcrumb could not be uploaded.');
				}
			}
			return back()->with('success', 'Logo, favicon and breadcrumb has been updated.');
		}
	}


	public function seoUpdate(Request $request)
	{
		$basicControl = basicControl();
		if ($request->isMethod('get')) {
			return view('admin.control_panel.seo', compact('basicControl'));
		} elseif ($request->isMethod('post')) {

			$purifiedData = Purify::clean($request->all());
			$purifiedData['image'] = $request->image;
			$validator = Validator::make($purifiedData, [
				'meta_keywords' => 'nullable|string|min:1',
				'meta_description' => 'nullable|string|min:1',
				'social_title' => 'nullable|string|min:1',
				'social_description' => 'nullable|string|min:1',
				'image' => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
			]);

			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}

			$purifiedData = (object)$purifiedData;
			$basicControl->meta_keywords = $purifiedData->meta_keywords;
			$basicControl->meta_description = $purifiedData->meta_description;
			$basicControl->social_title = $purifiedData->social_title;
			$basicControl->social_description = $purifiedData->social_description;
			$basicControl->save();

			if ($request->hasFile('image')) {
				try {
					$old = 'meta.png';
					$this->uploadImage($request->image, config('location.logo.path'), null, $old, $old);
				} catch (\Exception $exp) {
					return back()->with('error', 'Meta image could not be uploaded.');
				}
			}
			return back()->with('success', 'Seo has been updated.');
		}
	}

	public function getLink($id, $getLink = null)
	{
		$getData = Content::findOrFail($id);

		$contentSection = [$getData->name];
		$contentDetail = ContentDetails::select('id', 'content_id', 'description', 'created_at')
			->where('content_id', $getData->id)
			->whereHas('content', function ($query) use ($contentSection) {
				return $query->whereIn('name', $contentSection);
			})
			->with(['content:id,name',
				'content.contentMedia' => function ($q) {
					$q->select(['content_id', 'description']);
				}])
			->get()->groupBy('content.name');

		$title = @$contentDetail[$getData->name][0]->description->title;
		$description = @$contentDetail[$getData->name][0]->description->description;
		return view('frontend.getLink', compact('contentDetail', 'title', 'description'));
	}

	public function getTemplate($template = null)
	{
		$contentDetail = Template::where('section_name', $template)->firstOrFail();
		$title = @$contentDetail->description->title;
		$description = @$contentDetail->description->description;
		return view('frontend.getLink', compact('contentDetail', 'title', 'description'));
	}

	public function setLanguage($code)
	{
		$language = Language::where('short_name', $code)->first();
		if (!$language) $code = 'US';
		session()->put('lang', $code);
		session()->put('rtl', $language ? $language->rtl : 0);
		return back();
	}

	public function kycShow()
	{
		$data['kyc'] = Kyc::firstOrFail();
		return view('user.kyc.form', $data);
	}

	public function kycStore(Request $request)
	{
		$kyc = Kyc::firstOrFail();
		$params = $kyc->input_form;
		$userKyc = new UserKyc();

		$rules = [];
		$inputField = [];

		$verifyImages = [];

		if ($params != null) {
			foreach ($params as $key => $cus) {
				$rules[$key] = [$cus->validation];
				if ($cus->type == 'file') {
					array_push($rules[$key], 'image');
					array_push($rules[$key], 'mimes:jpeg,jpg,png');
					array_push($rules[$key], 'max:2048');
					array_push($verifyImages, $key);
				}
				if ($cus->type == 'text') {
					array_push($rules[$key], 'max:191');
				}
				if ($cus->type == 'textarea') {
					array_push($rules[$key], 'max:300');
				}
				$inputField[] = $key;
			}
		}
		$this->validate($request, $rules);

		$reqField = [];
		$path = config('location.kyc.path');
		$collection = collect($request);

		if ($params != null) {
			foreach ($collection as $k => $v) {
				foreach ($params as $inKey => $inVal) {
					if ($k != $inKey) {
						continue;
					} else {
						if ($inVal->type == 'file') {
							if ($request->hasFile($inKey)) {
								try {
									$reqField[$inKey] = [
										'field_name' => $inVal->field_level,
										'field_value' => $this->uploadImage($request[$inKey], $path),
										'type' => $inVal->type,
									];
								} catch (\Exception $exp) {
									session()->flash('error', 'Could not upload your ' . $inKey);
									return back()->withInput();
								}
							}
						} else {
							$reqField[$inKey] = [
								'field_name' => $inVal->field_level,
								'field_value' => $v,
								'type' => $inVal->type,
							];
						}
					}
				}
			}
			$userKyc->kyc_info = $reqField;
		} else {
			$userKyc->kyc_info = null;
		}
		$user = Auth::user();

		$userKyc->user_id = $user->id;
		$userKyc->save();

		$user->kyc_verified = 1;
		$user->save();

		return redirect()->route('user.dashboard')->with('success', 'KYC Submitted');
	}

	public function kycList()
	{
		$data['kycLists'] = UserKyc::where('user_id', Auth::id())->orderBy('id', 'desc')->paginate(config('basic.paginate'));
		return view('user.kyc.index', $data);
	}

	public function kycView($id)
	{
		$data['kyc'] = UserKyc::with('user')->where('user_id', Auth::id())->findOrFail($id);
		return view('user.kyc.view', $data);
	}

	public function qrCode()
	{
		return view('user.qrCode.qrCode');
	}

	public function qrPaymentList(Request $request)
	{
		$search = $request->all();
		$dateSearch = $request->datetrx;
		$date = preg_match("/^[0-9]{2,4}\-[0-9]{1,2}\-[0-9]{1,2}$/", $dateSearch);

		$data['gateways'] = Gateway::where('status', 1)->get();
		$data['qrPayments'] = QRCode::where('user_id', auth()->id())->where('status', 1)
			->when(isset($search['email']), function ($query) use ($search) {
				return $query->where("email", $search['email']);
			})
			->when(isset($search['gateway']), function ($query) use ($search) {
				return $query->where("gateway_id", $search['gateway']);
			})
			->when($date == 1, function ($query) use ($dateSearch) {
				return $query->whereDate("created_at", $dateSearch);
			})
			->orderBy('id', 'desc')->paginate(config('basic.paginate'));
		return view('user.qrCode.index', $data);
	}

	public function saveToken(Request $request)
	{
		auth()->user()->update(['fcm_token' => $request->token]);
		return response()->json(['token saved successfully.']);
	}
}
