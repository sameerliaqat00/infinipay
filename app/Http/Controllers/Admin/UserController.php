<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\Escrow;
use App\Models\Exchange;
use App\Models\Fund;
use App\Models\Gateway;
use App\Models\Invoice;
use App\Models\Language;
use App\Models\Payout;
use App\Models\QRCode;
use App\Models\RedeemCode;
use App\Models\RequestMoney;
use App\Models\Transfer;
use App\Models\TwoFactorSetting;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Voucher;
use App\Models\Wallet;
use App\Traits\Notify;
use App\Traits\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
	use Upload, Notify;

	public function index()
	{

		$users = User::with('profile')->latest()->paginate();
		return view('admin.user.index', compact('users'));
	}

	public function inactiveUserList()
	{
		$users = User::where('status', 0)
			->with('profile')
			->latest()
			->paginate();
		return view('admin.user.inactive', compact('users'));
	}

	public function search(Request $request)
	{
		$search = $request->all();
		$created_date = isset($search['created_at']) ? preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']) : 0;
		$last_login_at = isset($search['last_login_at']) ? preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['last_login_at']) : 0;

		$active = isset($search['status']) ? preg_match("/active/", $search['status']) : 0;
		$inactive = isset($search['status']) ? preg_match("/inactive/", $search['status']) : 0;

		$users = User::with('profile')
			->when(isset($search['name']), function ($query) use ($search) {
				return $query->where('name', 'LIKE', "%{$search['name']}%");
			})
			->when(isset($search['email']), function ($query) use ($search) {
				return $query->where('email', 'LIKE', "%{$search['email']}%");
			})
			->when($active == 1, function ($query) use ($search) {
				return $query->where("status", 1);
			})
			->when($inactive == 1, function ($query) use ($search) {
				return $query->where("status", 0);
			})
			->when($created_date == 1, function ($query) use ($search) {
				return $query->whereDate("created_at", $search['created_at']);
			})
			->when($last_login_at == 1, function ($query) use ($search) {
				return $query->whereHas('profile', function ($qry) use ($search) {
					$qry->whereDate("last_login_at", $search['last_login_at']);
				});
			})
			->when(isset($search['phone']), function ($query) use ($search) {
				return $query->whereHas('profile', function ($qry) use ($search) {
					$qry->where('phone', 'LIKE', "%{$search['phone']}%");
				});
			})
			->latest()
			->paginate();
		$users->appends($search);
		return view('admin.user.index', compact('search', 'users'));
	}

	public function inactiveUserSearch(Request $request)
	{
		$search = $request->all();
		$created_date = isset($search['created_at']) ? preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']) : 0;
		$last_login_at = isset($search['last_login_at']) ? preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['last_login_at']) : 0;

		$active = isset($search['status']) ? preg_match("/active/", $search['status']) : 0;
		$inactive = isset($search['status']) ? preg_match("/inactive/", $search['status']) : 0;

		$users = User::where('status', 0)->with('profile')
			->when(isset($search['name']), function ($query) use ($search) {
				return $query->where('name', 'LIKE', "%{$search['name']}%");
			})
			->when(isset($search['email']), function ($query) use ($search) {
				return $query->where('email', 'LIKE', "%{$search['email']}%");
			})
			->when($active == 1, function ($query) use ($search) {
				return $query->where("status", 1);
			})
			->when($inactive == 1, function ($query) use ($search) {
				return $query->where("status", 0);
			})
			->when($created_date == 1, function ($query) use ($search) {
				return $query->whereDate("created_at", $search['created_at']);
			})
			->when($last_login_at == 1, function ($query) use ($search) {
				return $query->whereHas('profile', function ($qry) use ($search) {
					$qry->whereDate("last_login_at", $search['last_login_at']);
				});
			})
			->when(isset($search['phone']), function ($query) use ($search) {
				return $query->whereHas('profile', function ($qry) use ($search) {
					$qry->where('phone', 'LIKE', "%{$search['phone']}%");
				});
			})
			->latest()
			->paginate();
		$users->appends($search);
		return view('admin.user.inactive', compact('search', 'users'));
	}

	public function edit(Request $request, user $user)
	{
		$userProfile = UserProfile::firstOrCreate(['user_id' => $user->id]);
		$wallets = Wallet::where('user_id', $user->id)->with('currency')->get();
		$languages = Language::get();
		if ($request->isMethod('get')) {
			$userId = $user->id;
			$countries = config('country');

			/* Transaction count */
			$transferCount = Transfer::where(function ($query) use ($userId) {
				$query->where('sender_id', '=', $userId);
				$query->orWhere('receiver_id', '=', $userId);
			})->count();
			$requestMoneyCount = RequestMoney::where(function ($query) use ($userId) {
				$query->where('sender_id', '=', $userId);
				$query->orWhere('receiver_id', '=', $userId);
			})->count();
			$exchangeCount = Exchange::where(['user_id' => $userId])->count();
			$redeemCodeCount = RedeemCode::where(function ($query) use ($userId) {
				$query->where('sender_id', '=', $userId);
				$query->orWhere('receiver_id', '=', $userId);
			})->count();
			$escrowCount = Escrow::where(function ($query) use ($userId) {
				$query->where('sender_id', '=', $userId);
				$query->orWhere('receiver_id', '=', $userId);
			})->count();
			$voucherCount = Voucher::where(function ($query) use ($userId) {
				$query->where('sender_id', '=', $userId);
				$query->orWhere('receiver_id', '=', $userId);
			})->count();
			$invoiceCount = Invoice::where(function ($query) use ($userId) {
				$query->where('sender_id', '=', $userId);
			})->count();

			$addFundCount = Fund::where(['user_id' => $userId])->count();

			$payoutCount = Payout::where(['user_id' => $userId])->count();

			$transactionCount = [
				'transfer' => $transferCount,
				'requestMoney' => $requestMoneyCount,
				'exchange' => $exchangeCount,
				'redeemCode' => $redeemCodeCount,
				'escrow' => $escrowCount,
				'voucher' => $voucherCount,
				'invoice' => $invoiceCount,
				'fund' => $addFundCount,
				'payout' => $payoutCount
			];

			return view('admin.user.show', compact('user', 'userProfile', 'wallets', 'transactionCount', 'countries', 'languages'));
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());

			$validator = Validator::make($purifiedData, [
				'name' => 'required|min:3|max:100|string',
				'city' => 'nullable|min:3|max:32|string',
				'state' => 'nullable|min:3|max:32|string',
				'phone' => 'required|max:32',
				'address' => 'nullable|max:250',
				'password' => 'nullable|min:5|max:50',
				'username' => 'required|min:5|max:50|unique:users,username,' . $user->id,
				'email' => 'required|email|min:5|max:100|unique:users,email,' . $user->id,
				'language' => 'required|numeric|not_in:0'
			]);
			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}
			$purifiedData = (object)$purifiedData;
			$user->name = $purifiedData->name;
			$user->username = $purifiedData->username;
			$user->email = $purifiedData->email;
			$user->status = $purifiedData->status;
			$user->language_id = $purifiedData->language;
			$user->email_verification = $purifiedData->email_verification;
			$user->sms_verification = $purifiedData->sms_verification;
			$userProfile->city = $purifiedData->city;
			$userProfile->state = $purifiedData->state;
			$userProfile->phone = $purifiedData->phone;
			$userProfile->phone_code = $purifiedData->phone_code;
			$userProfile->address = $purifiedData->address;

			$request->whenFilled('password', function ($input) use ($user, $purifiedData) {
				$user->password = bcrypt($purifiedData->password);
			});

			$request->whenFilled('user_pin', function ($input) use ($user, $purifiedData) {
				$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
				$twoFactorSetting->security_pin = bcrypt($purifiedData->security_pin);
				$twoFactorSetting->save();

				$receivedUser = $user;
				$params = [
					'pin' => $purifiedData->security_pin,
				];

				$action = [
					"link" => '#',
					"icon" => "fa fa-money-bill-alt text-white"
				];

				$this->sendMailSms($receivedUser, 'SECURITY_PIN_RESET', $params);
				$this->userPushNotification($receivedUser, 'SECURITY_PIN_RESET', $params, $action);
				$this->userFirebasePushNotification($receivedUser, 'SECURITY_PIN_RESET', $params);
			});

			$request->whenFilled('security_answer', function ($input) use ($user, $purifiedData) {
				$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
				$twoFactorSetting->security_answer = $purifiedData->security_answer;
				$twoFactorSetting->save();

				$receivedUser = $user;
				$params = [
					'answer' => $purifiedData->security_answer,
				];

				$action = [
					"link" => '#',
					"icon" => "fa fa-money-bill-alt text-white"
				];

				$this->sendMailSms($receivedUser, 'SECURITY_ANSWER_RESET', $params);
				$this->userPushNotification($receivedUser, 'SECURITY_ANSWER_RESET', $params, $action);
				$this->userFirebasePushNotification($receivedUser, 'SECURITY_ANSWER_RESET', $params);
			});

			if ($request->file('profile_picture') && $request->file('profile_picture')->isValid()) {
				$extension = $request->profile_picture->extension();
				$profileName = strtolower($user->username . '.' . $extension);
				$userProfile->profile_picture = $this->uploadImage($request->profile_picture, config('location.user.path'), config('location.user.size'), $userProfile->profile_picture, $profileName);
			}

			$user->save();
			$userProfile->save();

			return back()->with('success', 'Profile Update Successfully');
		}
	}

	public function sendMailUser(Request $request, user $user = null)
	{
		if ($request->isMethod('get')) {
			return view('admin.user.sendMail', compact('user'));
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());
			$validator = Validator::make($purifiedData, [
				'subject' => 'required|min:5',
				'template' => 'required|min:10',
			]);

			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}

			$purifiedData = (object)$purifiedData;
			$subject = $purifiedData->subject;
			$template = $purifiedData->template;

			if (isset($user)) {
				$this->mail($user, null, [], $subject, $template);
			} else {
				$users = User::all();
				foreach ($users as $user) {
					$this->mail($user, null, [], $subject, $template);
				}
			}
			return redirect(route('user-list'))->with('success', 'Email Send Successfully');
		}
	}

	public function asLogin($id)
	{
		Auth::guard('web')->loginUsingId($id);

		return redirect()->route('user.dashboard');
	}

	public function qrPayment(Request $request)
	{
		$search = $request->all();
		$dateSearch = $request->datetrx;
		$date = preg_match("/^[0-9]{2,4}\-[0-9]{1,2}\-[0-9]{1,2}$/", $dateSearch);

		$data['gateways'] = Gateway::where('status', 1)->get();
		$data['qrPayments'] = QRCode::where('status', 1)
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
		return view('admin.qrPayment.index', $data);
	}

}
