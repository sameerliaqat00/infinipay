@extends('user.layouts.master')
@section('page_title',__('Voucher Create'))

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>@lang('Voucher Create')</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active">
                        <a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
                    </div>
                    <div class="breadcrumb-item">@lang('Voucher Create')</div>
                </div>
            </div>
            <!------ alert ------>
            <div class="row ">
                <div class="col-md-12">
                    <div class="bd-callout bd-callout-primary mx-2">
                        <i class="fa-3x fas fa-info-circle text-primary"></i> @lang(optional($template->description)->short_description)
                    </div>
                </div>
            </div>


            <div class="row mb-3">
                <div class="container-fluid" id="container-wrapper">
                    <div class="row justify-content-md-center">
                        <div class="col-md-6">
                            <div class="card mb-4 card-primary shadow">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">@lang('Voucher Create')</h6>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('voucher.createRequest') }}" method="post">
                                        @csrf
                                        <div class="form-group">
                                            <label for="recipient">@lang('Recipient email') {{ basicControl()->allowUser ? __('or username') : '' }}</label>
                                            <input type="text"
                                                   placeholder="@lang('Please enter valid email') {{ basicControl()->allowUser ? __('or username') : ''}}"
                                                   autocomplete="off" value="{{ old('recipient') }}" name="recipient"
                                                   id="recipient"
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
                                                            class="form-control form-control-sm">
                                                        @foreach($currencies as $key => $currency)
                                                            <option value="{{ $currency->id }}"
                                                                    data-currency="{{ __($currency->code) }}"
                                                                    data-currencytype="{{ $currency->currency_type }}">
                                                                {{ __($currency->code) }} @lang('-') {{ __($currency->name) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="amount">@lang('Amount')</label>
                                                    <input type="text" id="amount" value="{{ old('amount') }}"
                                                           name="amount"
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
                                            <textarea name="note" rows="5"
                                                      class="form-control form-control-sm"></textarea>
                                        </div>
                                        <button type="submit" id="submit" class="btn btn-primary btn-sm btn-block"
                                                disabled>@lang('Create voucher')</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="card mb-4 card-primary shadow">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">@lang('Details')</h6>
                                </div>
                                <div class="card-body showCharge">
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
    <script src="{{ asset('assets/dashboard/js/pusher.min.js') }}"></script>
@endpush

@section('scripts')
    <script>
        'use strict';
        $(document).ready(function () {
            let recipientField = $('#recipient');
            let amountField = $('#amount');
            let recipientStatus = false;
            let amountStatus = false;

            function clearMessage(fieldId) {
                $(fieldId).removeClass('is-valid');
                $(fieldId).removeClass('is-invalid');
                $(fieldId).closest('div').find(".invalid-feedback").html('');
                $(fieldId).closest('div').find(".is-valid").html('');
            }

            //for receiver check
            $(document).on('input', "#recipient", function (e) {
                let recipient = $(recipientField).val();
                if (recipient === '') {
                    clearMessage(recipientField)
                }

                if (!'{{ basicControl()->allowUser }}') {
                    if (isEmail(recipient)) {
                        if (recipient.length >= 3) {
                            checkRecipient();
                        }
                    } else {
                        clearMessage(recipientField)
                    }
                } else if (recipient.length >= 3) {
                    checkRecipient();
                } else {
                    clearMessage(recipientField)
                }


            });

            function isEmail(email) {
                let regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                return regex.test(email);
            }

            function checkRecipient() {
                $.ajax({
                    method: "GET",
                    url: "{{ route('voucher.checkRecipient') }}",
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
                let transaction_type_id = "{{ config('transactionType.voucher') }}"; // 6=voucher
                let charge_from = 1;
                let currency_code = $('#currency option:selected').data('currency');
                let currency_type = $('#currency option:selected').data('currencytype');
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
                    $(amountField).val(0)
                    $('.showCharge').html('');
                }
            });

            function checkAmount(amount, currency_id, transaction_type_id, charge_from, currency_code) {
                $.ajax({
                    method: "GET",
                    url: "{{ route('voucher.checkInitiateAmount') }}",
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
                            $('.showCharge').html('');
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

            function showCharge(response, currency_code) {
                let txnDetails = `
			<ul class="list-group">
				<li class="list-group-item d-flex justify-content-between">
					<span>{{ __('Available Balance') }}</span>
					<span class="text-success"> ${response.balance} ${currency_code}</span>
				</li>
				<li class="list-group-item d-flex justify-content-between">
					<span>{{ __('Transfer Charge') }}</span>
					<span class="text-danger"> ${response.percentage_charge} + ${response.fixed_charge} = ${response.charge} ${currency_code}</span>
				</li>
				<li class="list-group-item d-flex justify-content-between">
					<span>{{ __('Request Amount') }}</span>
					<span class="text-info"> ${response.transfer_amount} ${currency_code}</span>
				</li>
				<li class="list-group-item d-flex justify-content-between">
					<span>{{ __('Receivable Amount') }}</span>
					<span class="text-info"> ${response.received_amount} ${currency_code}</span>
				</li>
				<li class="list-group-item d-flex justify-content-between">
					<span>{{ __('Min Request Limit') }}</span>
					<span>${parseFloat(response.min_limit).toFixed(response.currency_limit)} ${currency_code}</span>
				</li>
				<li class="list-group-item d-flex justify-content-between">
					<span>{{ __('Max Request Limit') }}</span>
					<span>${parseFloat(response.max_limit).toFixed(response.currency_limit)} ${currency_code}</span>
				</li>
			</ul>
			`
                $('.showCharge').html(txnDetails);
            }
        });
    </script>
@endsection
