@extends('admin.layouts.master')
@section('page_title',__('Staff List'))

@section('content')
	<div id="manage-user-app">
		<div class="main-content">
			<section class="section">
				<div class="section-header">
					<h1>@lang('Staff List')</h1>
					<div class="section-header-breadcrumb">
						<div class="breadcrumb-item active">
							<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
						</div>
						<div class="breadcrumb-item">@lang('Staff List')</div>
					</div>
				</div>

				<div class="row mb-3">
					<div class="container-fluid" id="container-wrapper">
						<div class="row">
							<div class="col-lg-12">
								<div class="card mb-4 card-primary shadow">
									<div
										class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<h6 class="m-0 font-weight-bold text-primary">@lang('Staff List')</h6>
										<button class="btn btn-sm btn-primary" data-target="#add-modal"
												data-toggle="modal" @click="makeDataEmpty">@lang('Add New')</button>
									</div>
									<div class="card-body">
										<div class="table-responsive">
											<table
												class="table table-striped table-hover align-items-center table-borderless">
												<thead class="thead-light">
												<tr>
													<th>@lang('SL.')</th>
													<th>@lang('User')</th>
													<th>@lang('Role')</th>
													<th>@lang('Status')</th>
													<th>@lang('Action')</th>
												</tr>
												</thead>
												<tbody>
												@forelse($roleUsers as $key => $value)
													<tr>
														<td data-label="@lang('SL.')">{{++$key }}</td>
														<td data-label="@lang('User')">
															<a href="javascript:void(0)"
															   class="text-decoration-none">
																<div class="d-lg-flex d-block align-items-center ">
																	<div class="mr-3"><img
																			src="{{ $value->profilePicture()??asset('assets/upload/boy.png') }}"
																			alt="user"
																			class="rounded-circle" width="35"
																			data-toggle="tooltip" title=""
																			data-original-title="{{$value->name?? __('N/A')}}">
																	</div>
																	<div
																		class="d-inline-flex d-lg-block align-items-center">
																		<p class="text-dark mb-0 font-16 font-weight-medium">{{Str::limit($value->name?? __('N/A'),20)}}</p>
																		<span
																			class="text-muted font-14 ml-1">{{ '@'.$value->username?? __('N/A')}}</span>
																	</div>
																</div>
															</a>
														</td>
														<td data-label="@lang('Role')">
															<span
																class="badge badge-primary">{{str_replace('_',' ',ucfirst(optional($value->role)->name))}}</span>
														</td>
														<td data-label="@lang('Status')">
															@if($value->status == 1)
																<span class="badge badge-success">@lang('Active')</span>
															@else
																<span
																	class="badge badge-danger">@lang('Inactive')</span>
															@endif
														</td>
														<td data-label="@lang('Action')">
															<button data-target="#login_as" data-toggle="modal"
																	data-route="{{route('admin.role.usersLogin',$value->id)}}"
																	class="btn btn-sm btn-outline-primary loginUser">@lang('Login As Staff')</button>
															@if($value->status == 0)
																<button data-target="#status_change" data-toggle="modal"
																		data-route="{{route('admin.role.statusChange',$value->id)}}"
																		class="btn btn-sm btn-outline-success enableStatus">
																	<i
																		class="fas fa-check-circle"></i> @lang('Active')
																</button>
															@else
																<button data-target="#status_change" data-toggle="modal"
																		data-route="{{route('admin.role.statusChange',$value->id)}}"
																		class="btn btn-sm btn-outline-danger disableStatus">
																	<i
																		class="fas fa-times"></i> @lang('Inactive')
																</button>
															@endif
														</td>
													</tr>
												@empty
													<tr>
														<th colspan="100%"
															class="text-center">@lang('No data found')</th>
													</tr>
												@endforelse
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>
		<div id="add-modal" class="modal fade" tabindex="-1" role="dialog"
			 aria-labelledby="primary-header-modalLabel"
			 aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title text-dark font-weight-bold"
							id="primary-header-modalLabel">@lang('Add Staffs')</h4>
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					</div>
					<form>
						<div class="modal-body">
							@if($roles)
								<div class="col-12">
									<label for="">@lang('Role') </label>
									<select
										class="form-select form-control"
										v-model="item.role"
										aria-label="Default select example">
										<option selected disabled>@lang('Select Role')</option>
										@forelse($roles as $item)
											<option value="{{$item->id}}">{{$item->name}}</option>
										@empty
										@endforelse
									</select>
									<span class="text-danger role-error"></span>
								</div>
							@endif
							<div class="col-12 mt-3">
								<label for="">@lang('Name')</label>
								<input
									type="text"
									class="form-control" v-model="item.name"
									placeholder="@lang('Name')"/>
								<span class="text-danger name-error"></span>
							</div>
							<div class="col-12 mt-3">
								<label for="">@lang('Email')</label>
								<input
									type="text"
									class="form-control" v-model="item.email"
									placeholder="@lang('Email')"/>
								<span class="text-danger email-error"></span>
							</div>
							<div class="col-12 mt-3">
								<label for="">@lang('Username')</label>
								<input
									type="text"
									class="form-control" v-model="item.username"
									placeholder="@lang('Username')"/>
								<span class="text-danger username-error"></span>
							</div>
							<div class="col-12 mt-3">
								<label for="">@lang('Password')</label>
								<input
									type="text"
									class="form-control" v-model="item.password"
									placeholder="@lang('Password')"/>
								<span class="text-danger password-error"></span>
							</div>
							<div class="col-md-12 my-3">
								<label for="">@lang('Status') </label>
								<div class="selectgroup w-100">
									<label class="selectgroup-item">
										<input type="radio" v-model="item.status" value="0" class="selectgroup-input">
										<span class="selectgroup-button">@lang('OFF')</span>
									</label>
									<label class="selectgroup-item">
										<input type="radio" v-model="item.status" value="1" class="selectgroup-input"
											   :checked="item.status == 1">
										<span class="selectgroup-button">@lang('ON')</span>
									</label>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
							<button type="button" class="btn btn-primary"
									@click.prevent="userSubmit">@lang('save')</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	{{--	 Status Change--}}
	<div id="status_change" class="modal fade" tabindex="-1" role="dialog"
		 aria-labelledby="primary-header-modalLabel"
		 aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title text-dark font-weight-bold"
						id="primary-header-modalLabel">@lang('Status Change Confirmation')</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				<form action="" method="post" class="statusRoute">
					@csrf
					<div class="modal-body">
						<div id="tag-body">
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
						<button type="submit" class="btn btn-primary">@lang('Submit')</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	{{-- Login as --}}
	<div id="login_as" class="modal fade" tabindex="-1" role="dialog"
		 aria-labelledby="primary-header-modalLabel"
		 aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title text-dark font-weight-bold"
						id="primary-header-modalLabel">@lang('Login as user')</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				<form action="" method="post" class="loginRoute">
					@csrf
					<div class="modal-body">
						<p>@lang('Are you sure want to login as user')</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
						<button type="submit" class="btn btn-primary">@lang('Submit')</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
@push('extra_scripts')
	<script>
		'use strict'
		$(document).on('click', '.enableStatus', function () {
			$('#tag-body').html('');
			var route = $(this).data('route');
			$('.statusRoute').attr('action', route)
			$('#tag-body').append(`<p>Are you sure active this staff<p>`)
		});

		$(document).on('click', '.disableStatus', function () {
			$('#tag-body').html('');
			var route = $(this).data('route');
			$('.statusRoute').attr('action', route)
			$('#tag-body').append(`<p>Are you sure inactive this staff<p>`)
		});

		$(document).on('click', '.loginUser', function () {
			var route = $(this).data('route');
			$('.loginRoute').attr('action', route)
		});

		var newApp = new Vue({
			el: "#manage-user-app",
			data: {
				item: {
					name: "", email: "", username: "", password: "", role: "",
					id: "",
					status: "",
				}
			},
			mounted() {
				this.item.status = 1;
			},
			methods: {
				userSubmit() {
					var $url = '{{route('admin.role.usersCreate')}}'
					axios.post($url, this.item)
						.then(function (response) {
							if (response.data.result) {
								location.reload();
							}
						})
						.catch(function (error) {
							let errors = error.response.data;
							errors = errors.errors
							for (let err in errors) {
								let selector = document.querySelector(`.${err}-error`);
								if (selector) {
									selector.innerText = `${errors[err]}`;
								}
							}
						});
				},
				makeDataEmpty() {
					this.item.name = "";
					this.item.email = "";
					this.item.username = "";
					this.item.password = "";
					this.item.id = "";
				},
				editRole(obj) {
					this.makeDataEmpty();
					this.item.name = obj.name;
					this.item.id = obj.id;
					this.item.status = obj.status;
					this.item.permissions = obj.permission;
					if (0 < obj.permission.length) {
						obj.permission.map(function (obj, i) {
							$(`.permission-check[value="${obj}"]`).attr('checked', 'checked');
						});
					}
				},
				rolePermissionUpdate() {
					var $url = '{{route('admin.role.update')}}'
					axios.post($url, this.item)
						.then(function (response) {
							if (response.data.result) {
								location.reload();
							}
						})
						.catch(function (error) {
							let errors = error.response.data;
							errors = errors.errors
							for (let err in errors) {
								let selector = document.querySelector(`.${err}-error`);
								if (selector) {
									selector.innerText = `${errors[err]}`;
								}
							}
						});
				},
			}
		})
	</script>
@endpush
