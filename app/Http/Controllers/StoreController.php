<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Traits\Notify;
use App\Traits\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class StoreController extends Controller
{
	use Notify, Upload;

	public function storeList()
	{
		$data['stores'] = Store::own()->withCount('productsMap')->orderBy('id', 'desc')->get();
		return view('user.store.storeList', $data);
	}

	public function storeCreate(Request $request)
	{
		if ($request->method() == 'GET') {
			return view('user.store.storeCreate');
		}
		if ($request->method() == 'POST') {
			$purifiedData = Purify::clean($request->all());
			$validator = Validator::make($purifiedData, [
				'name' => 'required',
				'shipping_charge' => 'required',
				'delivery_note' => 'required',
				'status' => 'required',
				'image' => 'required',
			]);
			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}
			if (auth()->user()->store_currency_id == null) {
				return back()->withInput()->with('alert', 'Please Set Currency From Setting Option');
			}
			$store = Store::where('link', $request->link)->exists();
			if ($store) {
				return back()->withInput()->with('alert', 'Link Already Exists');
			}

			if (preg_match('/' . preg_quote('/', '/') . '/', $request->link)) {
				return back()->withInput()->with('alert', '"/" can not be uses');
			}

			$store = new Store();
			$store->user_id = Auth::id();
			$store->name = $purifiedData['name'];
			$store->shipping_charge = $purifiedData['shipping_charge'];
			$store->status = $purifiedData['status'];
			$store->delivery_note = $purifiedData['delivery_note'];
			$store->image = $this->uploadImage($request->image, config('location.store.path'), config('location.store.size'));
			$store->short_description = $purifiedData['short_description'];
			$store->save();

			if ($request->link == null) {
				$data = $store->id . '|' . $store->name;
				$store->link = $this->encrypt($data);
			} else {
				$store->link = $purifiedData['link'];
			}

			$store->save();
			return redirect()->route('store.list')->with('success', 'Store Create Successfully');
		}
	}

	public function encrypt($data)
	{
		return implode(unpack("H*", $data));
	}

	public function storeEdit($id, Request $request)
	{
		$data['store'] = Store::own()->findOrFail($id);
		if ($request->method() == 'GET') {
			return view('user.store.storeEdit', $data);
		}
		if ($request->method() == 'POST') {
			$purifiedData = Purify::clean($request->all());
			$validator = Validator::make($purifiedData, [
				'name' => 'required',
				'shipping_charge' => 'required',
				'delivery_note' => 'required',
				'status' => 'required',
			]);
			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}
			$store = Store::where('link', $purifiedData['link'])->where('id', '!=', $data['store']->id)->exists();
			if ($store) {
				return back()->with('alert', 'Link Already Exists');
			}

			if (preg_match('/' . preg_quote('/', '/') . '/', $purifiedData['link'])) {
				return back()->withInput()->with('alert', '"/" can not be uses');
			}

			$data['store']->name = $purifiedData['name'];
			$data['store']->shipping_charge = $purifiedData['shipping_charge'];
			$data['store']->status = $purifiedData['status'];
			$data['store']->delivery_note = $purifiedData['delivery_note'];
			$data['store']->short_description = $purifiedData['short_description'];
			if ($request->image) {
				$data['store']->image = $this->uploadImage($request->image, config('location.store.path'), config('location.store.size'), $data['store']->image);
			}
			$data['store']->link = $purifiedData['link'];
			$data['store']->save();
			return back()->with('success', 'Updated Successfully');
		}

	}

	public function storeLinkCheck(Request $request)
	{
		if (preg_match('/' . preg_quote('/', '/') . '/', $request->link)) {
			return response()->json([
				'status' => 'success',
				'msg' => '"/" can not be uses'
			]);
		}
		if ($request->storeId == -1) {
			$store = Store::where('link', $request->link)->exists();
			if ($store) {
				return response()->json([
					'status' => 'success',
					'msg' => 'Link Already Exists'
				]);
			} else {
				return response()->json([
					'status' => 'notFound',
				]);
			}
		} else {

			$store = Store::where('link', $request->link)->where('id', '!=', $request->storeId)->exists();
			if ($store) {
				return response()->json([
					'status' => 'success',
					'msg' => 'Link Already Exists'
				]);
			} else {
				return response()->json([
					'status' => 'notFound',
				]);
			}

		}
	}

	public function storeDelete($id)
	{
		$store = Store::own()->with(['productsMap'])->findOrFail($id);
		if (count($store->productsMap) > 0) {
			return back()->with('alert', 'Store has lot of product');
		}
		$store->delete();
		return back()->with('success', 'Deleted Successfully');
	}
}
