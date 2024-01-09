@extends('user.layouts.master')
@section('page_title',__('Request Money'))

@section('content')
<div class="main-content">
	<section class="section">
		<div class="section-header">
			<h1>@lang('Request Money')</h1>
			<div class="section-header-breadcrumb">
				<div class="breadcrumb-item active">
					<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
				</div>
				<div class="breadcrumb-item">@lang('Request Money')</div>
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
					<div class="col-md-12">
						<div class="card card-primary shadow mb-4">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
								<h6 class="m-0 font-weight-bold text-primary">@lang('Request Money')</h6>
							</div>
							<div class="card-body">
								<form action="{{ route('requestMoney.initialize') }}" method="post">
									@csrf
									<div class="form-group">
										<label for="recipient">@lang('Recipient email or username')</label>
										<input type="text" name="recipient" id="recipient"
											   placeholder="@lang('Please enter valid email or username')" autocomplete="off"
											   value="{{ old('recipient') }}"
											   class="form-control @error('recipient') is-invalid @enderror">
										<div class="invalid-feedback">
											@error('recipient') @lang($message) @enderror
										</div>
										<div class="valid-feedback"></div>
									</div>
									<div class="row">
										<div class="col-md-6">
											<div class="form-group search-currency-dropdown">
												<label for="currency">@lang('Currency')</label>
												<select id="currency" name="currency"
														class="form-control @error('currency') is-invalid @enderror">
													@foreach($currencies as $key => $currency)
														<option value="{{ $currency->id }}"
																data-currencytype="{{ $currency->currency_type }}" {{ ($currency->is_default == 1) ? 'selected' : '' }}>
															{{ __($currency->code) }} - {{ __($currency->name) }}</option>
													@endforeach
												</select>
												<div class="invalid-feedback">
													@error('currency') @lang($message) @enderror
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="amount">@lang('Amount')</label>
												<input type="text" id="amount" value="{{ old('amount') }}" name="amount"
													   placeholder="@lang('0.00')"
													   class="form-control @error('amount') is-invalid @enderror"
													   autocomplete="off">
												<div class="invalid-feedback">
													@error('amount') @lang($message) @enderror
												</div>
												<div class="valid-feedback"></div>
											</div>
										</div>
									</div>
									<div class="form-group">
										<label for="note">@lang('Note')</label>
										<textarea name="note" rows="5" class="form-control form-control-sm"></textarea>
									</div>
									<div class="form-group mt-3 security-block">
										@if(in_array('request',$enable_for))
											<label for="security_pin">@lang('Security Pin')</label>
											<input type="password" name="security_pin"
												   placeholder="@lang('Please enter your security PIN')" autocomplete="off"
												   value="{{ old('security_pin') }}"
												   class="form-control @error('security_pin') is-invalid @enderror">
											<div class="invalid-feedback">
												@error('security_pin') @lang($message) @enderror
											</div>
											<div class="valid-feedback"></div>
										@endif
									</div>
									<button type="submit" id="submit" class="btn btn-primary btn-sm btn-block"
											disabled>@lang('Request Money')</button>
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

@section('scripts')
    <script>
        'use strict';
        $(document).ready(function () {
            let recipientField = $('#recipient');
            let amountField = $('#amount');
            let recipientStatus = false;
            let amountStatus = false;

            function clearMessage(fieldId) {
                $(fieldId).removeClass('is-valid')
                $(fieldId).removeClass('is-invalid')
                $(fieldId).closest('div').find(".invalid-feedback").html('');
                $(fieldId).closest('div').find(".is-valid").html('');
            }

            //for receiver check
            $(document).on('input', "#recipient", function (e) {
                let recipient = $(recipientField).val();
                if (recipient === '') {
                    clearMessage(recipientField)
                }
                if (recipient.length >= 4) {
                    checkRecipient();
                }
            });

            function checkRecipient() {
                $.ajax({
                    method: "GET",
                    url: "{{ route('requestMoney.checkRecipient') }}",
                    dataType: "json",
                    data: {
                        'recipient': recipientField.val()
                    }
                })
                    .done(function (response) {
                        if (response.status) {
                            clearMessage(recipientField)
                            $(recipientField).addClass('is-valid')
                            $(recipientField).closest('div').find(".valid-feedback").html(response.message)
                            recipientStatus = true;
                            submitButton()
                        } else {
                            recipientStatus = false;
                            submitButton()
                            clearMessage(recipientField)
                            $(recipientField).addClass('is-invalid')
                            $(recipientField).closest('div').find(".invalid-feedback").html(response.message);
                        }
                    });
            }

            //for amount limit check
            $(document).on('change, input', "#amount, #charge_from, #currency", function (e) {
                let amount = amountField.val();
                let currency_id = $('#currency').val();
                let transaction_type_id = "{{ config('transactionType.request') }}"; //request
                let currency_type = $('#currency option:selected').data('currencytype');
                let charge_from = 0;
                if ($("#charge_from").is(':checked')) {
                    charge_from = 1;
                }

                if (!isNaN(amount) && amount > 0) {
                    let fraction = amount.split('.')[1];
                    let limit = currency_type == 0 ? 8 : 2;

                    if (fraction && fraction.length > limit) {
                        amount = (Math.floor(amount * Math.pow(10, limit)) / Math.pow(10, limit)).toFixed(limit);
                        amountField.val(amount);
                    }
                    checkAmount(amount, currency_id, transaction_type_id, charge_from)
                } else {
                    clearMessage(amountField);
                    $('.showCharge').html('');
                }
            });

            function checkAmount(amount, currency_id, transaction_type_id, charge_from) {
                $.ajax({
                    method: "GET",
                    url: "{{ route('requestMoney.checkAmount') }}",
                    dataType: "json",
                    data: {
                        'amount': amount,
                        'currency_id': currency_id,
                        'transaction_type_id': transaction_type_id,
                        'charge_from': charge_from,
                    }
                })
                    .done(function (response) {
                        let amountField = $('#amount');
                        if (response.status) {
                            clearMessage(amountField)
                            $(amountField).addClass('is-valid')
                            $(amountField).closest('div').find(".valid-feedback").html(response.message)
                            amountStatus = true;
                            submitButton()
                        } else {
                            amountStatus = false;
                            submitButton()
                            clearMessage(amountField)
                            $(amountField).addClass('is-invalid')
                            $(amountField).closest('div').find(".invalid-feedback").html(response.message);
                        }
                    });
            }

            function submitButton() {
                if (recipientStatus && amountStatus) {
                    $("#submit").removeAttr("disabled");
                } else {
                    $("#submit").attr("disabled", true);
                }
            }
        });
    </script>
@endsection
