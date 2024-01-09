@extends('user.layouts.master')
@section('page_title',__('Lets Payment'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Lets Payment')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Lets Payment')</div>
				</div>
			</div>

			<!------ main content ------>
			<div class="row justify-content-md-center">
				<div class="col-md-8">
					<div class="card mb-4 shadow card-primary">
						<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
							<h6 class="m-0 font-weight-bold text-primary">@lang('Preview Bill Payment')</h6>
						</div>
						<div class="card-body">
							<form action="{{ route('pay.bill.confirm',$billPay->utr) }}" method="post">
								@csrf
								<li class="list-group-item d-flex justify-content-between">
									<span>{{ __('Category') }}</span>
									<span>{{str_replace('_',' ',ucfirst($billPay->category_name))}}</span>
								</li>
								<li class="list-group-item d-flex justify-content-between">
									<span>{{ __('Service') }}</span>
									<span>{{optional($billPay->service)->type}}</span>
								</li>
								<li class="list-group-item d-flex justify-content-between">
									<span>{{ __('Country Code') }}</span>
									<span>{{$billPay->country_name}}</span>
								</li>
								<li class="list-group-item d-flex justify-content-between">
									<span>{{ __('From Wallet') }}</span>
									<span>{{optional($billPay->walletCurrency)->code}} - {{optional($billPay->walletCurrency)->name}}</span>
								</li>
								<li class="list-group-item d-flex justify-content-between">
									<span>{{ __('Exchange Rate') }}</span>
									<span>1 {{optional($billPay->walletCurrency)->code}} = {{getAmount($billPay->exchange_rate,2)}} {{$billPay->currency}}</span>
								</li>
								<li class="list-group-item d-flex justify-content-between">
									<span>{{ __('Amount') }}</span>
									<span
										class="text-info">{{getAmount($billPay->amount,2)}} {{$billPay->currency}}</span>
								</li>
								<li class="list-group-item d-flex justify-content-between">
									<span>{{ __('Charge') }}</span>
									<span
										class="text-danger">{{getAmount($billPay->charge,2)}} {{$billPay->currency}}</span>
								</li>
								<li class="list-group-item d-flex justify-content-between">
									<span>{{ __('Payable Amount') }}</span>
									<span
										class="text-info">{{getAmount(($billPay->payable_amount / $billPay->exchange_rate) + ($billPay->charge / $billPay->exchange_rate),2)}} {{optional($billPay->walletCurrency)->code}}</span>
								</li>
								<div class="form-group mt-3 security-block">
									@if(in_array('bill_payment', $enable_for))
										<label for="security_pin">@lang('Security Pin')</label>
										<input type="password" name="security_pin"
											   placeholder="@lang('Please enter your security PIN')"
											   autocomplete="off"
											   value="{{ old('security_pin') }}"
											   class="form-control @error('security_pin') is-invalid @enderror">
										<div class="invalid-feedback">
											@error('security_pin') @lang($message) @enderror
										</div>
										<div class="valid-feedback"></div>
									@endif
								</div>
								<button type="submit" id="submit"
										class="btn btn-primary btn-sm btn-block">@lang('Pay')</button>
							</form>
						</div>
					</div>
				</div>
			</div>

		</section>
	</div>
@endsection

@section('scripts')
	@if ($errors->any())
		@php
			$collection = collect($errors->all());
			$errors = $collection->unique();
		@endphp
		<script>
			"use strict";
			@foreach ($errors as $error)
			Notiflix.Notify.Failure("{{ trans($error) }}");
			@endforeach
		</script>
	@endif
@endsection
