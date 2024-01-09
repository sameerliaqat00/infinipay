<?php

namespace App\Http\Controllers;

use App\Models\ChargesLimit;
use App\Models\Deposit;
use App\Models\Gateway;
use App\Models\QRCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Stevebauman\Purify\Facades\Purify;

class QrCodePaymentController extends Controller
{
	public function qrPayment(Request $request, $link)
	{
		if (basicControl()->qr_payment == 0) {
			return redirect()->route('home');
		}
		$user = User::with(['qrCurrency'])->where('qr_link', $link)->firstOrFail();
		if ((empty($user->qrCurrency))) {
			return redirect()->route('home');
		}

		if ($request->isMethod('get')) {
			$methods = Gateway::orderBy('sort_by', 'ASC')->get();
			return view('user.qrCode.payment', compact('methods', 'user'));
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());

			$validationRules = [
				'amount' => 'required|numeric|min:1|not_in:0',
				'methodId' => 'required|integer|min:1|not_in:0',
				'email' => 'required'
			];

			$validate = Validator::make($purifiedData, $validationRules);
			if ($validate->fails()) {
				return back()->withErrors($validate)->withInput();
			}

			$purifiedData = (object)$purifiedData;
			$amount = $purifiedData->amount;
			$currency_id = $user->qr_currency_id;
			$methodId = $purifiedData->methodId;

			$checkAmountValidate = $this->checkAmountValidate($amount, $currency_id, config('transactionType.deposit'), $methodId);//7 = deposit

			if (!$checkAmountValidate['status']) {
				return back()->withInput()->with('alert', $checkAmountValidate['message']);
			}

			$method = Gateway::findOrFail($methodId);

			$qrCode = new QRCode();
			$qrCode->user_id = $user->id;
			$qrCode->gateway_id = $methodId;
			$qrCode->currency_id = $currency_id;
			$qrCode->email = $purifiedData->email;
			$qrCode->charge = $checkAmountValidate['charge'];
			$qrCode->amount = $amount;
			$qrCode->status = 0;
			$qrCode->save();

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
			$deposit->email = $qrCode->email;
			$deposit->payment_method_currency = $method->currency;
			$deposit->depositable_id = $qrCode->id;
			$deposit->depositable_type = QRCode::class;
			$deposit->save();

			$checkAmountValidate = (new DepositController())->checkAmountValidate($deposit->amount, $deposit->currency_id, config('transactionType.deposit'), $deposit->payment_method_id);
			if (!$checkAmountValidate['status']) {
				return back()->withInput()->with('alert', $checkAmountValidate['message']);
			}

			return redirect(route('payment.process', $deposit->utr));
		}
	}

	public function checkAmountValidate($amount, $currency_id, $transaction_type_id, $methodId)
	{
		$chargesLimit = ChargesLimit::where(['currency_id' => $currency_id, 'transaction_type_id' => $transaction_type_id, 'payment_method_id' => $methodId, 'is_active' => 1])->firstOrFail();

		$balance = 0;

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

		$payable_amount = $amount + $charge;

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
}
