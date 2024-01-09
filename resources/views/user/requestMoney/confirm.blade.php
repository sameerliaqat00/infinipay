@extends('user.layouts.master')
@section('page_title',__('Confirm Transfer'))

@section('content')
<div class="main-content">
	<section class="section">
		<div class="section-header">
			<h1>@lang('Confirm Transfer')</h1>
			<div class="section-header-breadcrumb">
				<div class="breadcrumb-item active">
					<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
				</div>
				<div class="breadcrumb-item">@lang('Confirm Transfer')</div>
			</div>
		</div>

		<div class="row mb-3">
			<div class="container-fluid" id="container-wrapper">
				<div class="row justify-content-md-center">
					<div class="col-md-12">
						<div class="card shadow card-primary mb-4">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-center">
								<h6 class="m-0 font-weight-bold text-primary">@lang('Confirm Transfer')</h6>
							</div>
							<div class="card-body">
								<form action="{{ route('requestMoney.confirm',[$utr]) }}" method="post">
									@csrf
									<ul class="list-group">
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Name')</span>
											<span> {{ __(optional($requestMoney->sender)->name) }} </span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Currency')</span>
											<span>{{ __(optional($requestMoney->currency)->code) }}</span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Percentage charge') ({{ (getAmount($requestMoney->percentage)) }}@lang('%')</span>
											<span>{{ (getAmount($requestMoney->charge_percentage)) }}</span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Fixed charge')</span>
											<span>{{ (getAmount($requestMoney->charge_fixed)) }}</span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Total charge')</span>
											<span>{{ (getAmount($requestMoney->charge)) }}</span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Payable amount')</span>
											<span>{{ (getAmount($requestMoney->transfer_amount)) }}</span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Receiver will received')</span>
											<span>{{ (getAmount($requestMoney->received_amount)) }}</span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Charge deduct from')</span>
											<span>{{ $requestMoney->charge_from == 1 ? __('Receiver') : __('Sender') }}</span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Note')</span>
											<span> {{ __($requestMoney->note) }} </span>
										</li>
									</ul>
									<div class="form-group mt-3 security-block">
										@if(in_array('transfer',$enable_for))
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
