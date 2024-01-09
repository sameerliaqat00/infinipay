@extends('user.layouts.master')
@section('page_title', __('Shipping Charge'))
@push('extra_styles')
	<link rel="stylesheet" href="{{ asset('assets/dashboard/css/dataTables.bootstrap4.min.css') }}">
@endpush
@section('content')
	<div id="category" v-cloak>
		<div class="main-content">
			<section class="section">
				<div class="section-header">
					<h1>@lang('Shipping Charge')</h1>
					<div class="section-header-breadcrumb">
						<div class="breadcrumb-item active">
							<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
						</div>
						<div class="breadcrumb-item">@lang('Shipping Charge')</div>
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
												<h6 class="m-0 font-weight-bold text-primary">@lang('Shipping Charge')</h6>
												<a href="javascript:void(0)" data-target="#addShipping"
												   @click="makeEmpty"
												   data-toggle="modal"
												   class="btn btn-primary">@lang('Create Shipping Charge')</a>
											</div>
											<div class="card-body">
												<div class="table-responsive">
													<table
														class="table table-striped table-hover align-items-center table-flush"
														id="data-table">
														<thead class="thead-light">
														<tr>
															<th>@lang('SL.')</th>
															<th>@lang('Store')</th>
															<th>@lang('Address')</th>
															<th>@lang('Cost')</th>
															<th>@lang('Place Orders')</th>
															<th>@lang('Status')</th>
															<th>@lang('Action')</th>
														</tr>
														</thead>
														<tbody>
														@foreach($shipping as $key => $item)
															<tr>
																<td data-label="@lang('SL.')">{{++$key}}</td>
																<td data-label="@lang('Store')">
																	<a href="{{route('store.edit',$item->store_id)}}"
																	   class="text-decoration-none">
																		<p class="text-dark mb-0 font-16 font-weight-medium">{{Str::limit(optional($item->store)->name?? __('N/A'),20)}}</p>
																	</a>
																</td>
																<td data-label="@lang('Address')">{{ $item->address }}</td>
																<td data-label="@lang('Cost')">{{ $item->charge+0 }} {{ optional(auth()->user()->storeCurrency)->code }}</td>
																<td data-label="@lang('Place Orders')"><span class="badge badge-success">{{$item->orders_count}}</span></td>
																<td data-label="@lang('Status')">
																	@if($item->status == 1)
																		<span
																			class="badge badge-info">@lang('Active')</span>
																	@else
																		<span
																			class="badge badge-warning">@lang('Inactive')</span>
																	@endif
																</td>
																<td data-label="@lang('Action')">
																	<a href="javascript:void(0)"
																	   @click="edit({{$item}})"
																	   data-target="#editCategory"
																	   data-toggle="modal"
																	   class="btn btn-outline-primary btn-sm mr-2"><i
																			class="fas fa-edit"></i></a>
																	<a href="javascript:void(0)"
																	   data-target="#categoryDelete"
																	   data-toggle="modal"
																	   data-route="{{route('shipping.delete',$item->id)}}"
																	   class="btn btn-outline-danger btn-sm deleteShipping"><i
																			class="fas fa-trash"></i></a>
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
		@include('user.store.shipping.modal')
	</div>
@endsection

@push('extra_scripts')
	<script src="{{ asset('assets/dashboard/js/jquery.dataTables.min.js') }}"></script>
	<script src="{{ asset('assets/dashboard/js/dataTables.bootstrap4.min.js') }}"></script>
@endpush
@section('scripts')
	<script>
		'use strict'
		var newApp = new Vue({
			el: "#category",
			data: {
				item: {
					address: "", status: "", id: "", store: "", charge: ""
				},
				stores: [],
				addressError: "", storeError: "", chargeError: ""
			},
			mounted() {
				this.item.status = 1;
				this.stores = @json($stores);
			},
			methods: {
				save() {
					this.makeError();
					axios.post("{{ route('shipping.save') }}", this.item)
						.then(function (response) {
							console.log(response)
							if (response.data.status == 'success') {
								window.location.href = response.data.url;
							}
						})
						.catch(function (error) {
							let errors = error.response.data;
						});
				},
				edit(obj) {
					this.item.address = obj.address;
					this.item.id = obj.id;
					this.item.status = obj.status;
					this.item.store = obj.store.id;
					this.item.charge = obj.charge;
				},
				update() {
					this.makeError();
					axios.post("{{ route('shipping.update') }}", this.item)
						.then(function (response) {
							if (response.data.status == 'success') {
								window.location.href = response.data.url;
							}
						})
						.catch(function (error) {
							let errors = error.response.data;
						});
				},
				makeError() {
					if (!this.item.address) {
						this.addressError = "Address Field is required"
					}
					if (!this.item.store) {
						this.storeError = "Store Field is required"
					}

					if (!this.item.charge) {
						this.chargeError = "Charge Field is required"
					}
				},
				makeEmpty() {
					this.item.address = "";
					this.item.store = "";
					this.item.charge = "";
					this.item.status = 1;
					this.addressError = "";
					this.storeError = "";
					this.chargeError = "";
				}
			},
		})
		$(document).ready(function () {
			$('#data-table').dataTable({
				"aaSorting": [],
				"ordering": false
			});
		});

		$(document).on('click', '.deleteShipping', function () {
			var route = $(this).data('route');
			$('.deleteShippingForm').attr('action', route);
		})

		$(document).ready(function () {
			$('[data-toggle="tooltip"]').tooltip();
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
