@extends('user.layouts.master')
@section('page_title', __('Orders List'))
@section('content')
	<div class="main-content" id="store" v-cloak>
		<section class="section">
			<div class="section-header">
				<h1>@lang('Orders List')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Orders List')</div>
				</div>
			</div>
			<div class="section-body">
				<div class="row mt-sm-4">
					<div class="col-12 col-md-12 col-lg-12">
						<div class="container-fluid" id="container-wrapper">
							<div class="row">
								<div class="col-lg-12">
									<div class="card mb-4 card-primary shadow-sm">
										<div
											class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
											<h6 class="m-0 font-weight-bold text-primary">@lang('Search')</h6>
										</div>
										<div class="card-body">
											<form action="{{ route('order.list') }}" method="get">
												@include('user.store.orderSearchForm')
											</form>
										</div>
									</div>
								</div>
							</div>
							<div class="row justify-content-md-center">
								<div class="col-lg-12">
									<div class="card mb-4 card-primary shadow">
										<div
											class="card-header py-3 d-flex flex-wrap flex-row align-items-center justify-content-between">
											<h6 class="m-0 font-weight-bold text-primary">@lang('Orders List')</h6>
											<div
												class="d-flex flex-wrap flex-row align-items-center justify-content-between">
												<a href="{{route('order.list','new-arrival')}}"
												   class="mr-2 btn btn-sm btn-round btn-outline-warning">@lang('New Arrival')</a>
												<a href="{{route('order.list','processing')}}"
												   class="mr-2 btn btn-sm btn-round btn-outline-info">@lang('Processing')</a>
												<a href="{{route('order.list','on-shipping')}}"
												   class="mr-2 btn btn-sm btn-round btn-outline-dark">@lang('On Shipping')</a>
												<a href="{{route('order.list','out-for-delivery')}}"
												   class="mr-2 btn btn-sm btn-round btn-outline-primary">@lang('Out For Delivery')</a>
												<a href="{{route('order.list','delivered')}}"
												   class="mr-2 btn btn-sm btn-round btn-outline-success">@lang('Delivered')</a>
												<a href="{{route('order.list','cancel')}}"
												   class="mr-2 btn btn-sm btn-round btn-outline-danger">@lang('Cancel')</a>
												<div class="dropdown mb-2 text-right">
													<button class="btn btn-sm  btn-dark dropdown-toggle" type="button"
															id="dropdownMenuButton"
															data-toggle="dropdown" aria-haspopup="true"
															aria-expanded="false">
														<span><i class="fas fa-bars pr-2"></i> @lang('Action')</span>
													</button>
													<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
														<button class="dropdown-item processing" type="button"
																data-toggle="modal"
																data-target="#stageChange">@lang('Processing')</button>
														<button class="dropdown-item on-shipping" type="button"
																data-toggle="modal"
																data-target="#stageChange">@lang('On Shipping')</button>
														<button class="dropdown-item out-for-delivery" type="button"
																data-toggle="modal"
																data-target="#stageChange">@lang('Out For Delivery')</button>
														<button class="dropdown-item delivered" type="button"
																data-toggle="modal"
																data-target="#stageChange">@lang('Delivered')</button>
														<button class="dropdown-item cancel" type="button"
																data-toggle="modal"
																data-target="#stageChange">@lang('Cancel')</button>
													</div>
												</div>
											</div>
										</div>
										<div class="card-body">
											<div class="table-responsive">
												<table
													class="table table-striped table-hover align-items-center table-flush"
													id="data-table">
													<thead class="thead-light">
													<tr>
														<th scope="col" class="text-center">
															<input type="checkbox"
																   class="form-check-input check-all tic-check"
																   name="check-all"
																   id="check-all">
															<label for="check-all"></label>
														</th>
														<th>@lang('Date Time')</th>
														<th>@lang('#Order Number')</th>
														<th>@lang('Store')</th>
														<th>@lang('Email')</th>
														<th>@lang('Amount')</th>
														<th>@lang('Shipping Charge')</th>
														<th>@lang('Gateway')</th>
														<th>@lang('Stage')</th>
														<th>@lang('Action')</th>
													</tr>
													</thead>
													<tbody>
													@foreach($orders as $key => $item)
														<tr>
															<td class="text-center">
																<input type="checkbox" id="chk-{{ $item->id }}"
																	   class="form-check-input row-tic tic-check"
																	   name="check" value="{{$item->id}}"
																	   data-id="{{ $item->id }}">
																<label for="chk-{{ $item->id }}"></label>
															</td>
															<td data-label="@lang('Date Time')">
																{{dateTime($item->created_at)}}</td>
															<td data-label="@lang('Order Number')">
																#{{$item->order_number}}</td>
															<td data-label="Store">
																<a href="{{route('store.edit',$item->store_id)}}"
																   class="text-decoration-none">
																	<div
																		class="d-inline-flex d-lg-block align-items-center">
																		<p class="text-dark mb-0 font-16 font-weight-medium">
																			{{optional($item->store)->name}}</p>
																	</div>
																</a>
															</td>
															<td data-label="@lang('Email')">{{$item->email}}</td>
															<td data-label="@lang('Amount')"><span
																	class="text-dark font-weight-bold">{{optional(auth()->user()->storeCurrency)->symbol}}{{getAmount($item->total_amount,2)}}</span>
															</td>
															<td data-label="@lang('Shipping Charge')"><span
																	class="text-danger">{{optional(auth()->user()->storeCurrency)->symbol}}{{getAmount($item->shipping_charge)}}</span>
															</td>
															<td data-label="@lang('Gateway')">{{$item->gateway->name}}</td>
															<td data-label="@lang('Stage')">
																@if($item->stage == '')
																	<span
																		class="badge badge-warning">@lang('New Arrival')</span>
																@elseif($item->stage == 1)
																	<span
																		class="badge badge-info">@lang('Processing')</span>
																@elseif($item->stage == 2)
																	<span
																		class="badge badge-dark">@lang('On Shipping')</span>
																@elseif($item->stage == 3)
																	<span
																		class="badge badge-primary">@lang('Out For Delivery')</span>
																@elseif($item->stage == 4)
																	<span
																		class="badge badge-success">@lang('Delivered')</span>
																@elseif($item->stage == 5)
																	<span
																		class="badge badge-danger">@lang('Cancel')</span>
																@endif
															</td>
															<td data-label="@lang('Action')">
																<a href="{{route('order.view',$item->order_number)}}"
																   class="btn btn-outline-primary btn-sm mr-2"
																   title="@lang('view details')"><i
																		class="fa fa-eye"></i></a>
															</td>
														</tr>
													@endforeach
													</tbody>
												</table>
											</div>
											<div class="card-footer">
												{{ $orders->links() }}
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
	@include('user.store.modal')
@endsection

@push('extra_scripts')

@endpush
@section('scripts')
	<script>
		'use strict'
		$(document).on('click', '.processing', function () {
			$('.stage').val('processing');
		});
		$(document).on('click', '.on-shipping', function () {
			$('.stage').val('on-shipping');
		});
		$(document).on('click', '.out-for-delivery', function () {
			$('.stage').val('out-for-delivery');
		});
		$(document).on('click', '.delivered', function () {
			$('.stage').val('delivered');
		});
		$(document).on('click', '.cancel', function () {
			$('.stage').val('cancel');
		});
		$(document).on('click', '#check-all', function () {
			$('input:checkbox').not(this).prop('checked', this.checked);
		});

		$(document).on('change', ".row-tic", function () {
			let length = $(".row-tic").length;
			let checkedLength = $(".row-tic:checked").length;
			if (length == checkedLength) {
				$('#check-all').prop('checked', true);
			} else {
				$('#check-all').prop('checked', false);
			}
		});

		$(document).on('click', '.change-yes', function (e) {
			var stage = $('.stage').val();

			e.preventDefault();
			var allVals = [];
			$(".row-tic:checked").each(function () {
				allVals.push($(this).attr('data-id'));
			});

			var strIds = allVals;
			var stage = stage;

			$.ajax({
				headers: {'X-CSRF-TOKEN': $('meta[name="csrf_token"]').attr('content')},
				url: "{{ route('order.stage.change') }}",
				data: {strIds: strIds, stage: stage},
				datatType: 'json',
				type: "post",
				success: function (data) {
					location.reload();
				},
			});
		});

	</script>
	@if ($errors->any())
		@php
			$collection = collect($errors->all());
			$errors = $collection->unique();
		@endphp
		<script>
			"use strict";
			@foreach ($errors as $error)
			Notiflix.Notify.Failure("{{trans($error)}}");
			@endforeach
		</script>
	@endif
@endsection
