<?php

namespace App\Http\Controllers;

use App\Events\AdminNotification;
use App\Events\UserNotification;
use App\Mail\MasterTemplate;
use App\Models\Admin;
use App\Models\ApiOrder;
use App\Models\ApiOrderTest;
use App\Models\BillPay;
use App\Models\Deposit;
use App\Models\EmailTemplate;
use App\Models\Gateway;
use App\Models\ProductOrder;
use App\Models\SiteNotification;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Facades\App\Services\BasicService;

class
PaymentController extends Controller
{
	public function depositConfirm($utr)
	{
		try {
			$deposit = Deposit::with('receiver', 'depositable')->where(['utr' => $utr, 'status' => 0])->first();
			if (!$deposit) throw new \Exception('Invalid Payment Request.');
			$gateway = Gateway::findOrFail($deposit->payment_method_id);
			if (!$gateway) throw new \Exception('Invalid Payment Gateway.');

			$getwayObj = 'App\\Services\\Gateway\\' . $gateway->code . '\\Payment';

			$data = $getwayObj::prepareData($deposit, $gateway);

			$data = json_decode($data);
		} catch (\Exception $exception) {
			if ($deposit->depositable_type == ApiOrder::class || $deposit->depositable_type == ApiOrderTest::class) {
				$order = $deposit->depositable;
				$this->apiFailResponseSend($order, 'Something bad happened. Please try again');
			}
			return back()->with('alert', $exception->getMessage());
		}

		if (isset($data->error)) {
			return back()->with('alert', $data->message);
		}

		if (isset($data->redirect)) {
			return redirect($data->redirect_url);
		}
		$link = null;
		if ($data->view == 'user.store.payment.stripe') {
			$order = ProductOrder::find($deposit->depositable_id);
			$link = optional($order->store)->link ?? null;
		}
		$page_title = 'Payment Confirm';
		return view($data->view, compact('data', 'page_title', 'deposit', 'link'));
	}

	public function gatewayIpn(Request $request, $code, $trx = null, $type = null)
	{
		if (isset($request->m_orderid)) {
			$trx = $request->m_orderid;
		}
		if (isset($request->MERCHANT_ORDER_ID)) {
			$trx = $request->MERCHANT_ORDER_ID;
		}
		if (isset($request->payment_ref)) {
			$payment_ref = $request->payment_ref;
		}

		if ($code == 'coinbasecommerce') {
			$gateway = Gateway::where('code', $code)->first();
			$postdata = file_get_contents("php://input");
			$res = json_decode($postdata);
			if (isset($res->event)) {
				$deposit = Deposit::with('receiver')->where('utr', $res->event->data->metadata->trx)->orderBy('id', 'DESC')->first();
				$sentSign = $request->header('X-Cc-Webhook-Signature');
				$sig = hash_hmac('sha256', $postdata, $gateway->parameters->secret);

				if ($sentSign == $sig) {
					if ($res->event->type == 'charge:confirmed' && $deposit->status == 0) {
						BasicService::prepareOrderUpgradation($deposit);
					}
				}
			}
			session()->flash('success', 'You request has been processing.');

			if ($deposit->depositable_type == 'App\Models\ProductOrder') {
				$order = ProductOrder::with(['store'])->where('status', 1)->where('utr', $deposit->utr)->firstOrFail();
				$link = optional($order->store)->link ?? '@';
				return redirect()->route('order.success', [$link, $order->order_number]);
			}
			if ($deposit->depositable_type == ApiOrder::class || $deposit->depositable_type == ApiOrderTest::class) {
				$order = $deposit->depositable;
				$res = $this->apiResponseSend($order);
				$data = [
					'status' => 'success',
					'id' => $order->utr,
					'currency' => optional($order->currency)->code,
					'amount' => $order->amount,
					'order_id' => $order->order_id,
				];
				$data = http_build_query($data);
				$url = $order->redirect_url;
				$url = $url . '?' . $data;
				return redirect()->away($url);
			}

			return redirect()->route('success');
		}

		try {
			$gateway = Gateway::where('code', $code)->first();

			if (!$gateway) throw new \Exception('Invalid Payment Gateway.');

			if (isset($trx)) {
				$deposit = Deposit::with('receiver')->where('utr', $trx)->first();
				if (!$deposit) throw new \Exception('Invalid Payment Request.');
			}
			if (isset($payment_ref)) {
				$order = Deposit::where('btc_wallet', $payment_ref)->orderBy('id', 'desc')->with(['gateway', 'user'])->first();
				if (!$order) throw new \Exception('Invalid Payment Request.');
			}
			$getwayObj = 'App\\Services\\Gateway\\' . $code . '\\Payment';
			$data = $getwayObj::ipn($request, $gateway, @$deposit, @$trx, @$type);


		} catch (\Exception $exception) {
			if ($deposit->depositable_type == ApiOrder::class || $deposit->depositable_type == ApiOrderTest::class) {
				$order = $deposit->depositable;
				$this->apiFailResponseSend($order, $exception->getMessage());
			}
			return back()->with('alert', $exception->getMessage());
		}
		if (isset($data['redirect'])) {
			if ($deposit->depositable_type != 'App\Models\ProductOrder' && $deposit->depositable_type != 'App\Models\App\Models\QRCode' && $deposit->depositable_type != 'App\Models\Invoice' && $deposit->depositable_type != ApiOrder::class) {
				if (basicControl()->email_notification) {
					$emailTemplate = EmailTemplate::find(2);
					$emailName = $emailTemplate->name;
					$notify_email = $emailTemplate->notify_email;
					$to = $deposit->email;
					$subject = 'Deposit Money';

					$message = str_replace('[[sender_name]]', optional($deposit->receiver)->name, $emailTemplate->template);
					$message = str_replace('[[receiver_name]]', optional($deposit->receiver)->name, $message);
					$message = str_replace('[[amount]]', $deposit->amount, $message);
					$message = str_replace('[[utr]]', $deposit->utr, $message);

					Mail::to($to)->queue(new MasterTemplate($subject, $message, $notify_email, $emailName));
				}

				$currencyCode = ($deposit->currency)->code ?? 'N/A';

				$siteNotificationData = [
					"text" => "Payment received $deposit->amount $currencyCode",
					"link" => route('fund.index'),
					"icon" => "fas fa-donate text-white",
				];
				if (class_exists($deposit->depositable_type) && isset($deposit->depositable_id)) {
					$findDepoObj = $deposit->depositable_type::find($deposit->depositable_id);
					$user = User::find($findDepoObj->sender_id);
				}
				if (basicControl()->push_notification) {
					if (isset($user)) {
						$siteNotification = new SiteNotification();
						$siteNotification->description = $siteNotificationData;
						$user->siteNotificational()->save($siteNotification);
						event(new UserNotification($siteNotificationData, $deposit->user_id));
					}

					$admins = Admin::all();
					foreach ($admins as $admin) {
						$siteNotification = new SiteNotification();
						$siteNotification->description = $siteNotificationData;
						$admin->siteNotificational()->save($siteNotification);
						event(new AdminNotification($siteNotificationData, $admin->id));
					}
				}
			}
			if ($deposit->depositable_type == 'App\Models\ProductOrder') {
				$order = ProductOrder::with(['store'])->where('status', 1)->where('utr', $deposit->utr)->firstOrFail();
				$link = optional($order->store)->link ?? '@';
				return redirect()->route('order.success', [$link, $order->order_number]);
			}

			if ($deposit->depositable_type == ApiOrder::class || $deposit->depositable_type == ApiOrderTest::class) {
				$order = $deposit->depositable;
				$res = $this->apiResponseSend($order);
				$data = [
					'status' => 'success',
					'id' => $order->utr,
					'currency' => optional($order->currency)->code,
					'amount' => $order->amount,
					'order_id' => $order->order_id,
				];
				$data = http_build_query($data);
				$url = $order->redirect_url;
				$url = $url . '?' . $data;
				return redirect()->away($url);
			}
			return redirect($data['redirect'])->with($data['status'], $data['msg']);
		}
	}

	public function apiResponseSend($order)
	{
		$url = $order->ipn_url;
		$postParam = [
			'status' => 'success',
			'data' => [
				'id' => $order->utr,
				'currency' => optional($order->currency)->code,
				'amount' => $order->amount,
				'order_id' => $order->order_id,
				'meta' => [
					'customer_name' => optional($order->meta)->customer_name ?? null,
					'customer_email' => optional($order->meta)->customer_email ?? null,
					'description' => optional($order->meta)->description ?? null,
				],
			],
		];
		$methodObj = 'App\\Services\\BasicCurl';
		$response = $methodObj::curlPostRequest($url, $postParam);
		return 0;
	}

	public function apiFailResponseSend($order, $msg)
	{
		$order->status = 2;
		$order->save();

		$url = $order->ipn_url;
		$postParam = [
			'status' => 'error',
			'data' => [
				'message' => $msg
			],
		];
		$methodObj = 'App\\Services\\BasicCurl';
		$response = $methodObj::curlPostRequest($url, $postParam);
		return 0;
	}

	public function success()
	{
		return view('success');
	}

	public function orderSuccess($link, $orderNumber)
	{

		$store = Store::with('user.storeCurrency')->where('link', $link)->firstOrFail();
		$data['order'] = ProductOrder::with(['orderDetails', 'orderDetails.product'])->where('order_number', $orderNumber)->where('status', 1)->firstOrFail();
		return view('user.store.orderConfirm', $data, compact('link', 'store'));
	}

	public function failed()
	{
		return view('failed');
	}
}
