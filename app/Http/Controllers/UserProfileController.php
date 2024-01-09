<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\UserProfile;
use App\Models\Wallet;
use App\Traits\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Validator;

class UserProfileController extends Controller
{
	use Upload;

	public function changePassword(Request $request)
	{
		if ($request->isMethod('get')) {
			return view('auth.passwords.change');
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());
			$validator = Validator::make($purifiedData, [
				'currentPassword' => 'required|min:5',
				'password' => 'required|min:8|confirmed',
			]);

			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}
			$user = Auth::user();
			$purifiedData = (object)$purifiedData;

			if (!Hash::check($purifiedData->currentPassword, $user->password)) {
				return back()->withInput()->withErrors(['currentPassword' => 'current password did not match']);
			}

			$user->password = bcrypt($purifiedData->password);
			$user->save();
			return back()->with('success', 'Password changed successfully');
		}
	}

	public function index(Request $request)
	{
		$user = Auth::user();
		$userProfile = UserProfile::firstOrCreate(['user_id' => $user->id]);
		$wallets = Wallet::where('user_id', $user->id)->with('currency')->get();
		$countries = config('country');
		if ($request->isMethod('get')) {
            $languages = Language::select('id', 'name')->where('is_active', true)->orderBy('name', 'ASC')->get();
			return view('user.profile.show', compact('user', 'userProfile', 'countries', 'wallets', 'languages'));
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());

			$validator = Validator::make($purifiedData, [
				'name' => 'required|min:3|max:100|string',
				'city' => 'required|min:3|max:32|string',
				'state' => 'required|min:3|max:32|string',
                'language' => 'required|integer|not_in:0|exists:languages,id',
				'phone' => 'required|max:32',
				'address' => 'nullable|max:250',
			]);
			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}
			$purifiedData = (object)$purifiedData;

			$user->name = $purifiedData->name;
			$userProfile->city = $purifiedData->city;
			$userProfile->state = $purifiedData->state;
			$userProfile->phone = $purifiedData->phone;
			$userProfile->phone_code = $purifiedData->phone_code;
			$userProfile->address = $purifiedData->address;
			$userProfile->about_me = $purifiedData->about_me;
            $user->language_id = $purifiedData->language;

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
}
