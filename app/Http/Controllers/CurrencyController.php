<?php

namespace App\Http\Controllers;

use App\Models\ChargesLimit;
use App\Models\Currency;
use App\Models\Gateway;
use Illuminate\Http\Request;
use Stevebauman\Purify\Facades\Purify;
use Image;
use Illuminate\Support\Facades\Validator;

class CurrencyController extends Controller
{
	public function index()
	{
		$currencies = Currency::all();
		return view('admin.currency.index', compact('currencies'));
	}

	public function create()
	{
		return view('admin.currency.create');
	}

	public function store(Request $request)
	{
		$purifiedData = Purify::clean($request->all());
		$purifiedData['logo'] = $request->logo;
		$validationRules = [
			'name' => 'required|min:2|max:100',
			'exchange_rate' => 'required|numeric|not_in:0',
			'symbol' => 'required|unique:currencies',
			'code' => 'required|unique:currencies',
			'currency_type' => 'required|numeric:not_in:0',
			'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
		];

		$validate = Validator::make($purifiedData, $validationRules);
		if ($validate->fails()) {
			return back()->withErrors($validate)->withInput();
		}

		$purifiedData = (object)$purifiedData;
		$currency = new Currency();
		$currency->name = $purifiedData->name;
		$currency->symbol = $purifiedData->symbol;
		$currency->is_active = isset($purifiedData->is_active);
		$currency->code = $purifiedData->code;
		$currency->exchange_rate = $purifiedData->exchange_rate;
		$currency->currency_type = $purifiedData->currency_type;
		if ($request->file('logo') && $request->file('logo')->isValid()) {
			$extension = $request->logo->extension();
			$logoName = strtolower($purifiedData->code . '.' . $extension);
			$storedPath = base_path('assets/upload/currencyLogo/') . $logoName;
			$imageMake = Image::make($purifiedData->logo);
			$imageMake->fit(200);
			$imageMake->save($storedPath);
			$currency->logo = $logoName;
		}
		$currency->save();

		ChargesLimit::firstOrCreate(['currency_id' => $currency->id, 'transaction_type_id' => 1]);
		ChargesLimit::firstOrCreate(['currency_id' => $currency->id, 'transaction_type_id' => 2]);
		ChargesLimit::firstOrCreate(['currency_id' => $currency->id, 'transaction_type_id' => 3]);

		return redirect(route('currency.index'))->with('success', 'Currency Successfully Saved');
	}

	public function edit(currency $currency)
	{
		return view('admin.currency.edit', compact('currency'));
	}

	public function update(Request $request, currency $currency)
	{
		$purifiedData = Purify::clean($request->all());
		if ($request->file('logo')) {
			$purifiedData['logo'] = $request->logo;
		}
		$validationRules = [
			'name' => 'required',
			'exchange_rate' => 'required',
			'symbol' => 'required|unique:currencies,symbol,' . $currency->id,
			'code' => 'required|unique:currencies,code,' . $currency->id,
			'currency_type' => 'required|numeric:not_in:0',
			'logo' => 'nullable|mimes:jpg,jpeg,png|max:10240',
		];

		$validate = Validator::make($purifiedData, $validationRules);
		if ($validate->fails()) {
			return back()->withErrors($validate)->withInput();
		}

		$purifiedData = (object)$purifiedData;
		$currency->name = $purifiedData->name;
		$currency->symbol = $purifiedData->symbol;
		$currency->is_active = $purifiedData->is_active;
		$currency->code = $purifiedData->code;
		$currency->exchange_rate = $purifiedData->exchange_rate;
		$currency->currency_type = $purifiedData->currency_type;
		if ($request->file('logo') && $request->file('logo')->isValid()) {
			$extension = $request->logo->extension();
			$logoName = strtolower($purifiedData->code . '.' . $extension);
			$storedPath = 'assets/upload/currencyLogo/' . $logoName;
			$imageMake = Image::make($purifiedData->logo);
			$imageMake->fit(300);
			$imageMake->save($storedPath);
			$currency->logo = $logoName;
		}
		$currency->save();

		return redirect(route('currency.index'))->with('success', 'Currency Successfully Saved');
	}
}
