<?php

namespace App\Http\Controllers;

use App\Models\ChargesLimit;
use App\Models\Deposit;
use App\Models\TwoFactorSetting;
use App\Models\Voucher;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class DepositController extends Controller
{
    public function checkAmount(Request $request)
    {
        if ($request->ajax()) {
            $amount = $request->amount;
            $currency_id = $request->currency_id;
            $transaction_type_id = $request->transaction_type_id;
            $methodId = $request->methodId;
            $data = $this->checkAmountValidate($amount, $currency_id, $transaction_type_id, $methodId);
            return response()->json($data);
        }
    }

    public function checkAmountValidate($amount, $currency_id, $transaction_type_id, $methodId)
    {
        $chargesLimit = ChargesLimit::with('currency')->where(['currency_id' => $currency_id, 'transaction_type_id' => $transaction_type_id, 'payment_method_id' => $methodId])->first();

        if (!$chargesLimit) {
            return ['status' => false, 'message' => "Payment method not available for this transaction"];
        }

        $limit = optional($chargesLimit->currency)->currency_type == 0 ? 8 : 2;

        if (Auth::check()) {
            $wallet = Wallet::firstOrCreate(['user_id' => Auth::id(), 'currency_id' => $currency_id]);
            $balance = getAmount($wallet->balance, $limit);
        } else {
            $balance = 0;
        }

        $status = false;
        $amount = getAmount($amount, $limit);
        $charge = 0;
        $min_limit = 0;
        $max_limit = 0;
        $fixed_charge = 0;
        $percentage = 0;
        $percentage_charge = 0;

        if ($chargesLimit) {
            $percentage = getAmount($chargesLimit->percentage_charge, $limit);
            $percentage_charge = getAmount(($amount * $percentage) / 100, $limit);
            $fixed_charge = getAmount($chargesLimit->fixed_charge, $limit);
            $min_limit = getAmount($chargesLimit->min_limit, $limit);
            $max_limit = getAmount($chargesLimit->max_limit, $limit);
            $charge = getAmount($percentage_charge + $fixed_charge, $limit);
        }

        $payable_amount = getAmount($amount + $charge, $limit);

        $new_balance = getAmount($balance + $amount, $limit);

        if ($amount < $min_limit || $amount > $max_limit) {
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
        $data['currency_limit'] = $limit;

        return $data;
    }

    public function confirmDeposit(Request $request, $utr)
    {
        $deposit = Deposit::with('receiver', 'currency')->where('utr', $utr)->first();

		if (!$deposit || $deposit->status) {
			return back()->with('success', 'Transaction already complete');
		}

		if ($deposit->depositable_type != Voucher::class){
			$user = Auth::user();
			$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
			$enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);
		}

        if ($request->isMethod('get')) {
            return view('user.deposit.confirm', compact(['utr', 'deposit', 'enable_for']));
        } elseif ($request->isMethod('post')) {

            if (in_array('deposit', $enable_for) && $deposit->depositable_type != Voucher::class) {
                $purifiedData = Purify::clean($request->all());
                $validationRules = [
                    'security_pin' => 'required|integer|digits:5',
                ];
                $validate = Validator::make($purifiedData, $validationRules);

                if ($validate->fails()) {
                    return back()->withErrors($validate)->withInput();
                }
                if (!Hash::check($purifiedData['security_pin'], $twoFactorSetting->security_pin)) {
                    return back()->withErrors(['security_pin' => 'You have entered an incorrect PIN'])->withInput();
                }
            }

            $checkAmountValidate = $this->checkAmountValidate($deposit->amount, $deposit->currency_id, config('transactionType.deposit'), $deposit->payment_method_id);
            if (!$checkAmountValidate['status']) {
                return back()->withInput()->with('alert', $checkAmountValidate['message']);
            }

            return redirect(route('payment.process', $utr));
        }
    }
}
