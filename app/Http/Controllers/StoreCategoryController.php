<?php

namespace App\Http\Controllers;

use App\Models\StoreCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class StoreCategoryController extends Controller
{
	public function categoryList()
	{
		$data['categories'] = StoreCategory::own()->withCount('activeProducts')->orderBy('id', 'desc')->get();
		return view('user.store.category.index', $data);
	}

	public function categorySave(Request $request)
	{
		$purifiedData = Purify::clean($request->all());
		$validator = Validator::make($purifiedData, [
			'name' => 'required',
		]);
		if ($validator->fails()) {
			return back()->withErrors($validator)->withInput();
		}

		$storeCategory = new StoreCategory();
		$storeCategory->user_id = Auth::id();
		$storeCategory->name = $request->name;
		$storeCategory->status = $request->status;

		$storeCategory->save();
		session()->flash('success', 'Created Successfully');
		return response()->json([
			'status' => 'success',
			'url' => route('category.list')
		]);
	}

	public function categoryUpdate(Request $request)
	{
		$purifiedData = Purify::clean($request->all());
		$validator = Validator::make($purifiedData, [
			'name' => 'required',
		]);
		if ($validator->fails()) {
			return back()->withErrors($validator)->withInput();
		}

		$storeCategory = StoreCategory::own()->find($request->id);
		$storeCategory->name = $request->name;
		$storeCategory->status = $request->status;

		$storeCategory->save();
		session()->flash('success', 'Updated Successfully');
		return response()->json([
			'status' => 'success',
			'url' => route('category.list')
		]);
	}

	public function categoryDelete($id)
	{
		$storeCategory = StoreCategory::own()->with(['products'])->findOrFail($id);
		if (count($storeCategory->products) > 0) {
			return back()->with('alert', 'This category has lot of products');
		}
		$storeCategory->delete();
		return back()->with('success', 'Deleted Successfully');
	}
}
