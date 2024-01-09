<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use App\Models\DisputeDetails;
use App\Models\Escrow;
use App\Traits\Notify;
use App\Traits\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DisputeController extends Controller
{
	use Upload, Notify;

	public function index()
	{
		$user = Auth::user();
		$disputes = Dispute::with(['disputeDetails'])
			->whereHasMorph('disputable',
				[Escrow::class],
				function ($query) use ($user) {
					$query->where('receiver_id', $user->id)
						->orWhere(function ($query) use ($user) {
							$query->where('sender_id', $user->id)
								->whereHas('disputable.disputeDetails', function ($query) use ($user) {
									$query->whereNull('user_id')
										->orWhere('user_id', $user->id);
								});
						});
				})
			->latest()
			->paginate();
		return view('user.dispute.index', compact('disputes'));
	}

	public function search(Request $request)
	{
		$search = $request->all();
		$user = Auth::user();
		$created_date = isset($search['created_at']) ? preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']) : 0;

		$disputes = Dispute::with(['disputeDetails'])
			->when(isset($search['utr']), function ($query) use ($search) {
				return $query->where('utr', 'LIKE', "%{$search['utr']}%");
			})
			->when($created_date == 1, function ($query) use ($search) {
				return $query->whereDate("created_at", $search['created_at']);
			})
			->when(isset($search['status']), function ($query) use ($search) {
				return $query->where('status', $search['status']);
			})
			->whereHasMorph('disputable',
				[Escrow::class],
				function ($query) use ($user) {
					$query->where('receiver_id', $user->id)
						->orWhere(function ($query) use ($user) {
							$query->where('sender_id', $user->id)
								->whereHas('disputable.disputeDetails', function ($query) use ($user) {
									$query->whereNull('user_id')
										->orWhere('user_id', $user->id);
								});
						});
				})
			->latest()
			->paginate();
		$disputes->appends($search);
		return view('user.dispute.index', compact('search', 'disputes'));
	}

	public function userDisputeView(Request $request, $utr)
	{
		$user = Auth::user();
		$escrow = Escrow::where('utr', $utr)
			->where(function ($query) use ($user) {
				$query->where('sender_id', $user->id)
					->orWhere('receiver_id', $user->id);
			})
			->firstOrFail();

		if ($request->isMethod('get')) {

			$dispute = Dispute::with(['disputable', 'disputeDetails' => function ($query) use ($user) {
				return $query->where('user_id', $user->id)
					->orWhereNull('user_id')
					->orderBy('id', 'DESC');
			}, 'disputeDetails.user', 'disputeDetails.admin'])->where([
				'disputable_id' => $escrow->id,
				'disputable_type' => Escrow::class,
			])->first();

			return view('user.dispute.view', compact('escrow', 'dispute'));
		} elseif ($request->isMethod('put')) {
			$images = $request->file('attachments');
			$allowedExtension = ['jpg', 'png', 'jpeg', 'pdf'];
			$this->validate($request, [
				'attachments' => [
					'max:4096',
					function ($attribute, $value, $fail) use ($images, $allowedExtension) {
						foreach ($images as $img) {
							$ext = strtolower($img->getClientOriginalExtension());
							if (($img->getSize() / 1000000) > 2) {
								return $fail("Images MAX  2MB ALLOW!");
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

			$dispute = Dispute::where([
				'disputable_id' => $escrow->id,
				'disputable_type' => Escrow::class,
			])->first();

			if ($dispute) {
				if ($dispute->status != 0) {
					return back()->with('alert', 'Dispute closed');
				} elseif (($escrow->sender_id == $user->id && $dispute->defender_reply_yn == 0) || ($escrow->receiver_id == $user->id && $dispute->claimer_reply_yn == 0)) {
					return back()->with('alert', 'You are muted, you are unable to sent message');
				}
			} else {
				$dispute = new Dispute();
				$dispute->status = 0; //open
				$dispute->defender_reply_yn = 0;
				$dispute->claimer_reply_yn = 1;
				$dispute->utr = (string)Str::uuid();
				$escrow->disputable()->save($dispute);
				$escrow->status = 6; //0=Pending, 1=generated, 2 = payment done, 3 = sender request to payment disburse, 4 = payment disbursed,5 = cancel, 6= dispute
				$escrow->save();
			}

			$disputeDetails = new DisputeDetails();
			$disputeDetails->dispute_id = $dispute->id;
			$disputeDetails->user_id = $user->id;
			$disputeDetails->status = 0;
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

			// Mail and push notification to ADMIN
			$link = route('admin.dispute.view', $dispute->utr);
			$params = [
				'sender' => $user->name,
				'amount' => $escrow->amount,
				'currency' => optional($escrow->currency)->code,
				'transaction' => $escrow->utr,
				'link' => $link,
			];
			$action = [
				"link" => $link,
				"icon" => "fa fa-money-bill-alt text-white"
			];
			$firebaseAction = $link;
			$this->adminMail('DISPUTE_REQUEST_TO_ADMIN', $params);
			$this->adminPushNotification('DISPUTE_REQUEST_TO_ADMIN', $params, $action);
			$this->adminFirebasePushNotification('DISPUTE_REQUEST_TO_ADMIN', $params, $firebaseAction);
			return back();
		}
	}

	public function userDisputeDownload($utr, $file)
	{
		$user = Auth::user();
		$file = decrypt($file);
		DisputeDetails::where('utr', $utr)->where(function ($query) use ($user) {
			$query->where('user_id', $user->id)->orWhereNull('user_id');
		})->whereJsonContains('files', $file)->firstOrFail();
		$path = config('location.dispute.path');
		$full_path = $path . '/' . $file;
		$mimetype = mime_content_type($full_path);
		header("Content-Disposition: attachment; filename= $file");
		header("Content-Type: " . $mimetype);
		return readfile($full_path);
	}
}
