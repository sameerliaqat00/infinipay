@extends('admin.layouts.master')
@section('page_title', __('Edit Charge'))
@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Edit Charges & Limit')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item"><a href="{{ route('currency.index') }}">@lang('Currency')</a></div>
					<div class="breadcrumb-item">@lang('Edit Charges')</div>
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
											<h6 class="m-0 font-weight-bold text-primary">@lang("Charges & Limit For") {{ __(ucfirst(array_search($chargesLimit->transaction_type_id, config('transactionType')))) }} </h6>
											<a href="{{route('currency.index')}}" class="btn btn-sm btn-outline-primary"> <i
													class="fas fa-arrow-left"></i> @lang('Back')</a>
										</div>
										<div class="card-body">
											<form method="post" action="{{ route('charge.update',$chargesLimit) }}">
												@csrf
												@method('PUT')
												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<label for="min_limit">@lang('Min Limit')</label>
															<div class="input-group input-group-sm">
																<input type="text" name="min_limit"
																	   placeholder="@lang('eg:- 0.00')"
																	   value="{{ getAmount($chargesLimit->min_limit) }}"
																	   class="form-control @error('min_limit') is-invalid @enderror">
																<div class="input-group-append"><span
																		class="input-group-text">{{ __(optional($chargesLimit->currency)->code) }}</span>
																</div>
																<div
																	class="invalid-feedback">@error('min_limit') @lang($message) @enderror</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label for="max_limit">@lang('Max Limit')</label>
															<div class="input-group input-group-sm">
																<input type="text" name="max_limit"
																	   placeholder="@lang('eg:- 0.00')"
																	   value="{{ getAmount($chargesLimit->max_limit) }}"
																	   class="form-control @error('max_limit') is-invalid @enderror">
																<div class="input-group-append"><span
																		class="input-group-text">{{ __(optional($chargesLimit->currency)->code) }}</span>
																</div>
																<div
																	class="invalid-feedback">@error('max_limit') @lang($message) @enderror</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label
																for="percentage_charge">@lang('Charge Percentage')</label>
															<div class="input-group input-group-sm">
																<input type="text" name="percentage_charge"
																	   placeholder="@lang('eg:- 0.00')"
																	   value="{{ getAmount($chargesLimit->percentage_charge) }}"
																	   class="form-control @error('percentage_charge') is-invalid @enderror">
																<div class="input-group-append"><span
																		class="input-group-text">@lang('%')</span></div>
																<div
																	class="invalid-feedback">@error('percentage_charge') @lang($message) @enderror</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label for="fixed_charge">@lang('Charge Fixed')</label>
															<div class="input-group input-group-sm">
																<input type="text" name="fixed_charge"
																	   placeholder="@lang('eg:- 0.00')"
																	   value="{{ getAmount($chargesLimit->fixed_charge) }}"
																	   class="form-control @error('fixed_charge') is-invalid @enderror">
																<div class="input-group-append"><span
																		class="input-group-text">{{ __(optional($chargesLimit->currency)->code) }}</span>
																</div>
																<div
																	class="invalid-feedback">@error('fixed_charge') @lang($message) @enderror</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label>@lang('Enable Charge')</label>
															<div class="selectgroup w-100">
																<label class="selectgroup-item">
																	<input type="radio" name="is_active" value="0"
																		   class="selectgroup-input" {{ old('is_active', $chargesLimit->is_active) == 0 ? 'checked' : ''}}>
																	<span class="selectgroup-button">@lang('OFF')</span>
																</label>
																<label class="selectgroup-item">
																	<input type="radio" name="is_active" value="1"
																		   class="selectgroup-input" {{ old('is_active', $chargesLimit->is_active) == 1 ? 'checked' : ''}}>
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
												</div>
												<button type="submit"
														class="btn btn-primary btn-sm btn-block">@lang('Save Changes')</button>
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
