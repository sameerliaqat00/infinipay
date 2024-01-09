@extends('admin.layouts.master')
@section('page_title', __('API Sandbox'))
@push('extra_styles')
	<link href="{{ asset('assets/dashboard/css/select2.min.css') }}" rel="stylesheet" type="text/css">
@endpush
@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('API Sandbox')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('API Sandbox')</div>
				</div>
			</div>

			<div class="section-body">
				<div class="row mt-sm-4">
					<div class="col-12 col-md-4 col-lg-3">
						@include('admin.control_panel.components.sidebar', ['settings' => config('generalsettings.settings'), 'suffix' => 'Settings'])
					</div>
					<div class="col-12 col-md-8 col-lg-9">
						<div class="container-fluid" id="container-wrapper">
							<div class="row justify-content-center">
								<div class="col-md-12">
									<div class="card card-primary shadow mb-4">
										<form action="{{ route('api.sandbox.index') }}" method="post">
											@csrf
											<div
												class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
												<h6 class="m-0 font-weight-bold text-primary">@lang('Gateways For API Sandbox')</h6>
											</div>
											<div class="card-body">
												<div class="row">
													@if($gateways)
														<div class="col-md-12">
															<div class="form-group">
																<label for="currency">@lang('Select Gateways')</label>
																<select
																	class="form-select form-control @error('gateways') is-invalid @enderror"
																	name="gateways[]" multiple="multiple"
																	id="selectCurrency">
																	@foreach($gateways as $gateway)
																		<option
																			value="{{$gateway->id}}" {{in_array($gateway->id,$basicControl->sandbox_gateways) == true ? 'selected':''}}>{{$gateway->name}}</option>
																	@endforeach
																</select>
																<div class="invalid-feedback mt-3 ml-3">
																	@error('gateways') @lang($message) @enderror
																</div>
															</div>
														</div>
													@endif
												</div>
											</div>
											<div class="card-footer">
												<button type="submit"
														class="btn btn-block btn-primary">@lang('Save Changes')</button>
											</div>
										</form>
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
@push('extra_styles')
	<link href="{{ asset('assets/dashboard/css/select2.min.css') }}" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="{{ asset('assets/store/css/bootstrap-select.min.css') }}">
@endpush
@push('extra_scripts')
	<script src="{{ asset('assets/store/js/bootstrap-select.min.js') }}"></script>
@endpush

@section('scripts')
	<script>
		'use strict'
		$(function () {
			$('#selectCurrency').selectpicker();
		});
	</script>
@endsection
