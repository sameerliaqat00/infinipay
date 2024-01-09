<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Store;
use App\Models\StoreCategory;
use App\Models\StoreShipping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class StoreShippingController extends Controller
{
	public function shippingList()
	{
		$data['stores'] = Store::own()->orderBy('name', 'asc')->get();
		$data['shipping'] = StoreShipping::own()->withCount('orders')->orderBy('id', 'desc')->get();
		return view('user.store.shipping.index', $data);
	}

	public function shippingSave(Request $request)
	{
		$purifiedData = Purify::clean($request->all());
		$validator = Validator::make($purifiedData, [
			'store' => 'required',
			'address' => 'required',
			'charge' => 'required',
		]);
		if ($validator->fails()) {
			return back()->withErrors($validator)->withInput();
		}

		$storeShipping = new StoreShipping();
		$storeShipping->user_id = Auth::id();
		$storeShipping->store_id = $request->store;
		$storeShipping->address = $request->address;
		$storeShipping->charge = $request->charge;
		$storeShipping->status = $request->status;

		$storeShipping->save();
		session()->flash('success', 'Created Successfully');
		return response()->json([
			'status' => 'success',
			'url' => route('shipping.list')
		]);
	}

	public function shippingUpdate(Request $request)
	{
		$purifiedData = Purify::clean($request->all());
		$validator = Validator::make($purifiedData, [
			'store' => 'required',
			'address' => 'required',
			'charge' => 'required',
		]);
		if ($validator->fails()) {
			return back()->withErrors($validator)->withInput();
		}

		$storeShipping = StoreShipping::own()->find($request->id);
		$storeShipping->store_id = $request->store;
		$storeShipping->address = $request->address;
		$storeShipping->charge = $request->charge;
		$storeShipping->status = $request->status;

		$storeShipping->save();
		session()->flash('success', 'Updated Successfully');
		return response()->json([
			'status' => 'success',
			'url' => route('shipping.list')
		]);
	}

	public function shippingDelete($id)
	{
		$storeShipping = StoreShipping::own()->with(['orders'])->findOrFail($id);
		if (count($storeShipping->orders)) {
			return back()->with('error', 'shipping has lot of orders');
		}
		$storeShipping->delete();
		return back()->with('success', 'Deleted Successfully');
	}
}
