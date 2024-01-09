@extends('user.layouts.master')
@section('page_title',__('Payout'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Payout')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Payout')</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row justify-content-md-center">
						<div class="col-md-6">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Payout')</h6>
								</div>
								<div class="card-body">
									<form action="{{ route('payout.confirm',$payout->utr) }}" method="post"
										  enctype="multipart/form-data">
										@csrf
										@if($payoutMethod->supported_currency)
											<div class="row">
												<div class="col-md-12">
													<div class="form-group search-currency-dropdown">
														<label for="from_wallet">@lang('Select Bank Currency')</label>
														<select id="from_wallet" name="currency_code"
																class="form-control form-control-sm transfer-currency"
																required>
															<option value="" disabled=""
																	selected="">@lang('Select Currency')</option>
															@foreach($payoutMethod->supported_currency as $singleCurrency)
																<option
																	value="{{$singleCurrency}}"
																	@foreach($payoutMethod->convert_rate as $key => $rate)
																		@if($singleCurrency == $key) data-rate="{{$rate}}" @endif
																	@endforeach {{old('transfer_name') == $singleCurrency ?'selected':''}}>{{$singleCurrency}}</option>
															@endforeach
														</select>
														@error('currency_code')
														<span class="text-danger">{{$message}}</span>
														@enderror
													</div>
												</div>
											</div>
										@endif
										@if($payoutMethod->code == 'paypal')
											<div class="row">
												<div class="col-md-12">
													<div class="form-group search-currency-dropdown">
														<label for="from_wallet">@lang('Select Recipient Type')</label>
														<select id="from_wallet" name="recipient_type"
																class="form-control form-control-sm" required>
															<option value="" disabled=""
																	selected="">@lang('Select Recipient')</option>
															<option value="EMAIL">@lang('Email')</option>
															<option value="PHONE">@lang('phone')</option>
															<option value="PAYPAL_ID">@lang('Paypal Id')</option>
														</select>
														@error('recipient_type')
														<span class="text-danger">{{$message}}</span>
														@enderror
													</div>
												</div>
											</div>
										@endif
										@if(isset($payoutMethod->inputForm))
											@foreach(json_decode($payoutMethod->inputForm) as $key => $value)
												@if($value->type == 'text')
													<div class="form-group">
														<label for="{{ $value->name }}">@lang($value->label)</label>
														<input type="text" name="{{ $value->name }}"
															   placeholder="{{ __($value->label) }}"
															   autocomplete="off"
															   value="{{ old($value->name) }}"
															   class="form-control @error($value->name) is-invalid @enderror">
														<div
															class="invalid-feedback">@error($value->name) @lang($message) @enderror</div>
													</div>
												@elseif($value->type == 'textarea')
													<div class="form-group">
														<label for="{{ $value->name }}">@lang($value->label)</label>
														<textarea
															class="form-control @error($value->name) is-invalid @enderror"
															name="{{$value->name}}"
															rows="5">{{ old($value->name) }}</textarea>
														<div
															class="invalid-feedback">@error($value->name) @lang($message) @enderror</div>
													</div>
												@elseif($value->type == 'file')
													<div class="form-group mb-4">
														<label class="col-form-label">@lang('Choose File')</label>
														<div id="image-preview" class="image-preview">
															<label for="image-upload"
																   id="image-label">@lang('Choose File')</label>
															<input type="file" name="{{ $value->name }}"
																   class="@error($value->name) is-invalid @enderror"
																   id="image-upload"/>
														</div>
														<div class="invalid-feedback">
															@error($value->name) @lang($message) @enderror
														</div>
													</div>
												@endif
											@endforeach
										@endif
										<div class="form-group mt-3 security-block">
											@if(in_array('payout', $enable_for))
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
												class="btn btn-primary btn-sm btn-block">@lang('Send Request')</button>
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
								<div class="card-body">
									<ul class="list-group">
										<li class="list-group-item d-flex justify-content-between align-items-center">
											<span>@lang('Payout Method')</span>
											<span class="text-info">{{ __($payoutMethod->methodName) }} </span>
										</li>
										<li class="list-group-item d-flex justify-content-between align-items-center">
											<span>@lang('Request Amount')</span>
											<span
												class="text-success">{{ (getAmount($payout->amount)) }} {{ __(optional($payout->currency)->code) }}</span>
										</li>
										<li class="list-group-item d-flex justify-content-between align-items-center">
											<span>@lang('Charge Amount')</span>
											<span
												class="text-danger">{{ (getAmount($payout->charge)) }} {{ __(optional($payout->currency)->code) }}</span>
										</li>
										<li class="list-group-item d-flex justify-content-between align-items-center">
											<span>@lang('Total Payable')</span>
											<span
												class="text-danger">{{ (getAmount($payout->transfer_amount)) }} {{ __(optional($payout->currency)->code) }}</span>
										</li>
										<li class="list-group-item d-flex justify-content-between align-items-center">
											<span>@lang('Available Balance')</span>
											<span
												class="text-success">{{ (getAmount($wallet->balance - $payout->transfer_amount)) }} {{ __(optional($payout->currency)->code) }}</span>
										</li>
										<div class="dynamic">

										</div>
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

@push('extra_scripts')
	<script src="{{ asset('assets/dashboard/js/jquery.uploadPreview.min.js') }}"></script>
@endpush
@section('scripts')
	<script type="text/javascript">
		var bankName = null;
		var payAmount = '{{$payout->amount}}'
		var baseCurrency = "{{config('basic.base_currency_code')}}"

		$(document).on("change", ".transfer-currency", function () {
			let currencyCode = $(this).val();
			let rate = $(this).find(':selected').data('rate');
			let getAmount = parseFloat(rate) * parseFloat(payAmount);
			var output = null;
			$('.dynamic').html('');
			output = `<li class="list-group-item d-flex justify-content-between align-items-center">
						<span>@lang('Exchange rate')</span>
							<span class="text-primary">1 ${baseCurrency} = ${rate} ${currencyCode}</span></li>
					  <li class="list-group-item d-flex justify-content-between align-items-center">
					    <span>@lang('You will get')</span>
					      <span class="text-success">${getAmount} ${currencyCode}</span></li>`

			$('.dynamic').html(output);
		})

		$(document).ready(function () {
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
