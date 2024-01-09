@extends('user.layouts.master')
@section('page_title', __('Confirm Request'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Confirm Request')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Confirm Request')</div>
				</div>
			</div>

			<div class="row justify-content-md-center">
				<div class="col-md-6">
					<div class="card card-primary mb-4 shadow">
						<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
							<h6 class="m-0 font-weight-bold text-primary">@lang('Confirm Request')</h6>
						</div>
						<div class="card-body">
							<form action="{{ route('requestMoney.check',[$requestMoney->utr]) }}" method="post">
								@csrf
								<div class="form-group">
									<label for="recipient">@lang('Recipient email or username')</label>
									<input type="text" name="recipient" id="recipient"
										   placeholder="@lang('Please enter valid email or username')" readonly
										   value="{{ $requestMoney->sender->email }}"
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
													class="form-control @error('currency') is-invalid @enderror"
													disabled>
												<option value="{{ $requestMoney->currency->id }}"
														data-currency="{{ __(optional($requestMoney->currency)->code) }}"
														data-currencytype="{{ optional($requestMoney->currency)->currency_type }}"
														{{ ($requestMoney->currency_id == optional($requestMoney->currency)->id) ? 'selected' : '' }}>
													{{ __(optional($requestMoney->currency)->code) }} - {{ __(optional($requestMoney->currency)->name) }}
												</option>
											</select>
											<div class="invalid-feedback">
												@error('currency') @lang($message) @enderror
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label for="amount">@lang('Amount')</label>
											<input type="text" id="amount" value="{{ getAmount($requestMoney->amount) }}" name="amount"
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
									<label class="custom-switch" for="charge_from">
										<input type="checkbox" name="charge_from" id="charge_from" class="custom-switch-input" value="1">
										<span class="custom-switch-indicator"></span>
										<span class="custom-switch-description">@lang('Receiver will pay the txn charge')</span>
										<i class="fas fa-info-circle details-icon" data-toggle="tooltip" data-placement="top"
									   title="@lang('If enable transaction charge will deduct from receiver')"></i>
									</label>
								</div>
								<div class="form-group">
									<label for="note">@lang('Note')</label>
									<textarea name="note" rows="5" class="form-control form-control-sm"
											  readonly>{{ $requestMoney->note }}</textarea>
								</div>
								<button type="submit" id="submit" class="btn btn-primary btn-sm btn-block"
										disabled>@lang('Confirm Request')</button>
							</form>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="card mb-4 shadow">
						<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
							<h6 class="m-0 font-weight-bold text-primary">@lang('Transaction Details')</h6>
						</div>
						<div class="card-body showCharge">
						</div>
					</div>
				</div>
			</div>

		</section>
	</div>
@endsection

@push('extra_scripts')
    <script src="{{ asset('assets/dashboard/js/pusher.min.js') }}"></script>
@endpush

@section('scripts')
    <script>
        'use strict';
        $(document).ready(function(){
        $('[data-toggle="tooltip"]').tooltip();

        let recipientField = $('#recipient');
        let amountField = $('#amount');
        let recipientStatus = false;
        let amountStatus = false;

        let amount = amountField.val();
        let currency_id = $('#currency').val();
        let currency_code = $('#currency option:selected').data('currency');
        let transaction_type_id = "{{ config('transactionType.request') }}"; //Request
        let charge_from = 0;

        checkAmount(amount, currency_id, transaction_type_id, charge_from, currency_code);
        // for amount limit check
        $(document).on('change, input', "#amount, #charge_from", function (e) {
            let amount = amountField.val();
            let currency_id = $('#currency').val();
            let currency_code = $('#currency option:selected').data('currency');
            let currency_type = $('#currency option:selected').data('currencytype');
            let transaction_type_id = "{{ config('transactionType.request') }}"; //Request
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

                checkAmount(amount, currency_id, transaction_type_id, charge_from, currency_code)
            } else {
                clearMessage(amountField);
                $('.showCharge').html('');
            }
        });

        function checkAmount(amount, currency_id, transaction_type_id, charge_from, currency_code) {
            $.ajax({
                method: "GET",
                url: "{{ route('transfer.checkAmount') }}",
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
                        showCharge(response, currency_code)
                    } else {
                        amountStatus = false;
                        submitButton()
                        showCharge(response, currency_code)
                        clearMessage(amountField)
                        $(amountField).addClass('is-invalid')
                        $(amountField).closest('div').find(".invalid-feedback").html(response.message);
                    }
                });
        }

        //for receiver check
        checkRecipient();

        function checkRecipient() {
            $.ajax({
                method: "GET",
                url: "{{ route('transfer.checkRecipient') }}",
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

        function clearMessage(fieldId) {
            $(fieldId).removeClass('is-valid')
            $(fieldId).removeClass('is-invalid')
            $(fieldId).closest('div').find(".invalid-feedback").html('');
            $(fieldId).closest('div').find(".is-valid").html('');
        }

        function submitButton() {
            if (recipientStatus && amountStatus) {
                $("#submit").removeAttr("disabled");
            } else {
                $("#submit").attr("disabled", true);
            }
        }

        function showCharge(response, currency_code) {
            let txnDetails = `<ul class="list-group">
					<li class="list-group-item d-flex justify-content-between align-items-center">
						<span>{{ __('Available Balance') }}</span>
						<span class="text-success"> ${response.balance} ${currency_code} </span>
					</li>
					<li class="list-group-item d-flex justify-content-between align-items-center">
						<span>{{ __('Transfer Charge') }}</span>
						<span class="text-danger"> ${response.percentage_charge} + ${response.fixed_charge} = ${response.charge} ${currency_code} </span>
					</li>
					<li class="list-group-item d-flex justify-content-between align-items-center">
						<span>{{ __('Payable amount') }}</span>
						<span class="text-info"> ${response.transfer_amount} ${currency_code} </span>
					</li>
					<li class="list-group-item d-flex justify-content-between align-items-center">
						<span>{{ __('Receiver will received') }}</span>
						<span class="text-info"> ${response.received_amount} ${currency_code} </span>
					</li>
					<li class="list-group-item d-flex justify-content-between align-items-center">
						<span>{{ __('Remaining Balance') }}</span>
						<span class="text-primary"> ${response.remaining_balance} ${currency_code} </span>
					</li>
					<li class="list-group-item d-flex justify-content-between">
					<span>{{ __('Min Request Limit') }}</span>
					<span>${parseFloat(response.min_limit).toFixed(response.currency_limit)} ${currency_code}</span>
				</li>
				<li class="list-group-item d-flex justify-content-between">
					<span>{{ __('Max Request Limit') }}</span>
					<span>${parseFloat(response.max_limit).toFixed(response.currency_limit)} ${currency_code}</span>
				</li>
				</ul>`;

            $('.showCharge').html(txnDetails)
        }
    });
    </script>
@endsection
