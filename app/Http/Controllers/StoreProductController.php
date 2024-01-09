<?php

namespace App\Http\Controllers;

use App\Models\ProductAttrMap;
use App\Models\ProductStoreMap;
use App\Models\Store;
use App\Models\StoreCategory;
use App\Models\StoreProduct;
use App\Models\StoreProductAttr;
use App\Models\StoreProductImage;
use App\Traits\Notify;
use App\Traits\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class StoreProductController extends Controller
{
	use Notify, Upload;

	public function productList(Request $request)
	{
		$search = $request->all();
		$data['categories'] = StoreCategory::own()->where('status', 1)->orderBy('name', 'asc')->get();
		$data['products'] = StoreProduct::own()->with(['category'])->orderBy('id', 'desc')
			->when(isset($search['name']), function ($query) use ($search) {
				$query->where('name', 'LIKE', '%' . $search['name'] . '%');
			})
			->when(isset($search['sku']), function ($query) use ($search) {
				$query->where('sku', 'LIKE', '%' . $search['sku'] . '%');
			})
			->when(isset($search['category_id']), function ($query) use ($search) {
				$query->where('category_id', $search['category_id']);
			})
			->when(isset($search['status']), function ($query) use ($search) {
				$query->where('status', $search['status']);
			})
			->paginate(config('basic.paginate'));
		return view('user.store.product.productList', $data);
	}

	public function productCreate(Request $request)
	{
		if ($request->method() == 'GET') {
			$data['stores'] = Store::own()->where('status', 1)->orderBy('name', 'asc')->get();
			$data['categories'] = StoreCategory::own()->where('status', 1)->orderBy('name', 'asc')->get();
			$data['productsAttrs'] = StoreProductAttr::own()->where('status', 1)->orderBy('name', 'asc')->get();
			return view('user.store.product.productCreate', $data);
		}
		if ($request->method() == 'POST') {
			$purifiedData = Purify::clean($request->all());
			$validator = Validator::make($purifiedData, [
				'store' => 'required',
				'category' => 'required',
				'name' => 'required',
				'price' => 'required|numeric',
				'sku' => 'required',
				'attribute' => 'required',
				'thumbnail' => 'required',
			]);
			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}

			DB::beginTransaction();
			try {
				$storeProduct = new StoreProduct();
				$storeProduct->user_id = Auth::id();
				$storeProduct->category_id = $purifiedData['category'];
				$storeProduct->name = $purifiedData['name'];
				$storeProduct->price = $purifiedData['price'];
				$storeProduct->sku = $purifiedData['sku'];
				$storeProduct->tag = $purifiedData['tag'];
				$storeProduct->status = $purifiedData['status'];
				$storeProduct->description = $purifiedData['description'];
				$storeProduct->instruction = $purifiedData['instruction'];
				if ($request->thumbnail) {
					$storeProduct->thumbnail = $this->uploadImage($request->thumbnail, config('location.product.path'), config('location.product.thumbnail'));
				}
				$storeProduct->save();

				if ($request->image) {
					for ($i = 0; $i < count($request->image); $i++) {
						$storeImage = new StoreProductImage();
						$storeImage->product_id = $storeProduct->id;
						$storeImage->image = $this->uploadImage($request->image[$i], config('location.product.path'), config('location.product.size'), '', '', config('location.product.thumb'));
						$storeImage->save();
					}
				}
				if ($request->store) {
					for ($i = 0; $i < count($request->store); $i++) {
						$productStoreMap = new ProductStoreMap();
						$productStoreMap->product_id = $storeProduct->id;
						$productStoreMap->store_id = $request->store[$i];
						$productStoreMap->save();
					}
				}
				if ($request->attribute) {
					for ($i = 0; $i < count($request->attribute); $i++) {
						$productAtrMap = new ProductAttrMap();
						$productAtrMap->product_id = $storeProduct->id;
						$productAtrMap->attributes_id = $request->attribute[$i];
						$productAtrMap->save();
					}
				}
				DB::commit();
				return back()->with('success', 'Product has been created');

			} catch (\Exception $e) {
				DB::rollBack();
				return back()->with('error', 'something went wrong');
			}
		}
	}

	public function productView($id)
	{
		$data['product'] = StoreProduct::own()->with(['productImages', 'productStores.store', 'productAttrs.attribute'])->findOrFail($id);
		return view('user.store.product.productView', $data);
	}

	public function productEdit($id, Request $request)
	{
		$data['product'] = StoreProduct::own()->with(['productImages', 'productStores.store', 'productAttrs.attribute'])->findOrFail($id);

		if ($request->method() == 'GET') {
			$data['stores'] = Store::own()->where('status', 1)->orderBy('name', 'asc')->get();
			$data['categories'] = StoreCategory::own()->where('status', 1)->orderBy('name', 'asc')->get();
			$data['productsAttrs'] = StoreProductAttr::own()->where('status', 1)->orderBy('name', 'asc')->get();
			return view('user.store.product.productEdit', $data);
		}
		if ($request->method() == 'POST') {
			$purifiedData = Purify::clean($request->all());
			$validator = Validator::make($purifiedData, [
				'store' => 'required',
				'category' => 'required',
				'name' => 'required',
				'price' => 'required|numeric',
				'sku' => 'required',
				'attribute' => 'required',
			]);
			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}
			$data['product']->category_id = $purifiedData['category'];
			$data['product']->name = $purifiedData['name'];
			$data['product']->price = $purifiedData['price'];
			$data['product']->sku = $purifiedData['sku'];
			$data['product']->tag = $purifiedData['tag'];
			$data['product']->status = $purifiedData['status'];
			$data['product']->description = $purifiedData['description'];
			$data['product']->instruction = $purifiedData['instruction'];
			if ($request->thumbnail) {
				$data['product']->thumbnail = $this->uploadImage($request->thumbnail, config('location.product.path'), config('location.product.thumbnail'), $data['product']->thumbnail);
			}
			$data['product']->save();

			if ($request->image) {
				for ($i = 0; $i < count($request->image); $i++) {
					$storeImage = new StoreProductImage();
					$storeImage->product_id = $data['product']->id;
					$storeImage->image = $this->uploadImage($request->image[$i], config('location.product.path'), config('location.product.size'), '', '', config('location.product.thumb'));
					$storeImage->save();
				}
			}
			if ($request->store) {
				$previousStore = ProductStoreMap::where('product_id', $data['product']->id)->get();
				foreach ($previousStore as $item) {
					$item->delete();
				}
				for ($i = 0; $i < count($request->store); $i++) {
					$productStoreMap = new ProductStoreMap();
					$productStoreMap->product_id = $data['product']->id;
					$productStoreMap->store_id = $request->store[$i];
					$productStoreMap->save();
				}
			}
			if ($request->attribute) {
				$previousAttr = ProductAttrMap::where('product_id', $data['product']->id)->get();
				foreach ($previousAttr as $item) {
					$item->delete();
				}
				for ($i = 0; $i < count($request->attribute); $i++) {
					$productAtrMap = new ProductAttrMap();
					$productAtrMap->product_id = $data['product']->id;
					$productAtrMap->attributes_id = $request->attribute[$i];
					$productAtrMap->save();
				}
			}

			return back()->with('success', 'Product Updated Successfully');
		}
	}

	public function productImageDelete($id)
	{
		$productImage = StoreProductImage::findOrFail($id);
		$old_images = $productImage;
		$location = config('location.product.path');

		if (!empty($old_images)) {
			@unlink($location . '/' . $old_images->image);
			@unlink($location . '/thumb_' . $old_images->image);
		}
		$productImage->delete();
		return back()->with('success', 'Product image has been deleted');
	}

	public function productDelete($id)
	{
		$product = StoreProduct::with(['orderDetails'])->findOrFail($id);
		if (count($product->orderDetails) > 0) {
			return back()->with('error', 'Product has lot of orders');
		}

		$previousStore = ProductStoreMap::where('product_id', $id)->get();
		foreach ($previousStore as $item) {
			$item->delete();
		}
		$previousAttr = ProductAttrMap::where('product_id', $id)->get();
		foreach ($previousAttr as $item) {
			$item->delete();
		}
		$productImage = StoreProductImage::where('product_id', $id)->get();
		$old_images = $productImage;
		$location = config('location.product.path');
		if (!empty($old_images)) {
			foreach ($old_images as $image) {
				@unlink($location . '/' . $image->image);
				@unlink($location . '/thumb_' . $image->image);
				$image->delete();
			}
		}
		$product->delete();
		return back()->with('success', 'Product Delete Successfully');
	}
}
