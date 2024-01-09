@extends('admin.layouts.master')
@section('page_title', __('Edit Virtual Card'))
@push('extra_styles')
	<link href="{{ asset('assets/dashboard/css/select2.min.css') }}" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="{{ asset('assets/store/css/bootstrap-select.min.css') }}">
@endpush
@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang("Edit") @lang($virtualCardMethod->name)</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang("Edit") @lang($virtualCardMethod->name)</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row">
						<div class="col-lg-12">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang("Edit") @lang($virtualCardMethod->name)</h6>
									<a href="{{ route('admin.virtual.card') }}" class="btn btn-sm btn-outline-primary">
										<i
											class="fas fa-arrow-left"></i> @lang('Back')</a>
								</div>
								<div class="card-body">
									<form method="post"
										  action="{{route('admin.virtual.cardUpdate',$virtualCardMethod->id)}}"
										  enctype="multipart/form-data">
										@csrf
										@method('put')
										@if($virtualCardMethod->alert_message)
											<div class="row">
												<div class="col-md-12">
													<label class="text-warning">{{$virtualCardMethod->alert_message}}</label>
												</div>
											</div>
										@endif
										<div class="row">
											<div class="col-md-4">
												<div class="form-group">
													<label for="name">@lang('Name')</label>
													<input type="text" value="@lang($virtualCardMethod->name)"
														   name="name"
														   class="form-control mt-2 @error('name') is-invalid @enderror"
														   disabled required>
													<div class="invalid-feedback">
														@error('name') @lang($message) @enderror
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label for="currency">@lang('Currency')</label>
													<select
														class="form-select form-control @error('currency') is-invalid @enderror"
														name="currency[]" multiple="multiple" id="selectCurrency"
														required>
														@foreach($virtualCardMethod->currencies as $key => $currency)
															@foreach($currency as $curKey => $singleCurrency)
																<option value="{{ $curKey }}"
																		{{ in_array($curKey,$virtualCardMethod->currency) == true ? 'selected' : '' }} data-fiat="{{ $key }}">{{ trans($curKey) }}</option>
															@endforeach
														@endforeach
													</select>
													<div class="invalid-feedback">
														@error('currency') @lang($message) @enderror
													</div>
												</div>
											</div>

											<div class="col-md-4">
												<div class="form-group">
													<label for="currency">@lang('Debit Currency')</label>
													<select
														class="form-select form-control db_currency mt-2 @error('currency') is-invalid @enderror"
														name="debit_currency"
														required>
														@foreach($virtualCardMethod->currencies as $key => $currency)
															@foreach($currency as $curKey => $singleCurrency)
																<option value="{{ $curKey }}"
																		{{ old('debit_currency', $virtualCardMethod->debit_currency) == $curKey ? 'selected' : '' }} data-fiat="{{ $key }}">{{ trans($curKey) }}</option>
															@endforeach
														@endforeach
													</select>
													<div class="invalid-feedback">
														@error('debit_currency') @lang($message) @enderror
													</div>
												</div>
											</div>
										</div>
										@if($virtualCardMethod->add_fund_parameter)
											<div class="row mt-3">
												@foreach($virtualCardMethod->add_fund_parameter as $key => $currency)
													<div
														class="col-md-6 {{in_array($key,$virtualCardMethod->currency)?'':'d-none'}}">
														<div class="card card-primary shadow params-color">
															<div
																class="card-header text-dark font-weight-bold">{{$key}} @lang('Charges and limits')</div>
															<div class="card-body">
																<div class="row">
																	@foreach($currency as $key1 => $parameter)
																		<div class="col-md-6">
																			<div class="form-group">
																				<label
																					for="{{ $parameter->field_level }}">{{ __(ucfirst(str_replace('_',' ', $parameter->field_level))) }}</label>
																				<div class="input-group">
																					<input type="text"
																						   name="fund[{{$key}}][{{$key1}}]"
																						   class="form-control"
																						   value="{{$parameter->field_value}}">
																					<div
																						class="input-group-prepend">
																		<span
																			class="form-control">{{$parameter->field_level == 'Percent Charge' ? '%':$key}}</span>
																					</div>
																				</div>
																			</div>
																		</div>
																	@endforeach
																</div>
															</div>
														</div>
													</div>
												@endforeach
											</div>
										@endif
										<div class="row mt-4">
											@foreach ($virtualCardMethod->parameters as $key => $parameter)
												<div class="col-md-4">
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
										@if($virtualCardMethod->extra_parameters)
											<div class="row">
												@foreach($virtualCardMethod->extra_parameters as $key => $param)
													<div class="col-md-4">
														<div class="form-group">
															<label
																for="{{ $key }}">{{ __(strtoupper(str_replace('_',' ', $key))) }}</label>
															<div class="input-group">
																<input type="text" name=""
																	   class="form-control"
																	   value="{{$param}}"
																	   disabled>
															</div>
														</div>
													</div>
												@endforeach
											</div>
										@endif
										<div class="row">
											<div class="col-md-12">
												<label>@lang('Information Box')</label>
												<div class="input-group input-group-sm">
													<textarea class="form-control"
															  name="info_box">{{$virtualCardMethod->info_box}}</textarea>
												</div>
												<div class="invalid-feedback">
													@error('info_box') @lang($message) @enderror
												</div>
											</div>
										</div>
										<div class="row align-items-center">
											<div class="col-md-6">
												<div class="form-group mb-4">
													<label class="col-form-label">@lang('Image')</label>
													<div id="image-preview" class="image-preview"
														 style="background-image: url({{ getFile(config('location.virtualCardMethod.path') . $virtualCardMethod->image) ? : 0 }});">
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
	<script src="{{ asset('assets/store/js/bootstrap-select.min.js') }}"></script>
	<script src="{{ asset('assets/dashboard/js/jquery.uploadPreview.min.js') }}"></script>
@endpush
@section('scripts')
	<script>
		'use strict'
		$(document).ready(function () {
			$(function () {
				$('#selectCurrency').selectpicker();
			});

			$(document).on('change', '.db_currency', function () {
				$('.currencyChange').text($(this).val());
			})

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
