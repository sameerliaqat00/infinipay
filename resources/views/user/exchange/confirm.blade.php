@extends('user.layouts.master')
@section('page_title',__('Confirm Exchange'))

@section('content')
<div class="main-content">
	<section class="section">
		<div class="section-header">
			<h1>@lang('Confirm Exchange')</h1>
			<div class="section-header-breadcrumb">
				<div class="breadcrumb-item active">
					<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
				</div>
				<div class="breadcrumb-item">@lang('Confirm Exchange')</div>
			</div>
		</div>

		<div class="row mb-3">
			<div class="container-fluid" id="container-wrapper">
				<div class="row justify-content-md-center">
					<div class="col-lg-12">
						<div class="card mb-4 shadow card-primary">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-center">
								<h6 class="m-0 font-weight-bold text-primary">@lang('Confirm Exchange')</h6>
							</div>
							<div class="card-body">
								<form action="{{ route('exchange.confirm',$utr) }}" method="post">
									@csrf
									<ul class="list-group">
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Exchange')</span><span> {{ __(optional(optional($exchange->fromWallet)->currency)->code) }} @lang('to') {{ __(optional(optional($exchange->toWallet)->currency)->code) }} </span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Exchange Rate')</span>
											<span> @lang('1') {{ __(optional(optional($exchange->fromWallet)->currency)->code) }} @lang('=') {{ (getAmount($exchange->exchange_rate)) .' '. __(optional(optional($exchange->toWallet)->currency)->code) }} </span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Percentage charge') ({{ (getAmount($exchange->percentage)) }}@lang('%'))</span>
											<span>{{ (getAmount($exchange->charge_percentage)) .' '. __(optional(optional($exchange->fromWallet)->currency)->code) }}</span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Fixed charge')</span>
											<span>{{ (getAmount($exchange->charge_fixed))  .' '. __(optional(optional($exchange->fromWallet)->currency)->code) }}</span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Total charge')</span>
											<span>{{ (getAmount($exchange->charge))  .' '. __(optional(optional($exchange->fromWallet)->currency)->code) }}</span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Payable amount')</span><span>
												{{ (getAmount($exchange->transfer_amount)) }} {{ __(optional(optional($exchange->fromWallet)->currency)->code) }}
											</span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('You will get')</span><span>
												{{ (getAmount($exchange->received_amount)) }}
												{{ __(optional(optional($exchange->toWallet)->currency)->code) }}
											</span>
										</li>
									</ul>
									<div class="form-group mt-3 security-block">
										@if(in_array('exchange',$enable_for))
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
