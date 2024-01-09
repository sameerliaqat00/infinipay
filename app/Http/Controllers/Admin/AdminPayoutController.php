<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Payout;
use App\Models\Transaction;
use App\Traits\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stevebauman\Purify\Facades\Purify;

class AdminPayoutController extends Controller
{
	use Notify;

	public function index()
	{
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$payouts = Payout::with(['user', 'user.profile', 'admin', 'currency', 'payoutMethod'])
			->latest()->paginate();
		return view('admin.payout.index', compact('currencies', 'payouts'));
	}

	public function search(Request $request)
	{
		$filterData = $this->_filter($request);
		$search = $filterData['search'];
		$currencies = $filterData['currencies'];
		$payouts = $filterData['payouts']
			->latest()->paginate();
		$payouts->appends($filterData['search']);
		return view('admin.payout.index', compact('search', 'payouts', 'currencies'));
	}

	public function showByUser($userId)
	{
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$payouts = Payout::with(['user', 'user.profile', 'admin', 'currency'])
			->where('user_id', $userId)
			->latest()->paginate();
		return view('admin.payout.index', compact('currencies', 'payouts', 'userId'));
	}

	public function searchByUser(Request $request, $userId)
	{
		$filterData = $this->_filter($request);
		$search = $filterData['search'];
		$currencies = $filterData['currencies'];
		$payouts = $filterData['payouts']
			->where('user_id', $userId)
			->latest()
			->paginate();
		$payouts->appends($filterData['search']);
		return view('admin.payout.index', compact('search', 'payouts', 'currencies', 'userId'));
	}

	public function _filter($request)
	{
		$currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
		$search = $request->all();
		$sent = isset($search['type']) ? preg_match("/sent/", $search['type']) : 0;
		$received = isset($search['type']) ? preg_match("/received/", $search['type']) : 0;
		$created_date = isset($search['created_at']) ? preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']) : 0;

		$payouts = Payout::with('user', 'user.profile', 'admin', 'currency')
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
			->when(isset($search['sender']), function ($query) use ($search) {
				return $query->whereHas('user', function ($qry) use ($search) {
					$qry->where('name', 'LIKE', "%{$search['sender']}%")
						->orWhere('username', 'LIKE', "%{$search['sender']}%");
				});
			})
			->when($sent == 1, function ($query) use ($search) {
				return $query->where("user_id", Auth::id());
			})
			->when($received == 1, function ($query) use ($search) {
				return $query->where("receiver_id", Auth::id());
			})
			->when($created_date == 1, function ($query) use ($search) {
				return $query->whereDate("created_at", $search['created_at']);
			});

		$data = [
			'currencies' => $currencies,
			'search' => $search,
			'payouts' => $payouts,
		];
		return $data;
	}

	public function show($utr)
	{
		$payout = Payout::with(['user', 'admin', 'currency'])->where('utr', $utr)->first();

		if (!$payout) {
			return back()->with('alert', 'Transaction not found');
		}

		return view('admin.payout.show', compact('payout'));
	}

	public function confirmPayout(Request $request, $utr)
	{
		$payout = Payout::with(['user', 'admin', 'currency', 'payoutMethod'])->where('utr', $utr)->first();
		if (!$payout) {
			return back()->with('alert', 'Transaction not found');
		} elseif ($payout->status != 1) {
			return back()->with('alert', 'Action not possible');
		}

		if (optional($payout->payoutMethod)->is_automatic == 1) {
			$methodObj = 'App\\Services\\Payout\\' . optional($payout->payoutMethod)->code . '\\Card';
			$data = $methodObj::payouts($payout);
			if (!$data) {
				return back()->with('alert', 'Method not available or unknown errors occur');
			}

			if ($data['status'] == 'error') {
				$payout->last_error = $data['data'];
				$payout->save();
				return back()->with('alert', $data['data']);
			}
		}

		$purifiedData = Purify::clean($request->all());
		if ($payout->payoutMethod->is_automatic == 0) {
			$payout->note = $purifiedData['note'];
			$transaction = new Transaction();
			$transaction->amount = $payout->amount;
			$transaction->charge = $payout->charge;
			$transaction->currency_id = $payout->currency_id;
			$payout->transactional()->save($transaction);
			$payout->status = 2;
			$payout->save();

			$receivedUser = $payout->user;
			$params = [
				'sender' => $receivedUser->name,
				'amount' => getAmount($payout->amount),
				'currency' => optional($payout->currency)->code,
				'transaction' => $payout->utr,
			];

			$action = [
				"link" => route('payout.index'),
				"icon" => "fa fa-money-bill-alt text-white"
			];
			$firebaseAction = route('payout.index');
			$this->sendMailSms($receivedUser, 'PAYOUT_CONFIRM', $params);
			$this->userPushNotification($receivedUser, 'PAYOUT_CONFIRM', $params, $action);
			$this->userFirebasePushNotification($receivedUser, 'PAYOUT_CONFIRM', $params, $firebaseAction);

		} else {
			if (optional($payout->payoutMethod)->code == 'coinbase' || optional($payout->payoutMethod)->code == 'perfectmoney') {
				$payout->note = $purifiedData['note'];
				$transaction = new Transaction();
				$transaction->amount = $payout->amount;
				$transaction->charge = $payout->charge;
				$transaction->currency_id = $payout->currency_id;
				$payout->transactional()->save($transaction);
				$payout->status = 2;
				$payout->save();
				$this->userNotify($payout, 1);
			} else {
				$payout->note = $purifiedData['note'];
				$payout->response_id = $data['response_id'];
				$payout->save();
			}
		}

		return redirect(route('admin.payout.index'))->with('success', 'Payment Confirmed');
	}

	public function cancelPayout(Request $request, $utr)
	{
		$payout = Payout::with(['user', 'admin', 'currency'])->where('utr', $utr)->first();
		if (!$payout) {
			return back()->with('alert', 'Transaction not found');
		} elseif ($payout->status != 1) {
			return back()->with('alert', 'Action not possible');
		}
		$purifiedData = Purify::clean($request->all());

		/*
		 * Add money from Sender Wallet
		 * */
		$sender_wallet = updateWallet($payout->user_id, $payout->currency_id, $payout->transfer_amount, 1);

		$payout->note = $purifiedData['note'];
		$payout->status = 5;
		$payout->save();


		$receivedUser = $payout->user;
		$params = [
			'sender' => $receivedUser->name,
			'amount' => $payout->amount,
			'currency' => optional($payout->currency)->code,
			'transaction' => $payout->utr,
		];

		$action = [
			"link" => route('payout.index'),
			"icon" => "fa fa-money-bill-alt text-white"
		];
		$firebaseAction = route('payout.index');
		$this->sendMailSms($receivedUser, 'PAYOUT_CANCEL', $params);
		$this->userPushNotification($receivedUser, 'PAYOUT_CANCEL', $params, $action);
		$this->userFirebasePushNotification($receivedUser, 'PAYOUT_CANCEL', $params, $firebaseAction);

		return redirect(route('admin.payout.index'))->with('alert', 'Payment Canceled');

	}

	public function payout($code, Request $request)
	{
		$apiResponse = json_decode($request->all());
		if ($code == 'razorpay') {
			$this->razorpayPayoutWebhook($apiResponse);
		}
		if ($code == 'flutterwave') {
			$this->razorpayPayoutWebhook($apiResponse);
		}
		if ($code == 'paystack') {
			$this->paystackPayoutWebhook($apiResponse);
		}
		if ($code == 'paypal') {
			$this->paypalPayoutWebhook($apiResponse);
		}
	}

	public function razorpayPayoutWebhook($apiResponse)
	{
		if ($apiResponse) {
			if ($apiResponse->payload) {
				if ($apiResponse->payload->payout) {
					if ($apiResponse->payload->payout->entity) {
						$payout = Payout::where('response_id', $apiResponse->payload->payout->entity->id)->first();
						if ($payout) {
							if ($apiResponse->event == 'payout.processed' || $apiResponse->event == 'payout.updated') {
								if ($payout->status != 2) {
									$transaction = new Transaction();
									$transaction->amount = $payout->amount;
									$transaction->charge = $payout->charge;
									$transaction->currency_id = $payout->currency_id;
									$payout->transactional()->save($transaction);

									$payout->status = 2;
									$payout->save();
									$this->userNotify($payout, 1);
								}
							} elseif ($apiResponse->event == 'payout.rejected' || $apiResponse->event == 'payout.failed') {
								$payout->status = 6;
								$payout->last_error = $apiResponse->payload->payout->entity->status_details->description ?? '';
								$payout->save();
								updateWallet($payout->user_id, $payout->currency_id, $payout->transfer_amount, 1);
								$this->userNotify($payout, 0);
							}
						}
					}
				}
			}
		}
	}

	public function flutterwavePayoutWebhook($apiResponse)
	{
		if ($apiResponse) {
			if ($apiResponse->event == 'transfer.completed') {
				if ($apiResponse->data) {
					$payout = Payout::where('response_id', $apiResponse->data->id)->first();
					if ($payout) {
						if ($apiResponse->data->status == 'SUCCESSFUL') {
							$transaction = new Transaction();
							$transaction->amount = $payout->amount;
							$transaction->charge = $payout->charge;
							$transaction->currency_id = $payout->currency_id;
							$payout->transactional()->save($transaction);
							$payout->status = 2;
							$payout->save();
							$this->userNotify($payout, 1);
						}
						if ($apiResponse->data->status == 'FAILED') {
							$payout->status = 6;
							$payout->last_error = $apiResponse->data->complete_message;
							$payout->save();
							updateWallet($payout->user_id, $payout->currency_id, $payout->transfer_amount, 1);
							$this->userNotify($payout, 0);
						}
					}
				}
			}
		}
	}

	public function paystackPayoutWebhook($apiResponse)
	{
		if ($apiResponse) {
			if ($apiResponse->data) {
				$payout = Payout::where('response_id', $apiResponse->data->id)->first();
				if ($payout) {
					if ($apiResponse->event == 'transfer.success') {
						$transaction = new Transaction();
						$transaction->amount = $payout->amount;
						$transaction->charge = $payout->charge;
						$transaction->currency_id = $payout->currency_id;
						$payout->transactional()->save($transaction);
						$payout->status = 2;
						$payout->save();
						$this->userNotify($payout, 1);
					} elseif ($apiResponse->event == 'transfer.failed') {
						$payout->status = 6;
						$payout->last_error = $apiResponse->data->complete_message;
						$payout->save();
						updateWallet($payout->user_id, $payout->currency_id, $payout->transfer_amount, 1);
						$this->userNotify($payout, 0);
					}
				}
			}
		}
	}

	public function paypalPayoutWebhook($apiResponse)
	{
		if ($apiResponse) {
			if ($apiResponse->batch_header) {
				$payout = Payout::where('response_id', $apiResponse->batch_header->payout_batch_id)->first();
				if ($payout) {
					if ($apiResponse->event_type == 'PAYMENT.PAYOUTSBATCH.SUCCESS' || $apiResponse->event_type == 'PAYMENT.PAYOUTS-ITEM.SUCCEEDED' || $apiResponse->event_type == 'PAYMENT.PAYOUTSBATCH.PROCESSING') {
						if ($apiResponse->event_type != 'PAYMENT.PAYOUTSBATCH.PROCESSING') {
							$transaction = new Transaction();
							$transaction->amount = $payout->amount;
							$transaction->charge = $payout->charge;
							$transaction->currency_id = $payout->currency_id;
							$payout->transactional()->save($transaction);
							$payout->status = 2;
							$payout->save();
							$this->userNotify($payout, 1);
						}
					} else {
						$payout->status = 6;
						$payout->last_error = $apiResponse->summary;
						$payout->save();
						updateWallet($payout->user_id, $payout->currency_id, $payout->transfer_amount, 1);
						$this->userNotify($payout, 0);
					}
				}
			}
		}
	}


	public function userNotify($payout, $type = 1)
	{
		$receivedUser = $payout->user;
		$params = [
			'sender' => $receivedUser->name,
			'amount' => getAmount($payout->amount),
			'currency' => optional($payout->currency)->code,
			'transaction' => $payout->utr,
		];

		$action = [
			"link" => route('payout.index'),
			"icon" => "fa fa-money-bill-alt text-white"
		];
		$firebaseAction = route('payout.index');
		if ($type == 1) {
			$template = 'PAYOUT_CONFIRM';
		} else {
			$template = 'PAYOUT_FAIL';
		}

		$this->sendMailSms($receivedUser, $template, $params);
		$this->userPushNotification($receivedUser, $template, $params, $action);
		$this->userFirebasePushNotification($receivedUser, $template, $params, $firebaseAction);
		return 0;
	}
}
