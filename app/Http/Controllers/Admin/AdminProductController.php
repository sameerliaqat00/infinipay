<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductOrder;
use App\Models\ProductOrderDetail;
use App\Models\SellerContact;
use App\Models\Store;
use App\Models\StoreCategory;
use App\Models\StoreProduct;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
	public function storeList()
	{
		$data['stores'] = Store::withCount('productsMap')->orderBy('id', 'desc')->get();
		return view('admin.store.storeList', $data);
	}

	public function storeView($id)
	{
		$data['store'] = Store::findOrFail($id);
		return view('admin.store.storeView', $data);
	}

	public function productList(Request $request)
	{
		$search = $request->all();
		$data['categories'] = StoreCategory::where('status', 1)->orderBy('name', 'asc')->get();
		$data['products'] = StoreProduct::with(['category', 'user'])->orderBy('id', 'desc')
			->when(isset($search['username']), function ($query) use ($search) {
				$query->whereHas('user', function ($qq) use ($search) {
					$qq->where('username', 'LIKE', '%' . $search['username'] . '%');
				});
			})
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
		return view('admin.store.product.productList', $data);
	}

	public function productView($id)
	{
		$data['product'] = StoreProduct::with(['productImages', 'productStores.store', 'productAttrs.attribute'])->findOrFail($id);
		return view('admin.store.product.productView', $data);
	}

	public function orderList(Request $request, $stage = null)
	{
		$search = $request->all();
		$data['orders'] = ProductOrder::with(['store', 'store.user.storeCurrency'])->where('status', 1)
			->when($stage == 'new-arrival', function ($query) {
				return $query->where("stage", null);
			})
			->when($stage == 'processing', function ($query) {
				return $query->where("stage", 1);
			})
			->when($stage == 'on-shipping', function ($query) {
				return $query->where("stage", 2);
			})
			->when($stage == 'out-for-delivery', function ($query) {
				return $query->where("stage", 3);
			})
			->when($stage == 'delivered', function ($query) {
				return $query->where("stage", 4);
			})
			->when($stage == 'cancel', function ($query) {
				return $query->where("stage", 5);
			})
			->when(isset($search['username']), function ($query) use ($search) {
				$query->whereHas('store.user', function ($qq) use ($search) {
					$qq->where('username', 'LIKE', '%' . $search['username'] . '%');
				});
			})
			->when(isset($search['orderNumber']), function ($query) use ($search) {
				return $query->where("order_number", 'LIKE', '%' . $search['orderNumber'] . '%');
			})
			->when(isset($search['email']), function ($query) use ($search) {
				return $query->where("email", 'LIKE', '%' . $search['email'] . '%');
			})
			->when(isset($search['amount']), function ($query) use ($search) {
				return $query->where("total_amount", 'LIKE', '%' . $search['amount'] . '%');
			})
			->when(isset($search['stage']), function ($query) use ($search) {
				return $query->where("stage", $search['stage']);
			})
			->orderBy('id', 'desc')->paginate(config('basic.paginate'));
		return view('admin.store.orderList', $data);
	}

	public function orderView($orderNumber)
	{
		$order = ProductOrder::where('status', 1)->where('order_number', $orderNumber)->firstOrFail();
		$data['orderDetails'] = ProductOrderDetail::with(['product', 'order'])->where('order_id', $order->id)->get();
		return view('admin.store.orderView', $data, compact('order'));
	}

	public function contactList(Request $request)
	{
		$search = $request->all();
		$data['stores'] = Store::get();
		$data['contactLists'] = SellerContact::with(['store', 'user'])
			->when(isset($search['username']), function ($query) use ($search) {
				$query->whereHas('user', function ($qq) use ($search) {
					$qq->where('username', 'LIKE', '%' . $search['username'] . '%');
				});
			})
			->when(isset($search['store_id']), function ($query) use ($search) {
				return $query->where("store_id", $search['store_id']);
			})
			->when(isset($search['sender_name']), function ($query) use ($search) {
				return $query->where("sender_name", 'LIKE', '%' . $search['sender_name'] . '%');
			})
			->orderBy('id', 'desc')->paginate(config('basic.paginate'));
		return view('admin.store.contactList', $data);
	}
}
