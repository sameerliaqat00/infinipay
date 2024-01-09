@extends('user.layouts.master')
@section('page_title', __('Order Details'))
@push('extra_styles')
	<link rel="stylesheet" href="{{ asset('assets/dashboard/css/dataTables.bootstrap4.min.css') }}">
@endpush
@section('content')
	<div>
		<div class="main-content">
			<section class="section">
				<div class="section-header">
					<h1>@lang('Order Details')</h1>
					<div class="section-header-breadcrumb">
						<div class="breadcrumb-item active">
							<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
						</div>
						<div class="breadcrumb-item">@lang('Order Details')</div>
					</div>
				</div>
				<div class="section-body">
					<div class="row mt-sm-4">
						<div class="col-12 col-md-12 col-lg-12">
							<div class="container-fluid" id="container-wrapper">
								<div class="row justify-content-md-center">
									<div class="col-lg-8 col-md-8">
										<div class="card mb-4 card-primary shadow">
											<div
												class="card-header py-3 d-flex flex-wrap flex-row align-items-center justify-content-between">
												<h6 class="m-0 font-weight-bold text-primary">@lang('Order Number')
													- #{{$order->order_number}}</h6>
												<div class="d-flex justify-content-end">
													<a href="{{route('order.list')}}"
													   class="btn btn-primary">@lang('Back')</a>
													<div class="dropdown ml-2 text-right">
														<button class="btn btn-sm  btn-dark dropdown-toggle"
																type="button"
																id="dropdownMenuButton"
																data-toggle="dropdown" aria-haspopup="true"
																aria-expanded="false">
															<span><i
																	class="fas fa-bars pr-2"></i> @lang('Action')</span>
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
															<th>@lang('SL.')</th>
															<th>@lang('Product Name')</th>
															<th>@lang('Attributes')</th>
															<th>@lang('Price')</th>
															<th>@lang('Quantity')</th>
															<th>@lang('Total Price')</th>
														</tr>
														</thead>
														<tbody>
														@foreach($orderDetails as $key => $item)
															<tr>
																<td data-label="@lang('SL.')">{{++$key}}</td>
																<td data-label="Product Name">
																	<a href="{{route('product.view',$item->product_id)}}"
																	   class="text-decoration-none">
																		<div
																			class="d-lg-flex d-block align-items-center ">
																			<div class="mr-3"><img
																					src="{{getFile(config('location.product.path').optional($item->product)->thumbnail)}}"
																					alt="user" class="rounded-circle"
																					width="40" data-toggle="tooltip"
																					title=""
																					data-original-title="{{ optional($item->product)->name }}">
																			</div>
																			<div
																				class="d-inline-flex d-lg-block align-items-center">
																				<p class="text-dark mb-0 font-16 font-weight-medium">
																					{{ optional($item->product)->name }}</p>
																				<span
																					class="text-muted font-14 ml-1">{{ optional($item->product)->sku }}</span>
																			</div>
																		</div>
																	</a>
																</td>
																<td data-label="@lang('Attributes')">@foreach($item->attr as $attr)
																		<span class="text-dark font-weight-bold">{{optional($attr->attrName)->name}}:{{$attr->name}}</span>
																	@endforeach</td>
																<td data-label="@lang('Price')">{{optional(auth()->user()->storeCurrency)->symbol}}{{ getAmount($item->price,2) }}</td>
																<td data-label="@lang('Quantity')">{{ $item->quantity }}</td>
																<td data-label="@lang('Total Price')"><span
																		class="text-dark font-weight-bold">{{optional(auth()->user()->storeCurrency)->symbol}}{{ getAmount($item->total_price,2) }}</span>
																</td>
															</tr>
														@endforeach
														</tbody>
													</table>
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-4">
										<div class="card mb-4 card-primary shadow">
											<div
												class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
												<h6 class="m-0 font-weight-bold text-primary">@lang('Buyer Details')</h6>
												@if($order->stage == '')
													<span
														class="badge badge-warning">@lang('New Arrival')</span>
												@elseif($order->stage == 1)
													<span
														class="badge badge-info">@lang('Processing')</span>
												@elseif($order->stage == 2)
													<span
														class="badge badge-dark">@lang('On Shipping')</span>
												@elseif($order->stage == 3)
													<span
														class="badge badge-primary">@lang('Out For Delivery')</span>
												@elseif($order->stage == 4)
													<span
														class="badge badge-success">@lang('Delivered')</span>
												@elseif($order->stage == 5)
													<span
														class="badge badge-danger">@lang('Cancel')</span>
												@endif
											</div>
											<div class="card-body">
												<li class="list-group-item d-flex justify-content-between">
													<span>{{ __('Date Time') }}</span>
													<span
														class="text-info">{{dateTime($order->created_at)??'N/A'}}</span>
												</li>
												<li class="list-group-item d-flex justify-content-between">
													<span>{{ __('Full Name') }}</span>
													<span class="text-info">{{$order->fullname??'N/A'}}</span>
												</li>
												<li class="list-group-item d-flex justify-content-between">
													<span>{{ __('Email') }}</span>
													<span class="text-info">{{$order->email??'N/A'}}</span>
												</li>
												<li class="list-group-item d-flex justify-content-between">
													<span>{{ __('Phone') }}</span>
													<span class="text-info">{{$order->phone??'N/A'}}</span>
												</li>
												<li class="list-group-item d-flex justify-content-between">
													<span>{{ __('Alt. Phone') }}</span>
													<span class="text-info">{{$order->alt_phone??'N/A'}}</span>
												</li>
												@if($order->shipping_id )
													<li class="list-group-item d-flex justify-content-between">
														<span>{{ __('Shipping Address') }}</span>
														<span
															class="text-info">{{optional($order->shipping)->address??'N/A'}}</span>
													</li>
												@endif
												<li class="list-group-item d-flex justify-content-between">
													<span>{{ __('Details Address') }}</span>
													<span class="text-info">{{$order->detailed_address??'N/A'}}</span>
												</li>
												<li class="list-group-item d-flex justify-content-between">
													<span>{{ __('Total Amount') }}</span>
													<span
														class="text-info">{{getAmount($order->total_amount,2)}} {{optional(auth()->user()->storeCurrency)->code}}</span>
												</li>
												@if($order->shipping_charge > 0)
													<li class="list-group-item d-flex justify-content-between">
														<span>{{ __('Shipping Charge') }}</span>
														<span
															class="text-danger">{{getAmount($order->shipping_charge,2)}} {{optional(auth()->user()->storeCurrency)->code}}</span>
													</li>
												@endif
												<li class="list-group-item d-flex justify-content-between">
													<span>{{ __('Total Amount') }}</span>
													<span
														class="text-dark font-weight-bold">{{getAmount($order->total_amount+$order->shipping_charge,2)}} {{optional(auth()->user()->storeCurrency)->code}}</span>
												</li>
												<li class="list-group-item d-flex justify-content-between">
													<span>{{ __('Order Note') }}</span>
													<span class="text-info">{{$order->order_note??'N/A'}}</span>
												</li>
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
	</div>
	<div id="stageChange" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
		 aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title text-dark font-weight-bold"
						id="primary-header-modalLabel">@lang('Stage Change Confirmation')</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				@csrf
				<div class="modal-body">
					<p>@lang('Are you want to change those orders stage?')</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
					<form action="{{route('single.stage.change',$order->id)}}" method="post">
						@csrf
						<input type="hidden" name="stage" class="stage" value="">
						<button type="submit" class="btn btn-primary change-yes">@lang('Yes')</button>
					</form>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('extra_scripts')
	<script src="{{ asset('assets/dashboard/js/jquery.dataTables.min.js') }}"></script>
	<script src="{{ asset('assets/dashboard/js/dataTables.bootstrap4.min.js') }}"></script>
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
	</script>
@endsection
