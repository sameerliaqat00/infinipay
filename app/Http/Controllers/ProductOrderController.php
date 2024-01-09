<?php

namespace App\Http\Controllers;

use App\Models\ProductOrder;
use App\Models\ProductOrderDetail;
use App\Models\Store;
use Illuminate\Http\Request;

class ProductOrderController extends Controller
{
	public function orderList(Request $request, $stage = null)
	{
		$search = $request->all();
		$dateSearch = $request->datetrx;
		$date = preg_match("/^[0-9]{2,4}\-[0-9]{1,2}\-[0-9]{1,2}$/", $dateSearch);

		$storeId = array();
		$stores = Store::where('user_id', auth()->id())->get();
		if ($stores) {
			foreach ($stores as $store) {
				$storeId[] = $store->id;
			}
		}
		$data['orders'] = ProductOrder::whereIn('store_id', $storeId)->where('status', 1)
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
			->when($date == 1, function ($query) use ($dateSearch) {
				return $query->whereDate("created_at", $dateSearch);
			})
			->orderBy('id', 'desc')->paginate(config('basic.paginate'));
		return view('user.store.orderList', $data);
	}

	public function orderView($orderNumber)
	{
		$order = ProductOrder::where('status', 1)->where('user_id', auth()->id())->where('order_number', $orderNumber)->firstOrFail();
		$data['orderDetails'] = ProductOrderDetail::with(['product'])->where('order_id', $order->id)->get();
		return view('user.store.orderView', $data, compact('order'));
	}

	public function stageChange(Request $request)
	{
		$user_id = auth()->id();
		if ($request->strIds == null) {
			session()->flash('error', 'You do not select ID.');
			return response()->json(['error' => 1]);
		} else {
			if ($request->stage == 'processing') {
				ProductOrder::where('user_id', $user_id)->whereIn('id', $request->strIds)->update([
					'stage' => 1,
				]);
				session()->flash('success', 'Stage Has Been Updated');
				return response()->json(['success' => 1]);
			}
			if ($request->stage == 'on-shipping') {
				ProductOrder::where('user_id', $user_id)->whereIn('id', $request->strIds)->update([
					'stage' => 2,
				]);
				session()->flash('success', 'Stage Has Been Updated');
				return response()->json(['success' => 1]);
			}
			if ($request->stage == 'out-for-delivery') {
				ProductOrder::where('user_id', $user_id)->whereIn('id', $request->strIds)->update([
					'stage' => 3,
				]);
				session()->flash('success', 'Stage Has Been Updated');
				return response()->json(['success' => 1]);
			}
			if ($request->stage == 'delivered') {
				ProductOrder::where('user_id', $user_id)->whereIn('id', $request->strIds)->update([
					'stage' => 4,
				]);
				session()->flash('success', 'Stage Has Been Updated');
				return response()->json(['success' => 1]);
			}
			if ($request->stage == 'cancel') {
				$productOrders = ProductOrder::where('user_id', $user_id)->where('user_id')->whereIn('id', $request->strIds)->get();
				foreach ($productOrders as $productOrder) {
					$productOrder->cancel_from = $productOrder->stage;
					$productOrder->save();
				}
				ProductOrder::where('user_id', $user_id)->whereIn('id', $request->strIds)->update([
					'stage' => 5,
				]);
				session()->flash('success', 'Stage Has Been Updated');
				return response()->json(['success' => 1]);
			}
		}
	}

	public function singleStageChange(Request $request, $orderId)
	{
		$user_id = auth()->id();
		if ($request->stage == 'processing') {
			ProductOrder::where('user_id', $user_id)->where('id', $orderId)->update([
				'stage' => 1,
			]);
			session()->flash('success', 'Stage Has Been Updated');
			return back();
		}
		if ($request->stage == 'on-shipping') {
			ProductOrder::where('user_id', $user_id)->where('id', $orderId)->update([
				'stage' => 2,
			]);
			session()->flash('success', 'Stage Has Been Updated');
			return back();
		}
		if ($request->stage == 'out-for-delivery') {
			ProductOrder::where('user_id', $user_id)->where('id', $orderId)->update([
				'stage' => 3,
			]);
			session()->flash('success', 'Stage Has Been Updated');
			return back();
		}
		if ($request->stage == 'delivered') {
			ProductOrder::where('user_id', $user_id)->where('id', $orderId)->update([
				'stage' => 4,
			]);
			session()->flash('success', 'Stage Has Been Updated');
			return back();
		}
		if ($request->stage == 'cancel') {
			$productOrder = ProductOrder::where('user_id', $user_id)->where('id', $orderId)->firstOrFail();

			$productOrder->cancel_from = $productOrder->stage;
			$productOrder->save();

			ProductOrder::where('user_id', $user_id)->where('id', $orderId)->update([
				'stage' => 5,
			]);
			session()->flash('success', 'Stage Has Been Updated');
			return back();
		}
	}
}
