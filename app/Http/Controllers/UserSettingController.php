<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\NotifyTemplate;
use App\Models\TwoFactorSetting;
use Illuminate\Http\Request;
use Illuminate\Queue\RedisQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class UserSettingController extends Controller
{
	public function index(Request $request)
	{
		if ($request->method() == 'GET') {
			$data['currencies'] = Currency::where('is_active', 1)->orderBy('name', 'asc')->get();
			return view('user.setting.index', $data);
		}
		if ($request->method() == 'POST') {
			$user = Auth::user();
			$user->store_currency_id = $request->currency;
			$user->qr_currency_id = $request->qr_currency_id;
			$user->save();

			return back()->with('success', 'Updated Successfully');
		}
	}

	public function apiKey(Request $request)
	{
		$user = auth()->user();
		if ($request->method() == 'GET') {
			$public_key = $user->public_key;
			$secret_key = $user->secret_key;
			if (!$public_key || !$secret_key) {
				$user->public_key = bin2hex(random_bytes(20));
				$user->secret_key = bin2hex(random_bytes(20));
				$user->save();
			}
			return view('user.api.index', compact('user'));
		}
		if ($request->method() == 'POST') {
			$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
			$purifiedData = Purify::clean($request->all());
			$rules['security_pin'] = 'required|integer|digits:5';
			$validate = Validator::make($request->all(), $rules);
			if ($validate->fails()) {
				return back()->withErrors($validate)->withInput();
			}
			if (!Hash::check($purifiedData['security_pin'], $twoFactorSetting->security_pin)) {
				return back()->withErrors(['security_pin' => 'You have entered an incorrect PIN'])->withInput();
			}

			$user->public_key = bin2hex(random_bytes(20));
			$user->secret_key = bin2hex(random_bytes(20));
			$user->save();
			return back()->with('success', 'Api key generated successfully');
		}
	}

	public function modeChange($mode)
	{
		$user = auth()->user();
		if ($mode == 'test') {
			$user->mode = 0;
			$user->save();
			return redirect()->route('user.api.key')->with('success', 'Mode Updated');
		} else {
			$user->mode = 1;
			$user->save();
			return redirect()->route('user.api.key')->with('success', 'Mode Updated');
		}
	}

	public function apiDocx()
	{
		return view('user.api.docx');
	}

	public function settingNotify()
	{
		$data['templates'] = NotifyTemplate::where('firebase_notify_status', 1)->where('notify_for', 0)->get()->unique('template_key');
		return view('user.setting.notifyTemplate', $data);
	}

	public function settingNotifyUpdate(Request $request)
	{

		$user = auth()->user();
		$user->notify_active_template = $request->access;
		$user->save();

		session()->flash('success', 'Updated Successfully');
		return back();
	}
}
