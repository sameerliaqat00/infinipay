<?php

namespace App\Http\Controllers;

use App\Models\ProductAttrList;
use App\Models\ProductAttrMap;
use App\Models\StoreProduct;
use App\Models\StoreProductStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Stevebauman\Purify\Facades\Purify;

class StoreProductStockController extends Controller
{
	public function stockList()
	{
		$data['productStocks'] = StoreProductStock::own()->with(['product'])->latest()->groupBy('product_id')->get()->map(function ($item) {
			$q = StoreProductStock::where('product_id', $item->product_id)->sum('quantity');
			$item['sumQuantity'] = $q;
			return $item;
		});

		return view('user.store.productStock.stockList', $data);
	}

	public function stockCreate(Request $request)
	{
		if ($request->method() == "GET") {
			$data['products'] = StoreProduct::own()->where('status', 1)->get();
			return view('user.store.productStock.stockCreate', $data);
		}
		if ($request->method() == "POST") {
			$purifiedData = Purify::clean($request->all());
			$validator = Validator::make($purifiedData, [
				'product' => 'required',
				'attrName.*' => 'required',
				'quantity.*' => 'required',
			]);
			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}

			for ($i = 0; $i < count($request->quantity); $i++) {
				$data = [
					'user_id' => Auth::id(),
					'product_id' => $request->product,
					'product_attr_lists_id' => $request->attrName[$i],
					'quantity' => $request->quantity[$i],
				];

				$checkExit = StoreProductStock::where('product_id', $request->product)
					->where(function ($query) use ($request, $i) {
						foreach ($request->attrName[$i] as $key => $id) {
							$query->whereJsonContains('product_attr_lists_id', $id);
						}
					})->exists();
				if ($checkExit == false) {
					if ($request->quantity[$i] < 0) {
						return back()->with('alert', 'Quantity must be grater than 0');
					}
					StoreProductStock::create($data);
				} else {
					$newStock = StoreProductStock::where('product_id', $request->product)
						->where(function ($query) use ($request, $i) {
							foreach ($request->attrName[$i] as $key => $id) {
								$query->whereJsonContains('product_attr_lists_id', $id);
							}
						})->firstOrFail();
					$newQty = $newStock->quantity + $request->quantity[$i];
					if ($newQty < 0) {
						return back()->with('alert', 'Quantity must be grater than 0');
					}
					$newStock->quantity = $newQty;
					$newStock->save();
				}
			}
			return redirect()->route('stock.list')->with('success', 'Stock Added');
		}
	}

	public function stockAttrFetch(Request $request)
	{
		$productAttr = ProductAttrMap::with(['attribute.attrLists'])->where('product_id', $request->productId)->get();
		$dynamicForm = '';
		foreach ($productAttr as $k => $item) {
			$fieldLabel = optional($item->attribute)->name;
			$fieldName = Str::snake(optional($item->attribute)->name);
			$variants = optional($item->attribute)->attrLists ?? [];

			$options = '';
			if (!empty($variants)) {
				foreach ($variants as $variant) {
					$options .= '<option value="' . $variant->id . '">' . trans($variant->name) . '</option>';

				}
			}

			$dynamicForm .= '<div class="col-md-4 form-group">
                                                    <label>' . $fieldLabel . '</label>
                                                    <select name="attrName[0][]" class="form-control attrId" required>
                                                        ' . $options . '
                                                    </select>
                                                </div>';
		}


		$html = '<div class="col-md-12 column-form">
					 <div class="card card-primary shadow">
							<div class="card-header d-flex justify-content-between">
								<h5 class="card-title text-primary font-weight-bold">' . trans('Field information') . '</h3>
								<div class="d-flex justify-content-between">
								    <button  class="btn  btn-success copyFormData mr-3" type="button">
										<i class="fas fa-copy"></i>
									</button>
									<button  class="btn  btn-danger removeContentDiv" style="display: none" type="button">
										<i class="fa fa-times"></i>
									</button>
                                </div>

							</div>

							<div class="card-body">
								<div class="row">
									' . $dynamicForm . '

									<div class="col-md-4 form-group">
										<label>' . trans('Quantity') . '</label>
										<input name="quantity[]" class="form-control quantity" type="number" value="" required
											   placeholder="Quantity">
									</div>
								</div>
							</div>

						</div>
				</div>';

		return response()->json([
			'status' => 'success',
			'data' => $html
		]);
	}

	public function stockEdit(Request $request, $productId)
	{
		$productAttrList = collect();
		$data['product'] = StoreProduct::findOrFail($productId);
		$data['productStocks'] = StoreProductStock::own()->where('product_id', $productId)->get()->map(function ($item) use ($productAttrList) {
			$check = ProductAttrList::with(['attrName'])->whereIn('id', $item->product_attr_lists_id)->get();
			$productAttrList->push($check);

			$res['user_id'] = $item->user_id;
			$res['product_id'] = $item->product_id;
			$res['product_attr_lists_id'] = $item->product_attr_lists_id;
			$res['quantity'] = $item->quantity;
			$res['product_attributes'] = $check;
			return (object)$res;
		});


		return view('user.store.productStock.stockView', $data);
	}

}
