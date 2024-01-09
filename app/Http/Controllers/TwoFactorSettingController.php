<?php

namespace App\Http\Controllers;

use App\Models\SecurityQuestion;
use App\Models\TwoFactorSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Validator;

class TwoFactorSettingController extends Controller
{
	public function manage(Request $request)
	{
		$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => Auth::id()]);
		if (is_null($twoFactorSetting->security_pin)) {
			return redirect(route('securityPin.create'));
		}
		if ($request->isMethod('get')) {
			$data['enable_for'] = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);
			$data['twoFactorSetting'] = $twoFactorSetting;
			return view('user.twoFactor.manage', $data);
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());

			$validationRules = [
				'security_pin' => 'required|integer|digits:5',
			];
			$validate = Validator::make($purifiedData, $validationRules);

			if ($validate->fails()) {
				return back()->withErrors($validate)->withInput();
			}
			if (!Hash::check($purifiedData['security_pin'], $twoFactorSetting->security_pin)) {
				return back()->withErrors(['security_pin' => 'You have entered an incorrect PIN'])->with('alert', 'You have entered an incorrect PIN')->withInput();
			}

			$purifiedData = (object)$purifiedData;

			$enable_for = isset($purifiedData->enable_for) ? json_encode($purifiedData->enable_for) : '[]';
			$twoFactorSetting->enable_for = $enable_for;
			$twoFactorSetting->save();
			return redirect(route('securityPin.manage'))->with('success', 'Update Successfully');
		}
	}

	public function reset(Request $request)
	{
		$twoFactorSetting = TwoFactorSetting::with('securityQuestion')->firstOrCreate(['user_id' => Auth::id()]);

		if (is_null($twoFactorSetting->security_pin)) {
			return redirect(route('securityPin.create'));
		}

		if ($request->isMethod('get')) {
			$data['twoFactorSetting'] = $twoFactorSetting;
			return view('user.twoFactor.reset', $data);
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());
			$validationRules = [
				'answer' => 'required|min:1',
				'old_security_pin' => 'required|integer|digits:5',
				'security_pin' => 'required|confirmed|integer|digits:5',
			];
			$validate = Validator::make($purifiedData, $validationRules);
			if ($validate->fails()) {
				return back()->withErrors($validate)->withInput();
			}

			$purifiedData = (object)$purifiedData;
			if ($twoFactorSetting->answer !== $purifiedData->answer) {
				return redirect(route('securityPin.reset'))->with('alert', 'Security answer did not match')->withInput();
			} elseif (!Hash::check($purifiedData->old_security_pin, $twoFactorSetting->security_pin)) {
				return redirect(route('securityPin.reset'))->with('alert', 'Old pin did not match')->withInput();
			} else {
				$twoFactorSetting->security_pin = bcrypt($purifiedData->security_pin);
				$twoFactorSetting->save();
				return redirect(route('securityPin.reset'))->with('success', 'PIN reset successfully');
			}
		}
	}

	public function create()
	{
		$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => Auth::id()]);
		if (isset($twoFactorSetting->security_pin)) {
			return redirect(route('securityPin.reset'));
		}
		$data['securityQuestions'] = SecurityQuestion::all();

		return view('user.twoFactor.create', $data);
	}

	public function store(Request $request)
	{
		$purifiedData = Purify::clean($request->all());

		$validationRules = [
			'security_question' => 'required|integer|min:1|not_in:0|exists:security_questions,id',
			'answer' => 'required|min:1',
			'hints' => 'required|min:1',
			'security_pin' => 'required|confirmed|integer|digits:5',
		];

		$validate = Validator::make($purifiedData, $validationRules);
		if ($validate->fails()) {
			return back()->withErrors($validate)->withInput();
		}

		$purifiedData = (object)$purifiedData;
		$twoFactorSetting = TwoFactorSetting::where('user_id', Auth::id())->first();
		$twoFactorSetting->security_question_id = $purifiedData->security_question;
		$twoFactorSetting->answer = $purifiedData->answer;
		$twoFactorSetting->hints = $purifiedData->hints;
		$twoFactorSetting->security_pin = bcrypt($purifiedData->security_pin);
		$twoFactorSetting->save();

		return redirect(route('securityPin.reset'))->with('success', 'Security PIN Saved Successfully');
	}
}
