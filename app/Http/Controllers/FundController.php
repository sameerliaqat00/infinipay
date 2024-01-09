<?php

namespace App\Http\Controllers;

use App\Models\ChargesLimit;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\Fund;
use App\Models\Gateway;
use App\Models\Template;
use App\Models\VirtualCardMethod;
use App\Models\VirtualCardOrder;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Stevebauman\Purify\Facades\Purify;

class FundController extends Controller
{
	public function index()
	{
		$userId = Auth::id();
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$funds = Fund::with(['sender', 'receiver', 'currency'])
			->where('user_id', $userId)
			->latest()->paginate();
		return view('user.fund.index', compact('funds', 'currencies'));
	}

	public function search(Request $request)
	{
		$filterData = $this->_filter($request);
		$search = $filterData['search'];
		$currencies = $filterData['currencies'];
		$userId = $filterData['userId'];
		$funds = $filterData['funds']
			->where('user_id', $userId)
			->latest()
			->paginate();
		$funds->appends($filterData['search']);
		return view('user.fund.index', compact('search', 'funds', 'currencies'));
	}

	public function _filter($request)
	{
		$userId = Auth::id();
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$search = $request->all();
		$created_date = isset($search['created_at']) ? preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']) : 0;

		$funds = Fund::with('sender', 'receiver', 'currency')
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
			->when($created_date == 1, function ($query) use ($search) {
				return $query->whereDate("created_at", $search['created_at']);
			});

		$data = [
			'userId' => $userId,
			'currencies' => $currencies,
			'search' => $search,
			'funds' => $funds,
		];
		return $data;
	}

	public function initialize(Request $request, $from = null, $id = null)
	{
		$order = null;
		if ($from == 'card') {
			$order = VirtualCardOrder::with(['cardMethod'])->find($id);
		}
		if ($request->isMethod('get')) {
			$methods = Gateway::orderBy('sort_by', 'ASC')->where('status', 1)->get();
			$currencies = Currency::select('id', 'code', 'name', 'currency_type')->where('is_active', 1)->get();
			$template = Template::where('section_name', 'add-fund')->first();
			return view('user.fund.create', compact('methods', 'currencies', 'template', 'order', 'from', 'id'));
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

			$cardOrderId = VirtualCardOrder::select(['id', 'user_id'])->where('id', $request->orderCardId)->first();
			if ($cardOrderId) {
				if ($cardOrderId->user_id != auth()->id()) {
					return back()->with('alert', 'You are not eligible for this transaction');
				}
				$info = cardCurrencyCheck($cardOrderId->id);
				if ($info['status'] == 'success') {
					$curencyCode = Currency::select(['code'])->findOrFail($purifiedData->currency);

					if ($info['currencyCode'] != $curencyCode->code) {
						return back()->with('alert', 'Please select your card currency for transaction');
					}

					if ($info['MinimumAmount'] >= $purifiedData->amount) {
						return back()->with('alert', 'Amount must be greater than ' . $info['MinimumAmount']);
					}
					if ($info['MaximumAmount'] <= $purifiedData->amount) {
						return back()->with('alert', 'Amount must be smaller than ' . $info['MaximumAmount']);
					}

					$percentValue = ($info['PercentCharge'] * $purifiedData->amount) / 100;
					$charge = $info['FixedCharge'] + $percentValue;

					if ($purifiedData->amount < $charge) {
						return back()->with('alert', 'Charge Cannot be greater than input amount');
					}
				}
			}

			$amount = $purifiedData->amount;
			$currency_id = $purifiedData->currency;
			$methodId = $purifiedData->methodId;

			$checkAmountValidate = $this->checkAmountValidate($amount, $currency_id, config('transactionType.deposit'), $methodId);//7 = deposit

			if (!$checkAmountValidate['status']) {
				return back()->withInput()->with('alert', $checkAmountValidate['message']);
			}

			$method = Gateway::where('status', 1)->findOrFail($methodId);
			$user = Auth::user();
			$deposit = new Deposit();
			$deposit->user_id = $user->id;
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
			$deposit->email = $user->email;
			$deposit->card_order_id = $cardOrderId->id ?? null;
			$deposit->payment_method_currency = $method->currency;
			$deposit->depositable_type = Fund::class;
			$deposit->save();

			return redirect(route('deposit.confirm', $deposit->utr));
		}
	}

	public function checkAmountValidate($amount, $currency_id, $transaction_type_id, $methodId)
	{
		$chargesLimit = ChargesLimit::where(['currency_id' => $currency_id, 'transaction_type_id' => $transaction_type_id, 'payment_method_id' => $methodId, 'is_active' => 1])->first();
		$wallet = Wallet::firstOrCreate(['user_id' => Auth::id(), 'currency_id' => $currency_id]);

		$balance = $wallet->balance;
		$status = false;
		$charge = 0;
		$min_limit = 0;
		$max_limit = 0;
		$fixed_charge = 0;
		$percentage = 0;
		$percentage_charge = 0;

		if ($chargesLimit) {
			$percentage = $chargesLimit->percentage_charge;
			$percentage_charge = ($amount * $percentage) / 100;
			$fixed_charge = $chargesLimit->fixed_charge;
			$min_limit = $chargesLimit->min_limit;
			$max_limit = $chargesLimit->max_limit;
			$charge = $percentage_charge + $fixed_charge;
		}
		//Total amount with all fixed and percent charge for deduct

		$payable_amount = $amount + $charge;

		$new_balance = $balance + $amount;

		//Currency inactive
		if ($wallet->is_active != 1) {
			$message = 'Currency not available for this transaction';
		} elseif ($min_limit == 0 && $max_limit == 0) {
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
}
