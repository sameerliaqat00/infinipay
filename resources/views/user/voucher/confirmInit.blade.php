@extends('user.layouts.master')
@section('page_title',__('Preview Voucher Request'))

@section('content')
<div class="main-content">
	<section class="section">
		<div class="section-header">
			<h1>@lang('Preview Voucher Request')</h1>
			<div class="section-header-breadcrumb">
				<div class="breadcrumb-item active">
					<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
				</div>
				<div class="breadcrumb-item">@lang('Preview Voucher Request')</div>
			</div>
		</div>

		<div class="row mb-3">
			<div class="container-fluid" id="container-wrapper">
				<div class="row justify-content-md-center">
					<div class="col-md-12">
						<div class="card mb-4 card-primary shadow">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-center">
								<h6 class="m-0 font-weight-bold text-primary">@lang('Preview Voucher Request')</h6>
							</div>
							<div class="card-body">
								<form action="{{ route('voucher.confirmInit',$utr) }}" method="post">
									@csrf
									<ul class="list-group">
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Request Amount')</span>
											<span>{{ (getAmount($voucher->transfer_amount)) }} {{ __(optional($voucher->currency)->code) }}</span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Percentage Charge') ({{ getAmount($voucher->percentage) }}%)</span>
											<span>{{ (getAmount($voucher->charge_percentage)) }} {{ __(optional($voucher->currency)->code) }}</span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Fixed Charge')</span>
											<span>{{ (getAmount($voucher->charge_fixed)) }} {{ __(optional($voucher->currency)->code) }}</span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Total Charge')</span>
											<span>{{ (getAmount($voucher->charge)) }} {{ __(optional($voucher->currency)->code) }}</span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Receivable Amount')</span>
											<span>{{ (getAmount($voucher->received_amount)) }} {{ __(optional($voucher->currency)->code) }}</span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Note')</span>
											<span> {{ __($voucher->note) }} </span>
										</li>
									</ul>
									<div class="form-group mt-3 security-block">
										@if(in_array('voucher', $enable_for))
											<label for="security_pin">@lang('Security Pin')</label>
											<input type="password" name="security_pin" placeholder="@lang('Please enter your security PIN')" autocomplete="off"
												   value="{{ old('security_pin') }}" class="form-control @error('security_pin') is-invalid @enderror">
											<div class="invalid-feedback">
												@error('security_pin') @lang($message) @enderror
											</div>
											<div class="valid-feedback"></div>
										@endif
									</div>
									<button type="submit" id="submit" class="btn btn-primary btn-sm btn-block btn-security">@lang('Confirm')</button>
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
