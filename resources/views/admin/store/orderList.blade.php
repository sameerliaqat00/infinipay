@extends('admin.layouts.master')
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
											<form action="{{ route('admin.order.list') }}" method="get">
												@include('admin.store.orderSearchForm')
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
												<a href="{{route('admin.order.list','new-arrival')}}"
												   class="mr-2 btn btn-sm btn-round btn-outline-warning">@lang('New Arrival')</a>
												<a href="{{route('admin.order.list','processing')}}"
												   class="mr-2 btn btn-sm btn-round btn-outline-info">@lang('Processing')</a>
												<a href="{{route('admin.order.list','on-shipping')}}"
												   class="mr-2 btn btn-sm btn-round btn-outline-dark">@lang('On Shipping')</a>
												<a href="{{route('admin.order.list','out-for-delivery')}}"
												   class="mr-2 btn btn-sm btn-round btn-outline-primary">@lang('Out For Delivery')</a>
												<a href="{{route('admin.order.list','delivered')}}"
												   class="mr-2 btn btn-sm btn-round btn-outline-success">@lang('Delivered')</a>
												<a href="{{route('admin.order.list','cancel')}}"
												   class="mr-2 btn btn-sm btn-round btn-outline-danger">@lang('Cancel')</a>
											</div>
										</div>
										<div class="card-body">
											<div class="table-responsive">
												<table
													class="table table-striped table-hover align-items-center table-flush"
													id="data-table">
													<thead class="thead-light">
													<tr>
														<th>@lang('#Order Number')</th>
														<th>@lang('User')</th>
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
															<td data-label="@lang('Order Number')">
																#{{$item->order_number}}</td>
															<td data-label="@lang('User')">
																<a href="{{ route('user.edit', $item->store->user_id)}}"
																   class="text-decoration-none">
																	<div class="d-lg-flex d-block align-items-center ">
																		<div class="mr-3"><img
																				src="{{ optional($item->store->user)->profilePicture()??asset('assets/upload/boy.png') }}"
																				alt="user"
																				class="rounded-circle" width="35"
																				data-toggle="tooltip" title=""
																				data-original-title="{{optional($item->store->user)->name?? __('N/A')}}">
																		</div>
																		<div
																			class="d-inline-flex d-lg-block align-items-center">
																			<p class="text-dark mb-0 font-16 font-weight-medium">{{Str::limit(optional($item->store->user)->name?? __('N/A'),20)}}</p>
																			<span
																				class="text-muted font-14 ml-1">{{ '@'.optional($item->store->user)->username?? __('N/A')}}</span>
																		</div>
																	</div>
																</a>
															</td>
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
																	class="text-dark font-weight-bold">{{optional($item->store->user->storeCurrency)->symbol}}{{getAmount($item->total_amount,2)}}</span>
															</td>
															<td data-label="@lang('Shipping Charge')"><span
																	class="text-danger">{{optional($item->store->user->storeCurrency)->symbol}}{{getAmount($item->shipping_charge)}}</span>
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
																<a href="{{route('admin.order.view',$item->order_number)}}"
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
@endsection
