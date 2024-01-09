@extends('admin.layouts.master')
@section('page_title', __('Edit Payment Method'))
@push('extra_styles')
	<link href="{{ asset('assets/dashboard/css/select2.min.css') }}" rel="stylesheet" type="text/css">
@endpush
@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang("Edit") {{ trans($method->name) }}</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang("Edit") {{ trans($method->name) }}</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row">
						<div class="col-lg-12">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang("Edit") {{ trans($method->name) }}</h6>
									<a href="{{ route('payment.methods') }}" class="btn btn-sm btn-outline-primary"> <i
											class="fas fa-arrow-left"></i> @lang('Back')</a>
								</div>
								<div class="card-body">
									<form method="post" action="{{ route('update.payment.methods', $method->id) }}"
										  enctype="multipart/form-data">
										@csrf
										@method('put')
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="name">@lang('Name')</label>
													<input type="text" value="{{ old('name', $method->name ?? '') }}"
														   name="name"
														   class="form-control @error('name') is-invalid @enderror"
														   disabled required>
													<div class="invalid-feedback">
														@error('name') @lang($message) @enderror
													</div>
												</div>
											</div>
											@if($method->currencies)
												<div class="col-md-6">
													<div class="form-group">
														<label for="currency">@lang('Currency')</label>
														<select
															class="form-control currency-change @error('currency') is-invalid @enderror"
															name="currency" required>
															<option disabled selected>@lang('Select Currency')</option>
															@foreach($method->currencies as $key => $currency)
																@foreach($currency as $curKey => $singleCurrency)
																	<option value="{{ $curKey }}"
																			{{ old('currency', $method->currency) == $curKey ? 'selected' : '' }} data-fiat="{{ $key }}">{{ trans($curKey) }}</option>
																@endforeach
															@endforeach
														</select>
														<div class="invalid-feedback">
															@error('currency') @lang($message) @enderror
														</div>
													</div>
												</div>
											@endif
											<div class="col-md-6">
												<div class="form-group">
													<label for="currency_symbol">@lang('Currency Symbol')</label>
													<input type="text"
														   class="form-control @error('currency_symbol') is-invalid @enderror"
														   name="currency_symbol"
														   value="{{ old('currency_symbol', $method->symbol ?? '') }}"
														   required>
													<div class="invalid-feedback">
														@error('currency_symbol') @lang($message) @enderror
													</div>
												</div>
											</div>
										</div>
										<div class="row">
											@foreach ($method->parameters as $key => $parameter)
												<div class="col-md-6">
													<div class="form-group">
														<label
															for="{{ $key }}">{{ __(strtoupper(str_replace('_',' ', $key))) }}</label>
														<input type="text" name="{{ $key }}"
															   value="{{ old($key, $parameter) }}"
															   id="{{ $key }}"
															   class="form-control @error($key) is-invalid @enderror">
														<div class="invalid-feedback">
															@error($key) @lang($message) @enderror
														</div>
													</div>
												</div>
											@endforeach
										</div>
										@if($method->extra_parameters)
											<div class="row">
												@foreach($method->extra_parameters as $key => $param)
													<div class="col-md-6">
														<div class="form-group">
															<label
																for="{{ $key }}">{{ __(strtoupper(str_replace('_',' ', $key))) }}</label>
															<div class="input-group">
																<input type="text" name="{{ $key }}"
																	   class="form-control @error($key) is-invalid @enderror"
																	   value="{{ old($key, route($param, $method->code )) }}"
																	   disabled>
																<div class="input-group-append">
																	<button type="button"
																			class="btn btn-info copy-btn btn-sm">
																		<i class="fas fa-copy"></i>
																	</button>
																</div>
															</div>
															<div class="invalid-feedback">
																@error($key) @lang($message) @enderror
															</div>
														</div>
													</div>
												@endforeach
											</div>
										@endif
										<div class="row align-items-center">
											<div class="col-md-6">
												<div class="form-group mb-4">
													<label class="col-form-label">@lang('Image')</label>
													<div id="image-preview" class="image-preview"
														 style="background-image: url({{ getFile(config('location.gateway.path') . $method->image) ? : 0 }});">
														<label for="image-upload"
															   id="image-label">@lang('Choose File')</label>
														<input type="file" name="image"
															   class="@error('image') is-invalid @enderror"
															   id="image-upload"/>
													</div>
													<div class="invalid-feedback">
														@error('image') @lang($message) @enderror
													</div>
												</div>
											</div>
											<div class="col-md-3">
												<div class="form-group">
													<label>@lang('Enable to active')</label>
													<div class="selectgroup w-100">
														<label class="selectgroup-item">
															<input type="radio" name="status" value="0"
																   class="selectgroup-input" {{ old('status', $method->status) == 0 ? 'checked' : ''}}>
															<span class="selectgroup-button">@lang('OFF')</span>
														</label>
														<label class="selectgroup-item">
															<input type="radio" name="status" value="1"
																   class="selectgroup-input" {{ old('status', $method->status) == 1 ? 'checked' : ''}}>
															<span class="selectgroup-button">@lang('ON')</span>
														</label>
													</div>
													@error('status')
													<span class="text-danger" role="alert">
														<strong>{{ __($message) }}</strong>
													</span>
													@enderror
												</div>
											</div>
											@if($method->is_sandbox == 1)
												<div class="col-md-3">
													<div class="form-group">
														<label>@lang('Enable test environment')</label>
														<div class="selectgroup w-100">
															<label class="selectgroup-item">
																<input type="radio" name="environment" value="live"
																	   class="selectgroup-input" {{ old('environment', $method->environment) == 'live' ? 'checked' : ''}}>
																<span class="selectgroup-button">@lang('OFF')</span>
															</label>
															<label class="selectgroup-item">
																<input type="radio" name="environment" value="test"
																	   class="selectgroup-input" {{ old('environment', $method->environment) == 'test' ? 'checked' : ''}}>
																<span class="selectgroup-button">@lang('ON')</span>
															</label>
														</div>
														@error('environment')
														<span class="text-danger" role="alert">
															<strong>{{ __($message) }}</strong>
														</span>
														@enderror
													</div>
												</div>
											@endif
										</div>
										<button type="submit"
												class="btn btn-block btn-primary">@lang('Save Changes')</button>
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
@push('extra_scripts')
	<script src="{{ asset('assets/dashboard/js/select2.min.js') }}"></script>
	<script src="{{ asset('assets/dashboard/js/jquery.uploadPreview.min.js') }}"></script>
@endpush
@section('scripts')
	<script>
		'use strict'
		$(document).ready(function () {
			$('.currency-change').select2();
			setCurrency();
			$(document).on('change', '.currency-change', function () {
				setCurrency();
			});

			function setCurrency() {
				let currency = $('.currency-change').val();
				let fiatYn = $('.currency-change option:selected').attr('data-fiat');
				if (fiatYn == 0) {
					$('.set-currency').text(currency);
				} else {
					$('.set-currency').text('USD');
				}
			}

			$(document).on('click', '.copy-btn', function () {
				let _this = $(this)[0];
				let copyText = $(this).parents('.input-group-append').siblings('input');
				$(copyText).prop('disabled', false);
				copyText.select();
				document.execCommand("copy");
				$(copyText).prop('disabled', true);
				$(this).text('Coppied');
				setTimeout(function () {
					$(_this).text('');
					$(_this).html('<i class="fas fa-copy"></i>');
				}, 500);
			});

			$.uploadPreview({
				input_field: "#image-upload",
				preview_box: "#image-preview",
				label_field: "#image-label",
				label_default: "Choose File",
				label_selected: "Change File",
				no_label: false
			});
		});
	</script>
@endsection
