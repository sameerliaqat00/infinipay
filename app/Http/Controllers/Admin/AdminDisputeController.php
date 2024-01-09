<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\DisputeDetails;
use App\Models\Escrow;
use App\Models\Transaction;
use App\Traits\Notify;
use App\Traits\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AdminDisputeController extends Controller
{
	use Upload, Notify;

	public function index()
	{
		$disputes = Dispute::with(['disputeDetails'])
			->latest()
			->paginate();

		return view('admin.dispute.index', compact('disputes'));
	}

	public function search(Request $request)
	{
		$search = $request->all();
		$date = isset($search['date']) ? preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['date']) : 0;
		$disputes = Dispute::with(['disputeDetails'])
			->when(isset($search['utr']), function ($query) use ($search) {
				return $query->where('utr', 'LIKE', "%{$search['utr']}%");
			})
			->when($date == 1, function ($query) use ($search) {
				return $query->whereDate("created_at", $search['date']);
			})
			->when(isset($search['status']), function ($query) use ($search) {
				return $query->where('status', $search['status']);
			})
			->latest()
			->paginate();
		$disputes->appends($search);
		return view('admin.dispute.index', compact('disputes', 'search'));
	}

	public function disputeStatusChange(Request $request, $utr, $option)
	{
		$dispute = Dispute::with('disputable')->where('utr', $utr)->first();


		$status = $dispute->status;
		if ($status == 1) {
			return back('alert', 'Issue already solved');
		} elseif ($status == 2) {
			return back('alert', 'Issue already closed');
		}
		$dispute->status = $option;
		$action = 0;
		$message = '';

		if ($option == 1) {
			/*
			 * refund money to claimer
			 * */
			$receiver_wallet = updateWallet(optional($dispute->disputable)->receiver_id, optional($dispute->disputable)->currency_id, optional($dispute->disputable)->transfer_amount, 1);
			$action = 0;
			$transaction = new Transaction();
			$transaction->amount = $dispute->amount;
			$transaction->charge = $dispute->charge;
			$transaction->currency_id = $dispute->currency_id;
			$dispute->transactional()->save($transaction);
		} elseif ($option == 2) {
			/*
			 * add money to defender Wallet
			 * */
			$receiver_wallet = updateWallet(optional($dispute->disputable)->sender_id, optional($dispute->disputable)->currency_id, optional($dispute->disputable)->received_amount, 1);
			$message = 'Issue marked as closed successfully';
			$action = 1;
			$transaction = new Transaction();
			$transaction->amount = $dispute->amount;
			$transaction->charge = $dispute->charge;
			$transaction->currency_id = $dispute->currency_id;
			$dispute->disputable->transactional()->save($transaction);
		}
		$dispute->save();

		$disputeDetails = new DisputeDetails();
		$disputeDetails->utr = (string)Str::uuid();
		$disputeDetails->action = $action;
		$disputeDetails->dispute_id = $dispute->id;
		$disputeDetails->admin_id = Auth::id();
		$disputeDetails->status = 1;
		$disputeDetails->save();
		return back()->with('success', __($message));
	}

	public function claimerMuteUnmute(Request $request, $utr, $option)
	{
		$dispute = Dispute::with('disputable')->where('utr', $utr)->first();

		$dispute->claimer_reply_yn = !$dispute->claimer_reply_yn;
		$dispute->save();

		if ($dispute->claimer_reply_yn == 0) {
			$message = 'Claimer has been muted successfully';
			$action = 2;
		} else {
			$message = 'Claimer has been unmuted successfully';
			$action = 3;
		}
		$disputeDetails = new DisputeDetails();
		$disputeDetails->utr = (string)Str::uuid();
		$disputeDetails->action = $action;
		$disputeDetails->dispute_id = $dispute->id;
		$disputeDetails->admin_id = Auth::id();
		$disputeDetails->status = 1;
		$disputeDetails->user_id = optional($dispute->disputable)->receiver_id;
		$disputeDetails->save();
		return back()->with('success', __($message));
	}

	public function defenderMuteUnmute(Request $request, $utr, $option)
	{
		$dispute = Dispute::with('disputable')->where('utr', $utr)->first();

		$dispute->defender_reply_yn = !$dispute->defender_reply_yn;
		$dispute->save();

		if ($dispute->defender_reply_yn == 0) {
			$message = 'Defender has been muted successfully';
			$action = 2;
		} else {
			$message = 'Defender has been unmuted successfully';
			$action = 3;
		}
		$disputeDetails = new DisputeDetails();
		$disputeDetails->utr = (string)Str::uuid();
		$disputeDetails->action = $action;
		$disputeDetails->dispute_id = $dispute->id;
		$disputeDetails->admin_id = Auth::id();
		$disputeDetails->status = 1;
		$disputeDetails->user_id = optional($dispute->disputable)->sender_id;
		$disputeDetails->save();
		return back()->with('success', __($message));
	}

	public function adminDisputeView(Request $request, $utr)
	{
		$dispute = Dispute::with(['disputable', 'disputeDetails' => function ($query) {
			$query->orderBy('id', 'DESC');
		}, 'disputeDetails.user', 'disputeDetails.admin'])
			->where('utr', $utr)
			->firstOrFail();

		if ($request->isMethod('get')) {
			return view('admin.dispute.view', compact('dispute'));
		} elseif ($request->isMethod('put')) {
			$images = $request->file('attachments');
			$allowedExtension = array('jpg', 'png', 'jpeg', 'pdf');

			$this->validate($request, [
				'attachments.*' => [
					'max:102400',
					function ($attribute, $value, $fail) use ($images, $allowedExtension) {
						foreach ($images as $img) {
							$ext = strtolower($img->getClientOriginalExtension());
							if (($img->getSize() / 1000000) > 2) {
								return $fail("Images maximum 100 MB allowed.");
							}
							if (!in_array($ext, $allowedExtension)) {
								return $fail("Only png, jpg, jpeg, pdf images are allowed");
							}
						}
						if (count($images) > 5) {
							return $fail("Maximum 5 images can be uploaded");
						}
					},
				],
				'message' => 'required'
			]);

			$defender_id = optional($dispute->disputable)->sender_id;
			if ($request->user_id == $defender_id || !isset($request->user_id)) {
				$checkDisputeDetails = DisputeDetails::where('dispute_id', $dispute->id)->whereNotNull('message')
					->where(function ($query) use ($defender_id) {
						$query->where('user_id', $defender_id)->orWhereNull('user_id');
					})->count();
				$dispute->defender_reply_yn = $checkDisputeDetails > 0 ? $dispute->defender_reply_yn : 1;
			}

			$dispute->save();
			$user = Auth::user();
			$disputeDetails = new DisputeDetails();
			$disputeDetails->dispute_id = $dispute->id;
			$disputeDetails->admin_id = $user->id;
			$disputeDetails->user_id = $request->user_id;
			$disputeDetails->status = 1;
			$disputeDetails->utr = (string)Str::uuid();
			$disputeDetails->message = $request->message;
			if ($request->hasFile('attachments')) {
				$path = config('location.dispute.path');
				$files = [];
				foreach ($request->file('attachments') as $image) {
					try {
						$files[] = $this->uploadImage($image, $path);
					} catch (\Exception $exp) {
						return back()->withInput()->with('alert', 'Could not upload your ' . $image);
					}
				}
				$disputeDetails->files = $files;
			}
			$disputeDetails->save();

			// Mail and push notification to USER
			$receivedUser = $disputeDetails->user;
			$link = route('user.dispute.view', $dispute->utr);
			$params = [
				'sender' => $user->name,
				'transaction' => $dispute->utr,
				'link' => $link,
			];
			$action = [
				"link" => $link,
				"icon" => "fa fa-money-bill-alt text-white"
			];
			$firebaseAction = $link;
			$this->sendMailSms($receivedUser, 'DISPUTE_REQUEST_TO_USER', $params);
			$this->userPushNotification($receivedUser, 'DISPUTE_REQUEST_TO_USER', $params, $action);
			$this->userFirebasePushNotification($receivedUser, 'DISPUTE_REQUEST_TO_USER', $params, $firebaseAction);
			return back();
		}
	}

	public function adminDisputeDownload($utr, $file)
	{
		$file = decrypt($file);
		DisputeDetails::where('utr', $utr)->whereJsonContains('files', $file)->firstOrFail();
		$path = config('location.dispute.path');
		$full_path = $path . '/' . $file;
		$mimetype = mime_content_type($full_path);
		header("Content-Disposition: attachment; filename= $file");
		header("Content-Type: " . $mimetype);
		return readfile($full_path);
	}
}
