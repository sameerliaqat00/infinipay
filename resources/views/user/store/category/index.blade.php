@extends('user.layouts.master')
@section('page_title', __('Category'))
@push('extra_styles')
	<link rel="stylesheet" href="{{ asset('assets/dashboard/css/dataTables.bootstrap4.min.css') }}">
@endpush
@section('content')
	<div id="category" v-cloak>
		<div class="main-content">
			<section class="section">
				<div class="section-header">
					<h1>@lang('Category')</h1>
					<div class="section-header-breadcrumb">
						<div class="breadcrumb-item active">
							<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
						</div>
						<div class="breadcrumb-item">@lang('Category')</div>
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
												<h6 class="m-0 font-weight-bold text-primary">@lang('Category')</h6>
												<a href="javascript:void(0)" data-target="#addCategory"
												   @click="makeEmpty"
												   data-toggle="modal"
												   class="btn btn-primary">@lang('Create Category')</a>
											</div>
											<div class="card-body">
												<div class="table-responsive">
													<table
														class="table table-striped table-hover align-items-center table-flush"
														id="data-table">
														<thead class="thead-light">
														<tr>
															<th>@lang('SL.')</th>
															<th>@lang('Name')</th>
															<th>@lang('Active Products')</th>
															<th>@lang('Status')</th>
															<th>@lang('Action')</th>
														</tr>
														</thead>
														<tbody>
														@foreach($categories as $key => $item)
															<tr>
																<td data-label="@lang('SL.')">{{++$key}}</td>
																<td data-label="@lang('Name')">{{ $item->name }}</td>
																<td data-label="@lang('Active Products')"><span class="badge badge-success">{{$item->active_products_count}}</span></td>
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
																	   data-route="{{route('category.delete',$item->id)}}"
																	   class="btn btn-outline-danger btn-sm deleteCategory"><i
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
		@include('user.store.category.modal')
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
					name: "", status: "", id: ""
				},
				nameError: ""
			},
			mounted() {
				this.item.status = 1;
			},
			methods: {
				save() {
					this.makeError();
					axios.post("{{ route('category.save') }}", this.item)
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
					this.item.name = obj.name;
					this.item.id = obj.id;
					this.item.status = obj.status;
				},
				update() {
					this.makeError();
					axios.post("{{ route('category.update') }}", this.item)
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
				makeError() {
					if (!this.item.name) {
						this.nameError = "Name Field is required"
					}
				},
				makeEmpty() {
					this.item.name = "";
					this.item.status = 1;
					this.nameError = "";
				}
			},
		})
		$(document).ready(function () {
			$('#data-table').dataTable({
				"aaSorting": [],
				"ordering": false
			});
		});

		$(document).on('click', '.deleteCategory', function () {
			var route = $(this).data('route');
			$('.deleteCategoryForm').attr('action', route);
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
