<?php

namespace App\Services;

use App\Mail\SendInvoiceMail;
use App\Mail\SendOrderMail;
use App\Models\ApiOrder;
use App\Models\ApiOrderTest;
use App\Models\BillPay;
use App\Models\CommissionEntry;
use App\Models\Fund;
use App\Models\Invoice;
use App\Models\ProductOrder;
use App\Models\QRCode;
use App\Models\ReferralBonus;
use App\Models\StoreProductStock;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VirtualCardOrder;
use App\Models\Voucher;
use App\Traits\Notify;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Image;

class BasicService
{
	use Notify;

	public function validateImage(object $getImage, string $path)
	{
		if ($getImage->getClientOriginalExtension() == 'jpg' or $getImage->getClientOriginalName() == 'jpeg' or $getImage->getClientOriginalName() == 'png') {
			$image = uniqid() . '.' . $getImage->getClientOriginalExtension();
		} else {
			$image = uniqid() . '.jpg';
		}
		Image::make($getImage->getRealPath())->resize(300, 250)->save($path . $image);
		return $image;
	}

	public function validateDate(string $date)
	{
		if (preg_match("/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{2,4}$/", $date)) {
			return true;
		} else {
			return false;
		}
	}

	public function cryptoQR($wallet, $amount, $crypto = null)
	{

		$varb = $wallet . "?amount=" . $amount;
		return "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=$varb&choe=UTF-8";
	}

	public function validateKeyword(string $search, string $keyword)
	{
		return preg_match('~' . preg_quote($search, '~') . '~i', $keyword);
	}

	public function prepareOrderUpgradation($deposit)
	{
		$basicControl = basicControl();
		$deposit->status = 1;
		if ($deposit->depositable_type == Fund::class && !isset($deposit->depositable_id)) {
			/*
			 * Add money to wallet
			 * */
			if ($deposit->card_order_id != null) {
				$cardOrder = VirtualCardOrder::findOrFail($deposit->card_order_id);
				$cardOrder->status = 8;

				$info = cardCurrencyCheck($cardOrder->id);
				$percentValue = ($info['PercentCharge'] * $deposit->amount) / 100;
				$charge = $info['FixedCharge'] + $percentValue;

				$cardOrder->fund_amount = $deposit->amount - $charge;
				$cardOrder->fund_charge = $charge;
				$cardOrder->save();
			} else {
				$wallet = updateWallet($deposit->user_id, $deposit->currency_id, $deposit->amount, 1);
			}


			$fund = new Fund();
			$fund->user_id = $deposit->user_id;
			$fund->currency_id = $deposit->currency_id;
			$fund->percentage = $deposit->percentage;
			$fund->charge_percentage = $deposit->charge_percentage;
			$fund->charge_fixed = $deposit->charge_fixed;
			$fund->charge = $deposit->charge;
			$fund->amount = $deposit->amount;
			$fund->email = $deposit->email;
			$fund->status = 1;
			$fund->utr = $deposit->utr;
			$fund->card_order_id = $deposit->card_order_id ?? null;
			$fund->save();

			$deposit->depositable_id = $fund->id;
			$transaction = new Transaction();
			$transaction->amount = $fund->amount;
			$transaction->charge = $fund->charge;
			$transaction->currency_id = $fund->currency_id;
			$fund->transactional()->save($transaction);

			$amount = $fund->amount / $fund->currency->exchange_rate;
			if ($basicControl->deposit_commission == 1) {
				$commissionType = 'deposit';
				$this->levelCommission($fund->user_id, $amount, $commissionType);
			}

			$params = [
				'amount' => $fund->amount,
				'currency' => optional($fund->currency)->code,
				'transaction' => $fund->utr,
			];

			$action = [
				"link" => route('fund.index'),
				"icon" => "fa fa-money-bill-alt text-white"
			];
			$firebaseAction = route('fund.index');
			$user = Auth::user();
			$this->sendMailSms($user, 'ADD_FUND_USER_USER', $params);
			$this->userPushNotification($user, 'ADD_FUND_USER_USER', $params, $action);
			$this->userFirebasePushNotification($user, 'ADD_FUND_USER_USER', $params, $firebaseAction);

			$params = [
				'amount' => $fund->amount,
				'currency' => optional($fund->currency)->code,
				'transaction' => $fund->utr,
			];

			$action = [
				"link" => route('admin.user.fund.add.show', $user->id),
				"icon" => "fa fa-money-bill-alt text-white"
			];
			$firebaseAction = route('admin.user.fund.add.show', $user->id);
			$this->adminMail('ADD_FUND_USER_USER', $params);
			$this->adminFirebasePushNotification('ADD_FUND_USER_USER', $params, $firebaseAction);

		} elseif ($deposit->depositable_type == Voucher::class) {
			$voucher = Voucher::find($deposit->depositable_id);
			if ($voucher) {
				/*
				 * Add money to wallet
				 * */
				$wallet = updateWallet($voucher->sender_id, $voucher->currency_id, $voucher->received_amount, 1);
				$voucher->status = 2;
				$transaction = new Transaction();
				$transaction->amount = $voucher->amount;
				$transaction->charge = $voucher->charge;
				$transaction->currency_id = $voucher->currency_id;
				$voucher->transactional()->save($transaction);
				$voucher->save();

				// send mail sms notification who receiver payment
				$params = [
					'receiver' => $voucher->email,
					'amount' => $voucher->amount,
					'currency' => optional($voucher->currency)->code,
					'transaction' => $voucher->utr,
				];
				$action = [
					"link" => route('voucher.index'),
					"icon" => "fa fa-money-bill-alt text-white"
				];
				$firebaseAction = route('voucher.index');
				$sender = $voucher->sender;
				$this->sendMailSms($sender, 'VOUCHER_PAYMENT_TO', $params);
				$this->userPushNotification($sender, 'VOUCHER_PAYMENT_TO', $params, $action);
				$this->userFirebasePushNotification($sender, 'VOUCHER_PAYMENT_TO', $params, $firebaseAction);

				$user = new User();
				$user->name = 'Concern';
				$user->email = $voucher->email;
				// send mail sms notification who make payment
				$params = [
					'sender' => optional($voucher->sender)->name,
					'amount' => $voucher->amount,
					'currency' => optional($voucher->currency)->code,
					'transaction' => $voucher->utr,
				];
				$this->sendMailSms($user, 'VOUCHER_PAYMENT_FROM', $params);
				$this->userPushNotification($user, 'VOUCHER_PAYMENT_FROM', $params, $action);
				$this->userFirebasePushNotification($user, 'VOUCHER_PAYMENT_FROM', $params, $firebaseAction);
			}
		} elseif ($deposit->depositable_type == Invoice::class) {
			$invoice = Invoice::find($deposit->depositable_id);
			if ($invoice) {
				/*
				 * Add money to wallet
				 * */
				$receiveAmount = $invoice->grand_total;
				if ($invoice->charge_pay == 0) {
					$receiveAmount -= $invoice->charge;
				}

				$wallet = updateWallet($invoice->sender_id, $invoice->currency_id, $receiveAmount, 1);
				$invoice->status = 'paid';
				$invoice->paid_at = Carbon::now();

				$transaction = new Transaction();
				$transaction->amount = $invoice->grand_total;
				$transaction->charge = $invoice->charge;
				$transaction->currency_id = $invoice->currency_id;
				$invoice->transactional()->save($transaction);
				$invoice->save();

				// send mail sms notification who receiver payment
				$params = [
					'receiver' => $invoice->customer_email,
					'amount' => $invoice->grand_total,
					'currency' => optional($invoice->currency)->code,
					'transaction' => $invoice->has_slug ?? null,
				];
				$action = [
					"link" => route('invoice.index'),
					"icon" => "fa fa-money-bill-alt text-white"
				];
				$firebaseAction = route('invoice.index');
				$sender = $invoice->sendBy;
				$this->sendMailSms($sender, 'INVOICE_PAYMENT_TO', $params);
				$this->userPushNotification($sender, 'INVOICE_PAYMENT_TO', $params, $action);
				$this->userFirebasePushNotification($sender, 'INVOICE_PAYMENT_TO', $params, $firebaseAction);

				$user = new User();
				$user->name = 'Concern';
				$user->email = $invoice->customer_email;
				// send mail sms notification who make payment
				$params = [
					'sender' => optional($invoice->sendBy)->name,
					'amount' => $invoice->grand_total,
					'currency' => optional($invoice->currency)->code,
					'transaction' => $invoice->has_slug ?? null,
				];
				$this->sendMailSms($user, 'INVOICE_PAYMENT_FROM', $params);
				$this->userPushNotification($user, 'INVOICE_PAYMENT_FROM', $params, $action);
				$this->userFirebasePushNotification($user, 'INVOICE_PAYMENT_FROM', $params, $firebaseAction);
			}
			$deposit->save();
			return true;
		} elseif ($deposit->depositable_type == ProductOrder::class) {
			$order = ProductOrder::with(['store', 'store.user', 'orderDetails'])->find($deposit->depositable_id);
			if ($order) {
				/*
				 * Add money to wallet
				 * */
				$receiveAmount = $order->total_amount + $order->shipping_charge;

				$wallet = updateWallet(optional($order->store)->user_id, $deposit->currency_id, $receiveAmount, 1);
				$order->status = 1;

				foreach ($order->orderDetails as $singleProduct) {
					$stock = StoreProductStock::where('product_id', $singleProduct->product_id)->whereJsonContains('product_attr_lists_id', $singleProduct->attributes_id)->first();
					$newStock = $stock->quantity - $singleProduct->quantity;
					$stock->update(['quantity' => $newStock]);
				}

				$transaction = new Transaction();
				$transaction->amount = $receiveAmount;
				$transaction->charge = 0;
				$transaction->currency_id = $deposit->currency_id;
				$order->transactional()->save($transaction);
				$order->save();

				// send mail sms notification who receiver payment
				$params = [
					'amount' => $receiveAmount,
					'currency' => optional($deposit->currency)->code ?? null,
					'orderNumber' => $order->order_number,
				];
				$action = [
					"link" => "",
					"icon" => "fa fa-money-bill-alt text-white"
				];
				$this->userPushNotification(optional($order->store)->user, 'PRODUCT_ORDER', $params, $action);
				$this->userFirebasePushNotification(optional($order->store)->user, 'PRODUCT_ORDER', $params);

				Mail::to($order->email)
					->queue(new SendOrderMail($order));

				Mail::to(optional($order->store->user)->email)
					->queue(new SendOrderMail($order));
			}
			$deposit->save();
			return true;
		} elseif ($deposit->depositable_type == QRCode::class) {
			$qrCode = QRCode::find($deposit->depositable_id);
			if ($qrCode) {
				/*
				 * Add money to wallet
				 * */
				$receiveAmount = $qrCode->amount;

				$wallet = updateWallet($qrCode->user_id, $qrCode->currency_id, $receiveAmount, 1);
				$qrCode->status = 1;

				$transaction = new Transaction();
				$transaction->amount = $qrCode->amount;
				$transaction->charge = $qrCode->charge;
				$transaction->currency_id = $qrCode->currency_id;
				$qrCode->transactional()->save($transaction);
				$qrCode->save();

				// send mail sms notification who receive payment
				$params = [
					'email' => $qrCode->email,
					'amount' => $qrCode->amount,
					'currency' => optional($qrCode->currency)->code,
				];
				$action = [
					"link" => "",
					"icon" => "fa fa-money-bill-alt text-white"
				];

				$paymentReciver = $qrCode->user;
				$this->sendMailSms($paymentReciver, 'QR_PAYMENT_TO', $params);
				$this->userPushNotification($paymentReciver, 'QR_PAYMENT_TO', $params, $action);
				$this->userFirebasePushNotification($paymentReciver, 'QR_PAYMENT_TO', $params);

				$user = new User();
				$user->name = 'Concern';
				$user->email = $qrCode->email;
				// send mail sms notification who make payment
				$params = [
					'sender' => optional($qrCode->user)->name,
					'amount' => $qrCode->amount,
					'currency' => optional($qrCode->currency)->code,
				];
				$this->sendMailSms($user, 'QR_PAYMENT_FROM', $params);
				$this->userPushNotification($user, 'QR_PAYMENT_FROM', $params, $action);
				$this->userFirebasePushNotification($user, 'QR_PAYMENT_FROM', $params);
			}
		} elseif ($deposit->depositable_type == ApiOrder::class) {
			$apiOrder = ApiOrder::find($deposit->depositable_id);
			$wallet = updateWallet($apiOrder->user_id, $apiOrder->currency_id, $apiOrder->amount, 1);
			$apiOrder->status = 1;
			$apiOrder->paid_at = Carbon::now();
			$apiOrder->save();

			$transaction = new Transaction();
			$transaction->amount = $apiOrder->amount;
			$transaction->charge = $apiOrder->charge;
			$transaction->currency_id = $apiOrder->currency_id;
			$apiOrder->transactional()->save($transaction);

			$params = [
				'amount' => $apiOrder->amount,
				'currency' => optional($apiOrder->currency)->code,
				'transaction' => $apiOrder->utr,
			];

			$action = [
				"link" => route('fund.index'),
				"icon" => "fa fa-money-bill-alt text-white"
			];
			$firebaseAction = route('fund.index');
			$user = $apiOrder->user;
			$this->sendMailSms($user, 'API_PAYMENT', $params);
			$this->userPushNotification($user, 'API_PAYMENT', $params, $action);
			$this->userFirebasePushNotification($user, 'API_PAYMENT', $params, $firebaseAction);

			$params = [
				'receiver' => optional($apiOrder->user)->username,
				'amount' => $apiOrder->amount,
				'currency' => optional($apiOrder->currency)->code,
				'transaction' => $apiOrder->utr,
			];

			$action = [
				"link" => "",
				"icon" => "fa fa-money-bill-alt text-white"
			];

			$this->adminPushNotification('ADMIN_API_PAYMENT', $params, $action);
			$this->adminFirebasePushNotification('ADMIN_API_PAYMENT', $params);

		} elseif ($deposit->depositable_type == ApiOrderTest::class) {
			$apiOrderTest = ApiOrderTest::find($deposit->depositable_id);

			$apiOrderTest->status = 1;
			$apiOrderTest->paid_at = Carbon::now();
			$apiOrderTest->save();
		}
		$deposit->save();
		return true;
	}

	public function levelCommission($id, $amount, $commissionType = '')
	{
		$basicControl = basicControl();
		$usr = $id;
		$user = User::find($id);
		$i = 1;

		$levels = ReferralBonus::where('referral_on', $commissionType)->count();

		while ($usr != "" || $usr != "0" || $i < $levels) {
			$me = User::find($usr);
			$refer = User::find($me->ref_by);

			if ($refer == "") {
				break;
			}

			$commission = ReferralBonus::where(['referral_on' => $commissionType, 'level' => $i, 'status' => 1])->first();
			if (!$commission) {
				break;
			}

			// 1= fixed, 0 = percent
			if ($commission->calcType == 0) {
				$commission_amount = ($amount * $commission->amount) / 100;
			} else {
				$commission_amount = $commission->amount;
			}

			$commissionEntry = CommissionEntry::where(['to_user' => $refer->id, 'from_user' => $id, 'type' => $commissionType])->first();
			if ($commissionEntry) {
				break;
			}

			$commissionLog = new CommissionEntry();
			$commissionLog->to_user = $refer->id;
			$commissionLog->from_user = $id;
			$commissionLog->level = $i;
			$commissionLog->commission_amount = getAmount($commission_amount);
			$commissionLog->title = 'level ' . $i . ' Referral Commission From ' . $user->username;
			$commissionLog->type = $commissionType;
			$commissionLog->currency_id = $basicControl->base_currency;
			$commissionLog->utr = (string)Str::uuid();
			$commissionLog->save();

			$transaction = new Transaction();
			$transaction->amount = $commissionLog->commission_amount;
			$transaction->charge = 0;
			$transaction->currency_id = $commissionLog->currency_id;
			$commissionLog->transactional()->save($transaction);
			updateWallet($refer->id, $basicControl->base_currency, getAmount($amount), 1);


			$receivedUser = User::find($refer->id);
			$params = [
				'sender' => $user->username,
				'amount' => $commissionLog->commission_amount,
				'currency' => optional($commissionLog->currency)->code,
				'transaction' => $commissionLog->utr,
			];

			$action = [
				"link" => route('user.commission.index'),
				"icon" => "fa fa-money-bill-alt text-white"
			];
			$firebaseAction = route('user.commission.index');
			$this->sendMailSms($receivedUser, 'DEPOSIT_BONUS', $params);
			$this->userPushNotification($receivedUser, 'DEPOSIT_BONUS', $params, $action);
			$this->userFirebasePushNotification($receivedUser, 'DEPOSIT_BONUS', $params, $firebaseAction);

			$usr = $refer->id;
			$i++;
		}
		return 0;
	}
}
