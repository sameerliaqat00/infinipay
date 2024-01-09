@extends('user.layouts.master')
@section('page_title',__('Add Fund'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Add Fund')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Add Fund')</div>
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

			<!------ main content ------>
			<div class="row justify-content-md-center">
				<div class="col-md-6">
					<div class="card mb-4 shadow card-primary">
						<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
							<h6 class="m-0 font-weight-bold text-primary">@lang('Deposit')</h6>
						</div>
						<div class="card-body">
							<form action="{{ route('fund.initialize') }}" method="post">
								@csrf
								<div class="row">
									<input type="hidden" name="orderCardId" value="{{$id}}">
									<div class="col-md-6 search-currency-dropdown">
										<div class="form-group">
											<label for="currency">@lang('Currency')</label>
											<select id="currency" name="currency"
													class="form-control @error('currency') is-invalid @enderror">
												@foreach($currencies as $key => $currency)
													<option data-currency="{{ __($currency->code) }}"
															data-currencytype="{{ $currency->currency_type }}"
															value="{{ $currency->id }}"
													@if($from == 'card')
														{{ ($currency->code == $order->currency) ? 'selected' : 'disabled' }}
														@else
														{{ ($currency->is_default == 1) ? 'selected' : '' }}
														@endif>
														{{ __($currency->code) }} @lang('-') {{ __($currency->name) }}
													</option>
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
											@if($from == 'card')
												<span class="info" title="How much amount you need to add"
													  data-toggle="modal"
													  data-target="#infoModal"><img class="info-icon"
																					src="{{asset('assets/info.png')}}"
																					alt="..."></span>
											@endif
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

								<div class="row">
									<div class="col-md-12">
										<label for="methodId">@lang('Select Payment Method')</label>
										<div class="form-group">
											<div class="row payment-method-input p-1">
												@foreach($methods as $key => $method)
													<div class="col-md-2 col-sm-3 col-6">
														<div class="form-check form-check-inline mr-0 mb-3">
															<input class="form-check-input methodId" type="radio"
																   name="methodId" id="{{$key}}"
																   value="{{ $method->id }}" {{ old('methodId') == $method->id || $key == 0 ? ' checked' : ''}}>
															<label class="form-check-label" for="{{$key}}">
																<img
																	src="{{ getFile(config('location.gateway.path').$method->image ) }}">
															</label>
														</div>
													</div>
												@endforeach
											</div>
										</div>
									</div>
								</div>
								<button type="submit" id="submit" class="btn btn-primary btn-sm btn-block"
										disabled>@lang('Deposit')</button>
							</form>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="card mb-4 shadow card-primary">
						<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
							<h6 class="m-0 font-weight-bold text-primary">@lang('Details')</h6>
						</div>
						<div class="card-body showCharge">
						</div>
					</div>
				</div>
			</div>

		</section>
	</div>
	@if($from == 'card')
		<div id="infoModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
			 aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title text-dark font-weight-bold"
							id="primary-header-modalLabel">@lang('Card Add Fund Information')</h4>
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					</div>
					<div class="modal-body">
						@if(cardCurrencyCheck($id)['status'] == 'success')
							<ul>
								<li>@lang('Minimum Fund Amount') {{cardCurrencyCheck($id)['MinimumAmount']}} {{cardCurrencyCheck($id)['currencyCode']}}</li>
								<li>@lang('Maximum Fund Amount') {{cardCurrencyCheck($id)['MaximumAmount']}} {{cardCurrencyCheck($id)['currencyCode']}}</li>
								<li class="text-danger">@lang('Charges') <span>{{cardCurrencyCheck($id)['PercentCharge']}}% + {{cardCurrencyCheck($id)['FixedCharge']}} {{cardCurrencyCheck($id)['currencyCode']}}</span>
								</li>
							</ul>
						@endif
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
					</div>
				</div>
			</div>
		</div>
	@endif
@endsection

@section('scripts')
	<script>
		'use strict'
		$(document).ready(function () {
			$('[data-toggle="tooltip"]').tooltip()
			let amountField = $('#amount');
			let amountStatus = false;

			function clearMessage(fieldId) {
				$(fieldId).removeClass('is-valid')
				$(fieldId).removeClass('is-invalid')
				$(fieldId).closest('div').find(".invalid-feedback").html('');
				$(fieldId).closest('div').find(".is-valid").html('');
			}

			$(document).on('change, input', "#amount, #charge_from, #currency, .methodId", function (e) {
				let amount = amountField.val();
				let currency_id = $('#currency').val();
				let currency_code = $('#currency option:selected').data('currency');
				let currency_type = $('#currency option:selected').data('currencytype');
				let transaction_type_id = "{{ config('transactionType.deposit') }}"; //Deposit
				let methodId = $("input[type='radio'][name='methodId']:checked").val();
				if (!isNaN(amount) && amount > 0) {
					let fraction = amount.split('.')[1];
					let limit = currency_type == 0 ? 8 : 2;
					if (fraction && fraction.length > limit) {
						amount = (Math.floor(amount * Math.pow(10, limit)) / Math.pow(10, limit)).toFixed(limit);
						amountField.val(amount);
					}
					checkAmount(amount, currency_id, transaction_type_id, methodId, currency_code)
				} else {
					clearMessage(amountField)
					$('.showCharge').html('')
				}
			});

			function checkAmount(amount, currency_id, transaction_type_id, methodId, currency_code) {
				$.ajax({
					method: "GET",
					url: "{{ route('deposit.checkAmount') }}",
					dataType: "json",
					data: {
						'amount': amount,
						'currency_id': currency_id,
						'transaction_type_id': transaction_type_id,
						'methodId': methodId,
					}
				})
					.done(function (response) {
						let amountField = $('#amount');
						if (response.status) {
							clearMessage(amountField);
							$(amountField).addClass('is-valid');
							$(amountField).closest('div').find(".valid-feedback").html(response.message);
							amountStatus = true;
							submitButton();
							showCharge(response, currency_code);
						} else {
							amountStatus = false;
							submitButton();
							$('.showCharge').html('');
							clearMessage(amountField);
							$(amountField).addClass('is-invalid');
							$(amountField).closest('div').find(".invalid-feedback").html(response.message);
						}
					});
			}

			function submitButton() {
				if (amountStatus) {
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
							<span>{{ __('Payable Amount') }}</span>
							<span class="text-info"> ${response.payable_amount} ${currency_code}</span>
						</li>
						<li class="list-group-item d-flex justify-content-between">
							<span>{{ __('Received') }}</span>
							<span class="text-info"> ${response.amount} ${currency_code}</span>
						</li>
						<li class="list-group-item d-flex justify-content-between">
							<span>{{ __('New Balance') }}</span>
							<span class="text-primary"> ${response.new_balance} ${currency_code}</span>
						</li>
						<li class="list-group-item d-flex justify-content-between">
							<span>{{ __('Min Deposit Limit') }}</span>
							<span>${parseFloat(response.min_limit).toFixed(response.currency_limit)} ${currency_code}</span>
						</li>
						<li class="list-group-item d-flex justify-content-between">
							<span>{{ __('Max Deposit Limit') }}</span>
							<span>${parseFloat(response.max_limit).toFixed(response.currency_limit)} ${currency_code}</span>
						</li>
					</ul>
					`;
				$('.showCharge').html(txnDetails)
			}
		});
	</script>
@endsection
