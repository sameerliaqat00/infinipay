<?php

namespace App\Http\Controllers;

use App\Models\Gateway;
use App\Traits\Upload;
use Illuminate\Http\Request;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Validator;

class PaymentMethodController extends Controller
{
	use Upload;

	public function index()
	{
		$methods = Gateway::orderBy('sort_by', 'ASC')->get();
		return view('admin.payment_methods.index', compact('methods'));
	}

	public function sortPaymentMethods(Request $request)
	{
		$sortItems = $request->sort;

		foreach ($sortItems as $key => $value) {
			Gateway::where('code', $value)->update(['sort_by' => $key + 1]);
		}
	}

	public function edit($id)
	{
		$method = Gateway::findOrFail($id);
		return view('admin.payment_methods.edit', compact('method'));
	}

	public function update(Request $request, $id)
	{
		$rules = [
			'currency' => 'required|string',
			'currency_symbol' => 'required|string',
			'environment' => 'sometimes|required|string|in:test,live',
			'status' => 'sometimes|required|min:0|in:0,1',
		];

		$gateway = Gateway::where('id', $id)->firstOr(function () {
			throw new \Exception('No payment method found.');
		});

		$validationRules = [
			'currency' => 'required',
			'currency_symbol' => 'required',
		];

		$parameters = [];
		foreach ($request->except('_token', '_method', 'image') as $k => $v) {
			foreach ($gateway->parameters as $key => $cus) {
				if ($k != $key) {
					continue;
				} else {
					$rules[$key] = 'required|max:191';
					$parameters[$key] = $v;
				}
			}
		}

		$purifiedData = Purify::clean($request->all());
		$purifiedData['image'] = $request->image;
		$validate = Validator::make($purifiedData, $validationRules);
		if ($validate->fails()) {
			return back()->withErrors($validate)->withInput();
		}
		$purifiedData = (object)$purifiedData;
		if ($request->hasFile('image')) {
			try {
				$old = $gateway->image ?: null;
				$image = $this->uploadImage($request->image, config('location.gateway.path'), config('location.gateway.size'), $old);
			} catch (\Exception $exp) {
				return back()->with('alert', 'Image could not be uploaded.');
			}
		}

		try {
			$response = $gateway->update([
				'currency' => $purifiedData->currency,
				'symbol' => $purifiedData->currency_symbol,
				'parameters' => $parameters,
				'image' => $image ?? $gateway->image,
				'environment' => $purifiedData->environment ?? null,
				'status' => $purifiedData->status
			]);

			if (!$response) {
				throw new \Exception('Unexpected error! Please try again.');
			}
			return back()->with('success', 'Gateway data has been updated.');
		} catch (\Exception $exception) {
			return back()->with('alert', $exception->getMessage());
		}
	}

}
