@extends('user.layouts.master')
@section('page_title',__('Product Stock View'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Product Stock View')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Product Stock View')</div>
				</div>
			</div>
			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row justify-content-md-center">
						<div class="col-md-12">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Product Stock View')</h6>
									<a href="{{route('stock.create')}}" target="_blank" class="btn btn-primary">@lang('Create Stock')</a>
								</div>
								<div class="card-body">
									<form>
										@csrf
										<div class="row">
											<div class="col-md-6">
												<a href="{{route('product.edit',$product->id)}}"
												   class="text-decoration-none">
													<div class="d-lg-flex d-block align-items-center ">
														<div class="mr-3"><img
																src="{{getFile(config('location.product.path').$product->thumbnail)}}"
																alt="user" class="rounded-circle"
																width="40" data-toggle="tooltip"
																title=""
																data-original-title="{{$product->name}}">
														</div>
														<div
															class="d-inline-flex d-lg-block align-items-center">
															<p class="text-dark mb-0 font-16 font-weight-medium">
																{{$product->name}}</p>
															<span
																class="text-muted font-14 ml-1">{{$product->sku}}</span>
														</div>
													</div>
												</a>
											</div>
										</div>
										@forelse($productStocks as $stock)
											<div class="row mt-3">
												@forelse($stock->product_attributes as $item)
													<div class="col-md-4">
														<labe>{{optional($item->attrName)->name}}</labe>
														<select class="form-control">
															<option>{{$item->name}}</option>
														</select>
													</div>
												@empty
												@endforelse
												<div class="col-md-4">
													<lable>@lang('Quantity')</lable>
													<input type="text" value="{{$stock->quantity}}" class="form-control"
														   readonly>
												</div>
											</div>
										@empty
										@endforelse
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

		</section>
	</div>
@endsection
@push('extra_styles')
	<link rel="stylesheet" href="{{ asset('assets/store/css/select2.min.css') }}">
@endpush
@section('scripts')
	<script src="{{ asset('assets/store/js/select2.min.js') }}"></script>
	<script>
		'use strict';

		var productId = '{{$product->id}}';
		attrCollect(productId);

		$(document).on('change', '.productSelect', function () {
			productId = $(this).val();
			attrCollect(productId);
		});


		$(document).on('click', '.delete_desc', function () {
			$(this).closest('.input-group').parent().remove();
		});

		function attrCollect(productId) {
			$('.addedField').html('');
			$.ajax({
				url: "{{ route('stock.edit.fetch') }}",
				method: "get",
				data: {
					productId: productId,
				},
				success: function (res) {
					if (res.status == 'success') {
						attrAppend(res.data);
					}
				}
			});
		}

		function attrAppend(data) {
			$('.addedField').append(data)
		}

	</script>
@endsection

