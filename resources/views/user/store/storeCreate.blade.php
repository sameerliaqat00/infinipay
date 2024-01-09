@extends('user.layouts.master')
@section('page_title',__('Store Create'))

@section('content')
	<div class="main-content" id="storeCreate" v-cloak>
		<section class="section">
			<div class="section-header">
				<h1>@lang('Store Create')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Store Create')</div>
				</div>
			</div>
			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row justify-content-md-center">
						<div class="col-md-12">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Store Create')</h6>
								</div>
								<div class="card-body">
									<form action="{{ route('store.create') }}" method="post"
										  enctype="multipart/form-data">
										@csrf
										<div class="row">
											<div class="col-md-4">
												<div class="form-group">
													<label for="Store Name">@lang('Store Name') <sup
															class="text-danger">*</sup></label>
													<input type="text"
														   name="name"
														   value="{{ old('name') }}"
														   class="form-control @error('name') is-invalid @enderror"
														   autocomplete="off" required>
													<div class="invalid-feedback">
														@error('name') @lang($message) @enderror
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label for="Shipping Charge">@lang('Shipping Charge')</label>
													<select name="shipping_charge"
															class="form-control form-control-sm">
														<option
															value="1" {{old('shipping_charge') == '1' ? 'selected':''}}>@lang('Active')</option>
														<option
															value="0" {{old('shipping_charge') == '0' ? 'selected':''}}>@lang('Inactive')</option>
													</select>
													<div class="invalid-feedback">
														@error('shipping_charge') @lang($message) @enderror
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label for="Delivery Note">@lang('Delivery Note')</label>
													<select name="delivery_note"
															class="form-control form-control-sm">
														<option
															value="disabled" {{old('delivery_note') == 'disabled' ? 'selected':''}}>@lang('Disable')</option>
														<option
															value="enable" {{old('delivery_note') == 'enable' ? 'selected':''}}>@lang('Enable')</option>
													</select>
													<div class="invalid-feedback">
														@error('delivery_note') @lang($message) @enderror
													</div>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-8">
												<label for="Image">@lang('Store Link')</label>
												<div class="input-group">
													<input type="text" value="{{route('public.view')}}"
														   class="form-control" readonly>
													<div class="input-group-prepend width-50">
														<input type="text" v-on:keyup="storeLinkCheck" name="link"
															   value=""
															   class="form-control">
													</div>
												</div>
												<span class="text-danger float-right">@{{ msg }}</span>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label for="Status">@lang('Status')</label>
													<select name="status"
															class="form-control form-control-sm">
														<option
															value="1" {{old('status') == '1' ? 'selected':''}}>@lang('Active')</option>
														<option
															value="0" {{old('status') == '0' ? 'selected':''}}>@lang('Inactive')</option>
													</select>
													<div class="invalid-feedback">
														@error('status') @lang($message) @enderror
													</div>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-4">
												<label for="Image">@lang('Store Image') <sup
														class="text-danger">* @lang('size')
														= {{config('location.store.size')}}@lang('px')</sup></label>
												<div class="form-group mt-2">
													<div class="fileinput fileinput-new "
														 data-provides="fileinput">
														<div class="fileinput-new thumbnail withdraw-thumbnail"
															 data-trigger="fileinput">
															<img class="w-150px"
																 src="{{ getFile(config('location.default2')) }}"
																 alt="...">
														</div>
														<div
															class="fileinput-preview fileinput-exists thumbnail wh-200-150"></div>
														<div class="img-input-div">
                                                                <span class="btn btn-info btn-file">
                                                                    <span
																		class="fileinput-new"> @lang('Select Store Image')</span>
                                                                    <span
																		class="fileinput-exists"> @lang('Change')</span>
                                                                    <input type="file" name="image" accept="image/*"
																		   required>
                                                                </span>
															<a href="#" class="btn btn-danger fileinput-exists"
															   data-dismiss="fileinput"> @lang('Remove')</a>
														</div>
														<div class="invalid-feedback">
															@error('image') @lang($message) @enderror
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label for="Store Description">@lang('Store Description')</label>
													<textarea name="short_description" rows="5"
															  class="form-control form-control-sm">{{old('short_description')}}</textarea>
												</div>
											</div>
										</div>
										<button type="submit"
												class="btn btn-primary btn-sm btn-block">@lang('Create store')</button>
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
	<link rel="stylesheet" href="{{ asset('assets/dashboard/css/bootstrap-fileinput.css') }}">
@endpush
@section('scripts')
	<script src="{{ asset('assets/dashboard/js/bootstrap-fileinput.js') }}"></script>
	<script>
		'use strict';
		var newApp = new Vue({
			el: "#storeCreate",
			data: {
				link: {},
				msg: ''
			},
			mounted() {
			},
			methods: {
				storeLinkCheck(link) {
					let storeLink = this.link;
					storeLink.link = link.target.value;
					storeLink.storeId = '-1';
					var _this = this;
					axios.post("{{ route('store.link.check') }}", this.link)
						.then(function (response) {
							if (response.data.status == 'success') {
								_this.msg = response.data.msg;
							}
							if (response.data.status == 'notFound') {
								_this.msg = '';
							}
						})
						.catch(function (error) {
							let errors = error.response.data;
						});
				},
			},
		})
	</script>
@endsection

