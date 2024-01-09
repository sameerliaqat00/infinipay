@extends('user.layouts.master')
@section('page_title', __('Store Fronts'))
@push('extra_styles')
	<link rel="stylesheet" href="{{ asset('assets/dashboard/css/dataTables.bootstrap4.min.css') }}">
@endpush

@section('content')
	<div class="main-content" id="store" v-cloak>
		<section class="section">
			<div class="section-header">
				<h1>@lang('Store Fronts')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Store Fronts')</div>
				</div>
			</div>
			<div class="section-body">
				<div class="row mt-sm-4">
					<div class="col-12 col-md-12 col-lg-12">
						<div class="container-fluid" id="container-wrapper">
							<div class="row justify-content-md-center">
								<div class="col-lg-12">
									<div class="card mb-4 card-primary shadow">
										<div
											class="card-header py-3 d-flex flex-wrap flex-row align-items-center justify-content-between">
											<h6 class="m-0 font-weight-bold text-primary">@lang('Store Fronts')</h6>
											<a href="{{route('store.create')}}"
											   class="btn btn-primary">@lang('Create Store')</a>
										</div>
										<div class="card-body">
											<div class="table-responsive">
												<table
													class="table table-striped table-hover align-items-center table-flush"
													id="data-table">
													<thead class="thead-light">
													<tr>
														<th>@lang('SL.')</th>
														<th>@lang('Image')</th>
														<th>@lang('Name')</th>
														<th>@lang('Product')</th>
														<th>@lang('Shipping Charge')</th>
														<th>@lang('Status')</th>
														<th>@lang('Delivery Note')</th>
														<th>@lang('Copy Link')</th>
														<th>@lang('Action')</th>
													</tr>
													</thead>
													<tbody>
													@foreach($stores as $key => $item)
														<tr>
															<td data-label="@lang('SL.')">{{++$key}}</td>
															<td data-label="@lang('Image')">
																<img class="img-store-custom"
																	 src="{{ getFile(config('location.store.path').$item->image) }}">
															</td>
															<td data-label="@lang('Name')">{{ $item->name }}</td>
															<td data-label="@lang('Product')"><span
																	class="badge badge-info">{{ $item->products_map_count }}</span>
															</td>
															<td data-label="@lang('Shipping Charge')">
																@if($item->shipping_charge == 1)
																	<span
																		class="badge badge-info">@lang('Active')</span>
																@else
																	<span
																		class="badge badge-warning">@lang('Inactive')</span>
																@endif
															</td>
															<td data-label="@lang('Status')">
																@if($item->status == 1)
																	<span
																		class="badge badge-info">@lang('Active')</span>
																@else
																	<span
																		class="badge badge-warning">@lang('Inactive')</span>
																@endif
															</td>
															<td data-label="@lang('Delivery Note')">
																<span
																	class="badge badge-success">{{ucfirst($item->delivery_note)}}</span>
															</td>
															<td data-label="@lang('Copy Link')">
																<a href="javascript:void(0)"
																   @click.stop.prevent="copyTestingCode('{{route('public.view',$item->link)}}')">
																	<i class="fas fa-link"></i>
																</a>
															</td>
															<td data-label="@lang('Action')">
																<div class="dropdown">
																	<button
																		class="btn btn-sm btn-primary dropdown-toggle"
																		type="button" data-toggle="dropdown"
																		data-boundary="window" aria-haspopup="true"
																		aria-expanded="false">@lang('More Action')
																	</button>
																	<div class="dropdown-menu">
																		<a class="dropdown-item text-primary"
																		   href="{{route('store.edit',$item->id)}}">
																			<i class="fas fa-edit mr-2"></i>@lang('Edit')
																		</a>
																		<a class="dropdown-item text-danger deleteStore"
																		   data-target="#storeDelete"
																		   data-toggle="modal"
																		   data-route="{{route('store.delete',$item->id)}}"
																		   href="javascript:void(0)"><i
																				class="fa fa-trash mr-2"></i> @lang('Delete')
																		</a>
																	</div>
																</div>
															</td>
														</tr>
													@endforeach
													</tbody>
												</table>
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
	<script src="{{ asset('assets/dashboard/js/jquery.dataTables.min.js') }}"></script>
	<script src="{{ asset('assets/dashboard/js/dataTables.bootstrap4.min.js') }}"></script>
@endpush
@section('scripts')
	<script>
		'use strict'
		$(document).ready(function () {
			$('#data-table').dataTable({
				"aaSorting": [],
				"ordering": false
			});
		});

		$(document).on('click', '.deleteStore', function () {
			var route = $(this).data('route');
			$('.deleteStoreForm').attr('action', route);
		})

		var newApp = new Vue({
			el: "#store",
			data: {},
			mounted() {
			},
			methods: {
				copyTestingCode(copyText) {
					navigator.clipboard.writeText(copyText);
					Notiflix.Notify.Success('Link Copied');
				},
			},
		})
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
