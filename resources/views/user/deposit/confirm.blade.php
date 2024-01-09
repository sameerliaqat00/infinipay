@extends('user.layouts.master')
@section('page_title',__('Preview Add Fund'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Preview Add Fund')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Preview Add Fund')</div>
				</div>
			</div>

			<div class="row">
				<div class="container-fluid" id="container-wrapper">
					<div class="row justify-content-md-center">
						<div class="col-lg-6">
							<div class="card mb-4 shadow card-primary">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-center">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Preview Deposit')</h6>
								</div>
								<div class="card-body">
									<form action="{{ route('deposit.confirm',$utr) }}" method="post">
										@csrf

										<div class="text-center">
											<img class="rounded mb-5" src="{{ getFile(config('location.gateway.path').optional($deposit->gateway)->image) }}" width="109">
										</div>

										<ul class="list-group">
											<li class="list-group-item list-group-item-action d-flex justify-content-between">
												<span>@lang('Gateway')</span>
												<span>{{ __(optional($deposit->gateway)->name) }} </span>
											</li>
											<li class="list-group-item list-group-item-action d-flex justify-content-between">
												<span>@lang('Name')</span>
												<span> {{ __($deposit->receiver->name) }} </span>
											</li>
											<li class="list-group-item list-group-item-action d-flex justify-content-between">
												<span>@lang('Currency')</span>
												<span>{{ __($deposit->currency->code) }}</span>
											</li>
											<li class="list-group-item list-group-item-action d-flex justify-content-between">
												<span>@lang('Amount')</span>
												<span>{{ (getAmount($deposit->amount)) }} {{ __(optional($deposit->currency)->code)  }}</span>
											</li>
											<li class="list-group-item list-group-item-action d-flex justify-content-between">
												<span>@lang('Charge')</span>
												<span>{{ (getAmount($deposit->charge)) }} {{ __(optional($deposit->currency)->code)  }}</span>
											</li>
											<li class="list-group-item list-group-item-action d-flex justify-content-between">
												<span>@lang('Payable amount')</span>
												<span>{{ (getAmount($deposit->amount + $deposit->charge)) }} {{ __(optional($deposit->currency)->code)  }}</span>
											</li>
										</ul>
										<div class="form-group mt-3 security-block">
											@if(in_array('deposit',$enable_for))
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
