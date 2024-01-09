@extends('admin.layouts.master')
@section('page_title', __('Edit Currency'))
@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Update Currency')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item"><a href="{{ route('currency.index') }}">@lang('Currencies')</a></div>
					<div class="breadcrumb-item">@lang('Edit Currency')</div>
				</div>
			</div>

			<div class="section-body">
				<div class="row mt-sm-4">
					<div class="col-12 col-md-4 col-lg-3">
						@include('admin.control_panel.components.sidebar', ['settings' => config('generalsettings.settings'), 'suffix' => 'Settings'])
					</div>
					<div class="col-12 col-md-8 col-lg-9">
						<div class="container-fluid" id="container-wrapper">
							<div class="row justify-content-md-center">
								<div class="col-lg-12">
									<div class="card mb-4 card-primary shadow">
										<div
											class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
											<h6 class="m-0 font-weight-bold text-primary">@lang('Edit Currency')</h6>
											<a href="{{route('currency.index')}}" class="btn btn-sm btn-outline-primary"> <i
													class="fas fa-arrow-left"></i> @lang('Back')</a>
										</div>
										<div class="card-body">
											<form method="post" action="{{ route('currency.update',$currency) }}"
												  enctype="multipart/form-data">
												@csrf
												@method('PUT')
												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<label for="name">@lang('Name')</label>
															<input type="text" name="name"
																   placeholder="@lang('eg:- US Doller')"
																   value="{{ $currency->name }}"
																   class="form-control @error('name') is-invalid @enderror">
															<div
																class="invalid-feedback">@error('name') @lang($message) @enderror</div>
														</div>
													</div>

													<div class="col-md-6">
														<div class="form-group">
															<label for="code">@lang('Code')</label>
															<input type="text" name="code"
																   placeholder="@lang('eg:- USD')"
																   value="{{ $currency->code }}"
																   class="form-control code @error('code') is-invalid @enderror">
															<div
																class="invalid-feedback">@error('code') @lang($message) @enderror</div>
														</div>
													</div>

													<div class="col-md-6">
														<div class="form-group">
															<label for="symbol">@lang('Symbol')</label>
															<input type="text" name="symbol"
																   placeholder="@lang('eg:- $')"
																   value="{{ $currency->symbol }}"
																   class="form-control @error('symbol') is-invalid @enderror">
															<div
																class="invalid-feedback">@error('symbol') @lang($message) @enderror</div>
														</div>
													</div>

													<div class="col-md-6">
														<div class="form-group">
															<label for="exchange_rate">@lang('Exchange Rate')</label>
															<div class="input-group input-group-sm">
																<div class="input-group-prepend"><span
																		class="input-group-text">1 {{ __(basicControl()->base_currency_code) }} @lang('=') </span>
																</div>
																<input type="text" name="exchange_rate"
																	   placeholder="@lang('eg:- 0.00')"
																	   value="{{ getAmount($currency->exchange_rate) }}"
																	   class="form-control rate @error('exchange_rate') is-invalid @enderror">
																<div class="input-group-append"><span
																		class="input-group-text rate"></span></div>
																<div
																	class="invalid-feedback">@error('exchange_rate') @lang($message) @enderror</div>
															</div>
														</div>
													</div>

													<div class="col-md-6">
														<div class="form-group">
															<label for="currency_type">@lang('Currency Type')</label>
															<select id="currency_type" name="currency_type"
																	class="form-control @error('currency_type') is-invalid @enderror">
																<option
																	value="0" {{ (old('currency_type',$currency->currency_type) == 0) ? 'selected' : '' }}>@lang('Crypto')</option>
																<option
																	value="1" {{ (old('currency_type',$currency->currency_type) == 1) ? 'selected' : '' }}>@lang('Fiat')</option>
															</select>
															<div
																class="invalid-feedback">@error('currency_type') @lang($message) @enderror</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label>@lang('Active Currency')</label>
															<div class="selectgroup w-100">
																<label class="selectgroup-item">
																	<input type="radio" name="is_active" value="0"
																		   class="selectgroup-input" {{ old('is_active', $currency->is_active) == 0 ? 'checked' : ''}}>
																	<span class="selectgroup-button">@lang('OFF')</span>
																</label>
																<label class="selectgroup-item">
																	<input type="radio" name="is_active" value="1"
																		   class="selectgroup-input" {{ old('is_active', $currency->is_active) == 1 ? 'checked' : ''}}>
																	<span class="selectgroup-button">@lang('ON')</span>
																</label>
															</div>
															@error('is_active')
															<span class="text-danger" role="alert">
														<strong>{{ __($message) }}</strong>
													</span>
															@enderror
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group mb-4">
															<label class="col-form-label">@lang('Logo')</label>
															<div id="image-preview" class="image-preview"
																 style="background-image: url({{ getFile(config('location.currencyLogo.path').$currency->logo) ? : 0 }});">
																<label for="image-upload"
																	   id="image-label">@lang('Choose logo')</label>
																<input type="file" name="logo"
																	   class="@error('logo') is-invalid @enderror"
																	   id="image-upload"/>
															</div>
															<div class="invalid-feedback">
																@error('logo') @lang($message) @enderror
															</div>
														</div>
													</div>
												</div>
												<input type="submit" class="btn btn-primary btn-sm btn-block"
													   value="@lang('Save Changes')">
											</form>
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

@push('extra_scripts')
	<script src="{{ asset('assets/dashboard/js/jquery.uploadPreview.min.js') }}"></script>
@endpush
@section('scripts')
	<script>
		'use strict'
		$(document).ready(function () {
			setCurrencyCode();

			function setCurrencyCode() {
				$('.rate').text($('.code').val());
			}

			$(document).on('input', '.code', function () {
				setCurrencyCode();
			})

			$.uploadPreview({
				input_field: "#image-upload",
				preview_box: "#image-preview",
				label_field: "#image-label",
				label_default: "Choose logo",
				label_selected: "Change logo",
				no_label: false
			});
		});
	</script>
@endsection
