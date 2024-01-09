@extends('user.layouts.master')
@section('page_title',__('Send Payout Request'))
@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Send Payout Request')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Send Payout Request')</div>
				</div>
			</div>
			<!------ alert ------>
			<div class="row ">
				<div class="col-md-12">
					<div class="bd-callout bd-callout-primary mx-2">
						<i class="fa-3x fas fa-info-circle text-primary"></i> @lang(@$template->description->short_description)
					</div>
				</div>
			</div>


			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row justify-content-md-center">
						<div class="col-md-6">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Choose a payout method')</h6>
								</div>
								<div class="card-body payout">
									<form action="{{ route('payout.request') }}" method="post">
										@csrf
										<div class="row">
											<div class="col-md-12">
												<label for="methodId">@lang('Payout Method')</label>
												<div class="form-group">
													@foreach($payoutMethods as $key => $value)
														<div class="form-check form-check-inline mb-4 ml-2">
															<input class="form-check-input" type="radio" name="methodId"
																   id="{{ $key }}"
																   value="{{ $value->id }}" {{ old('methodId') == $value->id ? ' checked' : ''}}>
															<label class="form-check-label" for="{{ $key }}">
																<img
																	src="{{ getFile(config('location.methodLogo.path').$value->logo) }}">
																<span>{{ __($value->methodName) }}</span>
															</label>
														</div>
													@endforeach
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label for="amount">@lang('Amount')</label>
													<div class="input-group input-group-sm">
														<input type="text" name="amount" value="{{ old('amount') }}"
															   placeholder="@lang('Enter Amount')"
															   class="form-control @error('amount') is-invalid @enderror">
														<div class="input-group-prepend">
															<span
																class="input-group-text">@lang(optional($baseControl->currency)->code)</span>
														</div>
													</div>
													<div
														class="invalid-feedback">@error('amount') @lang($message) @enderror</div>
												</div>
											</div>
										</div>
										<button type="submit" id="submit"
												class="btn btn-primary btn-sm btn-block">@lang('Continue')</button>
									</form>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Details')</h6>
								</div>
								<div class="card-body showCharge d-none">
									<ul class="list-group">
										<li class="list-group-item d-flex justify-content-between align-items-center">
											<span>@lang('Fixed charge')</span>
											<span class="text-danger" id="fixed_charge"></span>
										</li>
										<li class="list-group-item d-flex justify-content-between align-items-center">
											<span>@lang('Percentage charge')</span><span class="text-danger"
																						 id="percentage_charge"></span>
										</li>
										<li class="list-group-item d-flex justify-content-between align-items-center">
											<span>@lang('Min limit')</span>
											<span class="text-info" id="min_limit"></span>
										</li>
										<li class="list-group-item d-flex justify-content-between align-items-center">
											<span>@lang('Max limit')</span>
											<span class="text-info" id="max_limit"></span>
										</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

		</section>
	</div>
@endsection

@section('scripts')
	<script>
		'use strict';
		$(document).ready(function () {
			$(document).on('input', 'input[name="amount"]', function () {
				let limit = '{{ optional($baseControl->currency)->currency_type == 0 ? 8 : 2 }}';
				let amount = $(this).val();
				let fraction = amount.split('.')[1];
				if (fraction && fraction.length > limit) {
					amount = (Math.floor(amount * Math.pow(10, limit)) / Math.pow(10, limit)).toFixed(limit);
					$(this).val(amount);
				}
			});

			$(document).on('change', "input[type=radio][name=methodId]", function (e) {
				let methodId = this.value;
				$.ajax({
					method: "GET",
					url: "{{ route('payout.checkLimit') }}",
					dataType: "json",
					data: {'methodId': methodId}
				})
					.done(function (response) {
						let amountField = $('#amount');
						if (response.status) {
							$('.showCharge').removeClass('d-none');
							$('#fixed_charge').html(response.fixed_charge + ' ' + response.currency_code);
							$('#percentage_charge').html(response.percentage_charge + ' ' + response.currency_code);
							$('#min_limit').html(parseFloat(response.min_limit).toFixed(response.currency_limit) + ' ' + response.currency_code);
							$('#max_limit').html(parseFloat(response.max_limit).toFixed(response.currency_limit) + ' ' + response.currency_code);
						} else {
							$('.showCharge').addClass('d-none');
						}
					});
			});
		});
	</script>
@endsection
