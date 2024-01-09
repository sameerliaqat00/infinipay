@extends('user.layouts.master')
@section('page_title',__('Exchange Money'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Exchange Money')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Exchange Money')</div>
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
									<h6 class="m-0 font-weight-bold text-primary">@lang('Exchange Money')</h6>
								</div>
								<div class="card-body">
									<form action="{{ route('exchange.initialize') }}" method="post">
										@csrf
										<div class="form-group search-currency-dropdown">
											<label for="from_wallet">@lang('From Wallet')</label>
											<select id="from_wallet" name="from_wallet"
													class="form-control form-control-sm">
												<option value="" disabled selected>@lang('Select wallet')</option>
												@foreach($currencies as $key => $currency)
													<option data-from_code="{{ __($currency->code) }}"
															data-currencytype="{{ $currency->currency_type }}"
															value="{{ $currency->id }}">
														{{ __($currency->code) }} - {{ __($currency->name) }} </option>
												@endforeach
											</select>
										</div>
										<div class="form-group search-currency-dropdown">
											<label for="to_wallet">@lang('To Wallet')</label>
											<select id="to_wallet" name="to_wallet"
													class="form-control form-control-sm">
											</select>
										</div>

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

										<input type="submit" id="submit" class="btn btn-primary btn-sm btn-block"
											   value="@lang('Exchange Money')" disabled>
									</form>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Transaction Details')</h6>
								</div>
								<div class="card-body showCharge"></div>
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
			let from_walletField = $('#from_wallet');
			let to_walletField = $('#to_wallet');
			let amountField = $('#amount');
			$(document).on('change', '#from_wallet', function (e) {
				let from_wallet = from_walletField.val();
				if (from_wallet.length >= 1) {
					currenciesExceptSelected(from_wallet)
				} else {
					$('#to_wallet').html('<option value=""> Select from wallet </option>');
				}
			})

			$(document).on('change, input', '#to_wallet, #amount', function (e) {
				checkAmount();
			})

			function checkAmount() {
				let from_wallet = from_walletField.val();
				let from_code = $('#from_wallet option:selected').data('from_code');
				let to_code = $('#to_wallet option:selected').data('to_code');
				let currency_type = $('#from_wallet option:selected').data('currencytype');

				let to_wallet = to_walletField.val();
				let amount = amountField.val();
				if (amount > 0 && from_wallet && to_wallet && from_wallet.length >= 1 && to_wallet.length >= 1) {

					let fraction = amount.split('.')[1];
					let limit = currency_type == 0 ? 8 : 2;

					if (fraction && fraction.length > limit) {
						amount = (Math.floor(amount * Math.pow(10, limit)) / Math.pow(10, limit)).toFixed(limit);
						amountField.val(amount);
					}

					$.ajax({
						method: "GET",
						url: "{{ route('exchange.checkAmount') }}",
						dataType: "json",
						data: {
							'from_wallet': from_wallet,
							'to_wallet': to_wallet,
							'amount': amount,
						}
					})
						.done(function (response) {
							showCharge(response, from_code, to_code)
							if (response.status) {
								clearMessage(amountField)
								$(amountField).addClass('is-valid')
								$(amountField).closest('div').find(".valid-feedback").html(response.message)
								$("#submit").removeAttr("disabled");
							} else {
								clearMessage(amountField)
								$(amountField).addClass('is-invalid')
								$(amountField).closest('div').find(".invalid-feedback").html(response.message);
								$("#submit").attr("disabled", true);
							}
						});
				} else {
					clearMessage(amountField)
					$('.showCharge').html('')
				}
			}

			function showCharge(response, from_code, to_code) {
				let txnDetails = `
			<ul class="list-group">
				<li class="list-group-item d-flex justify-content-between align-items-center">
					<span>{{ __('Available Balance') }}</span>
					<span class="text-success">${response.balance} ${from_code}</span>
				</li>
				<li class="list-group-item d-flex justify-content-between align-items-center">
					<span>{{ __('Transfer Charge') }}</span>
					<span class="text-danger">${response.charge_percentage} + ${response.charge_fixed} = ${response.charge} ${from_code}</span>
				</li>
				<li class="list-group-item d-flex justify-content-between align-items-center">
					<span>{{ __('Payable amount') }}</span>
					<span class="text-info">${response.transfer_amount} ${from_code}</span>
				</li>
				<li class="list-group-item d-flex justify-content-between align-items-center">
					<span>{{ __('You will get') }}</span>
					<span class="text-info">${response.received_amount} ${to_code}</span>
				</li>
				<li class="list-group-item d-flex justify-content-between align-items-center">
					<span>{{ __('Exchange rate') }}</span>
					<span class="text-info">1 ${from_code} = ${response.exchange_rate} ${to_code}</span>
				</li>
				<li class="list-group-item d-flex justify-content-between align-items-center">
					<span>{{ __('Remaining Balance') }}</span>
					<span class="text-primary">${response.fromWalletUpdateBalance} ${from_code}</span>
				</li>
				<li class="list-group-item d-flex justify-content-between align-items-center">
					<span>{{ __('Updated Balance') }}</span>
					<span class="text-primary">${response.toWalletUpdateBalance} ${to_code}</span>
				</li>
				<li class="list-group-item d-flex justify-content-between align-items-center">
					<span>{{ __('Min Request Limit') }}</span>
					<span>${parseFloat(response.min_limit).toFixed(response.currency_limit)} ${from_code}</span>
				</li>
				<li class="list-group-item d-flex justify-content-between align-items-center">
					<span>{{ __('Max Request Limit') }}</span>
					<span>${parseFloat(response.max_limit).toFixed(response.currency_limit)} ${from_code}</span>
				</li>
			</ul>`
				$('.showCharge').html(txnDetails);
			}

			function currenciesExceptSelected(from_wallet) {
				$.ajax({
					method: "GET",
					url: "{{ route('currencies.except.selected') }}",
					dataType: "json",
					data: {
						'from_wallet': from_wallet
					}
				})
					.done(function (response) {
						if (response.status) {
							let options = '';
							if (response.to_wallet.length === 0) {
								options = '<option value="" disabled selected> @lang('No Currency Available') </option>'
							} else {
								options += '<option value="" disabled selected>@lang('Select from wallet')</option>'
								$.each(response.to_wallet, function (key, value) {
									options += '<option data-to_code="' + value.code + '" value="' + value.id + '">' + value.code + ' - ' + value.name + ' </option>'
								});
							}
							$('#to_wallet').html(options);
							checkAmount();
						}
					});
			}

			function clearMessage(fieldId) {
				$(fieldId).removeClass('is-valid')
				$(fieldId).removeClass('is-invalid')
				$(fieldId).closest('div').find(".invalid-feedback").html('');
				$(fieldId).closest('div').find(".is-valid").html('');
			}
		});
	</script>
@endsection
