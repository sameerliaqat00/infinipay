@extends('user.layouts.master')
@section('page_title',__('Pay Bill'))
@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Pay Bill')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Pay Bill')</div>
				</div>
			</div>
			<!------ alert ------>
			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row justify-content-md-center">
						<div class="col-md-6">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Pay Bill')</h6>
								</div>
								<div class="card-body payout">
									<form action="{{ route('pay.bill.submit') }}" method="post">
										@csrf
										<div class="row">
											<div class="col-md-12">
												<div class="bill-categories">
													<div class="row g-4">
														<div class="col-xl-2 col-lg-3 col-md-4 col-6">
														   <span class="tag-item">
															  <input type="radio" value="airtime" class="btn-check"
																	 name="category"
																	 id="bill1" autocomplete="off" checked/>
															  <label class="btn btn-primary" for="bill1">
																 <img
																	 src="{{asset('assets/frontend/images/airplane.png')}}"
																	 alt="@lang('AirTime')"/>
																 <p>@lang('AirTime')</p>
															  </label>
														   </span>
														</div>
														<div class="col-xl-2 col-lg-3 col-md-4 col-6">
														   <span class="tag-item">
															  <input type="radio" value="power" class="btn-check"
																	 name="category"
																	 id="bill2" autocomplete="off"/>
															  <label class="btn btn-primary" for="bill2">
																 <img
																	 src="{{asset('assets/frontend/images/electricity.png')}}"
																	 alt="@lang('Electricity')"/>
																 <p>@lang('Electricity')</p>
															  </label>
														   </span>
														</div>
														<div class="col-xl-2 col-lg-3 col-md-4 col-6">
														   <span class="tag-item">
															  <input type="radio" value="internet" class="btn-check"
																	 name="category"
																	 id="bill3" autocomplete="off"/>
															  <label class="btn btn-primary" for="bill3">
																 <img
																	 src="{{asset('assets/frontend/images/internet.png')}}"
																	 alt="@lang('Internet')"/>
																 <p>@lang('Internet')</p>
															  </label>
														   </span>
														</div>
														<div class="col-xl-2 col-lg-3 col-md-4 col-6">
														   <span class="tag-item">
															  <input type="radio" value="toll" class="btn-check"
																	 name="category"
																	 id="bill4" autocomplete="off"/>
															  <label class="btn btn-primary" for="bill4">
																 <img
																	 src="{{asset('assets/frontend/images/toll.png')}}"
																	 alt="@lang('Toll')"/>
																 <p>@lang('Toll')</p>
															  </label>
														   </span>
														</div>
														<div class="col-xl-2 col-lg-3 col-md-4 col-6">
														   <span class="tag-item">
															  <input type="radio" value="cables" class="btn-check"
																	 name="category"
																	 id="bill5" autocomplete="off"/>
															  <label class="btn btn-primary" for="bill5">
																 <img
																	 src="{{asset('assets/frontend/images/smart-tv.png')}}"
																	 alt="@lang('Cable Tv')"/>
																 <p>@lang('Cable Tv')</p>
															  </label>
														   </span>
														</div>
														<div class="col-xl-2 col-lg-3 col-md-4 col-6">
														   <span class="tag-item">
															  <input type="radio" value="data_bundle" class="btn-check"
																	 name="category"
																	 id="bill6" autocomplete="off"/>
															  <label class="btn btn-primary" for="bill6">
																 <img
																	 src="{{asset('assets/frontend/images/data-bundle.png')}}"
																	 alt="@lang('Data Bundle')"/>
																 <p>@lang('Data Bundle')</p>
															  </label>
														   </span>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12">
												<div class="form-group search-currency-dropdown">
													<label for="from_wallet">@lang('From Wallet')</label>
													<select id="from_wallet" name="from_wallet"
															class="form-control form-control-sm">
														<option value="" disabled
																selected>@lang('Select wallet')</option>
														@foreach($currencies as $key => $currency)
															<option value="{{ $currency->id }}">
																{{ __($currency->code) }}
																- {{ __($currency->name) }} </option>
														@endforeach
													</select>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12">
												<lable>@lang('Country')</lable>
												<select class="form-select form-control mt-1 country" name="country"
														data-resource="{{json_encode($countryLists)}}" required>
													<option value="" selected=""
															disabled>@lang('Select Country')</option>
													@if($countryNames)
														@foreach($countryNames as $key => $name)
															<option value="{{$key}}">{{$name}}</option>
														@endforeach
													@endif
												</select>
												@error('country')
												<span class="text-danger">{{$message}}</span>
												@enderror
											</div>
										</div>
										<div class="row dynamic-services mx-1 mt-4">
											<label>@lang('Services')</label>
											<select id="dynamic-services" name="service"
													class="form-control form-control-sm" required>
											</select>
											@error('service')
											<span class="text-danger">{{$message}}</span>
											@enderror
										</div>

										<div class="row mt-4">
											<div class="col-md-12">
												<div class="form-group">
													<div id="showLabel">
														<label for="amount">@lang('Customer')</label>
													</div>
													<input type="text" name="customer" value="{{ old('customer') }}"
														   class="form-control @error('customer') is-invalid @enderror"
														   required>
													<div
														class="invalid-feedback">@error('customer') @lang($message) @enderror</div>
												</div>
											</div>
										</div>

										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label for="amount">@lang('Amount')</label>
													<div class="input-group input-group-sm">
														<input type="text" name="amount" value="{{ old('amount') }}"
															   placeholder="@lang('Enter Amount')"
															   class="form-control amount @error('amount') is-invalid @enderror"
															   required>
														<div class="input-group-prepend">
															<span
																class="input-group-text showCurrency"></span>
														</div>
													</div>
													<div
														class="invalid-feedback">@error('amount') @lang($message) @enderror</div>
												</div>
											</div>
										</div>
										<button type="submit" id="submit"
												class="btn btn-primary btn-sm btn-block">@lang('Continue')</button>
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
								<div class="card-body showCharge d-none">
									<ul class="list-group">
										<li class="list-group-item d-flex justify-content-between align-items-center">
											<span>@lang('Fixed charge')</span>
											<span class="text-danger" id="fixed_charge"></span>
										</li>
										<li class="list-group-item d-flex justify-content-between align-items-center">
											<span>@lang('Percentage charge')</span><span class="text-danger"
																						 id="percentage_charge"></span>
										</li>
										<li class="list-group-item d-flex justify-content-between align-items-center">
											<span>@lang('Min limit')</span>
											<span class="text-info" id="min_limit"></span>
										</li>
										<li class="list-group-item d-flex justify-content-between align-items-center">
											<span>@lang('Max limit')</span>
											<span class="text-info" id="max_limit"></span>
										</li>
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

@section('scripts')
	<script>
		'use strict';
		$(document).on('change', '.country', function () {
			var country = $(this).data('resource');
			var code = $(this).find(':selected').val()
			var category = $("input[name='category']:checked").val();
			currencCode(country, code);
			fetchService(category, code);

		})

		$(document).on('click', "input[name='category']", function () {
			var country = $('.country').data('resource');
			var code = $('.country').find(':selected').val()
			var category = $("input[name='category']:checked").val();
			currencCode(country, code);
			fetchService(category, code);
			removeAttr();
		})

		$(document).on('change', '#dynamic-services', function () {
			$('.showCharge').addClass('d-none');
			removeAttr();

			let fixed = $(this).find(':selected').data('fixed')
			let percent = $(this).find(':selected').data('percent')
			let min = $(this).find(':selected').data('min')
			let max = $(this).find(':selected').data('max')
			let currency = $(this).find(':selected').data('currency')
			let amount = $(this).find(':selected').data('amount')
			let label = $(this).find(':selected').data('label')

			$('#showLabel').html('')
			$('#showLabel').append(`<label for="${label}">${label}</label>`)

			if (currency) {
				$('.showCharge').removeClass('d-none');
			}
			$('#fixed_charge').text(`${fixed} ${currency}`);
			$('#percentage_charge').text(`${percent}%`);
			$('#min_limit').text(`${min} ${currency}`);
			$('#max_limit').text(`${max} ${currency}`);

			if (amount > 0) {
				$('.amount').val(amount).attr('readonly', true);
			}
		})

		function removeAttr() {
			$('.amount').val('').attr('readonly', false);
		}

		function currencCode(country, code) {
			Object.keys(country).forEach(key => {
				let singleCode = country[key].code;
				if (singleCode == code) {
					$('.showCurrency').text(country[key].iso_code)
					return;
				}
			});
		}

		function fetchService(category, code) {
			$.ajax({
				headers: {'X-CSRF-TOKEN': $('meta[name="csrf_token"]').attr('content')},
				url: "{{ route('fetch.services') }}",
				data: {category: category, code: code},
				dataType: 'json',
				type: "post",
				success: function (data) {
					if (data.status == 'success') {
						let services = data.data;
						showServices(services);
					}
				}
			});
		}

		function showServices(services) {
			$('#dynamic-services').html('');
			var options = `<option disabled value="" selected>@lang("Select Service")</option>`;
			for (let i = 0; i < services.length; i++) {
				options += `<option value="${services[i].id}" data-percent="${services[i].percent_charge}"
                data-fixed="${services[i].fixed_charge}" data-min="${services[i].min_amount}" data-max="${services[i].max_amount}"
                data-currency="${services[i].currency}" data-amount="${services[i].amount}" data-label="${services[i].label_name}">${services[i].type}</option>`;
			}
			$('#dynamic-services').html(options);
		}

	</script>
@endsection
