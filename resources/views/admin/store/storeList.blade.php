@extends('admin.layouts.master')
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
						<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
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
										<div class="card-body">
											<div class="table-responsive">
												<table
													class="table table-striped table-hover align-items-center table-flush"
													id="data-table">
													<thead class="thead-light">
													<tr>
														<th>@lang('SL.')</th>
														<th>@lang('User')</th>
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
															<td data-label="@lang('User')">
																<a href="{{ route('user.edit', $item->user_id)}}"
																   class="text-decoration-none">
																	<div class="d-lg-flex d-block align-items-center ">
																		<div class="mr-3"><img
																				src="{{ optional($item->user)->profilePicture()??asset('assets/upload/boy.png') }}"
																				alt="user"
																				class="rounded-circle" width="35"
																				data-toggle="tooltip" title=""
																				data-original-title="{{optional($item->user)->name?? __('N/A')}}">
																		</div>
																		<div
																			class="d-inline-flex d-lg-block align-items-center">
																			<p class="text-dark mb-0 font-16 font-weight-medium">{{Str::limit(optional($item->user)->name?? __('N/A'),20)}}</p>
																			<span
																				class="text-muted font-14 ml-1">{{ '@'.optional($item->user)->username?? __('N/A')}}</span>
																		</div>
																	</div>
																</a>
															</td>
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
																<a href="{{route('admin.store.view',$item->id)}}"
																   class="btn btn-outline-primary btn-sm"><i
																		class="fa fa-eye"></i></a>
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
