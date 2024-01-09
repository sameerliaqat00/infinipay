@extends('admin.layouts.master')
@section('page_title', __('Service Control'))
@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Service Control')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Service Control')</div>
				</div>
			</div>

			<div class="section-body">
				<div class="row mt-sm-4">
					<div class="col-12 col-md-4 col-lg-3">
						@include('admin.control_panel.components.sidebar', ['settings' => config('generalsettings.settings'), 'suffix' => 'Settings'])
					</div>
					<div class="col-12 col-md-8 col-lg-9">
						<div class="container-fluid" id="container-wrapper">
							<div class="row justify-content-md-center">
								<div class="col-lg-12">
									<div class="card mb-4 card-primary shadow">
										<div
											class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
											<h6 class="m-0 font-weight-bold text-primary">@lang('Service Control')</h6>
										</div>
										<div class="card-body">
											<form action="{{ route('service.control') }}" method="post">
												@csrf
												<div class="row">
													@if(checkPermission(2) == true)
														<div class="col-md-3">
															<div class="form-group">
																<label>@lang('Transfer Service')</label>
																<div class="selectgroup w-100">
																	<label class="selectgroup-item">
																		<input type="radio" name="transfer" value="0"
																			   class="selectgroup-input" {{ old('transfer', $serviceControl->transfer) == 0 ? 'checked' : ''}}>
																		<span
																			class="selectgroup-button">@lang('OFF')</span>
																	</label>
																	<label class="selectgroup-item">
																		<input type="radio" name="transfer" value="1"
																			   class="selectgroup-input" {{ old('transfer', $serviceControl->transfer) == 1 ? 'checked' : ''}}>
																		<span
																			class="selectgroup-button">@lang('ON')</span>
																	</label>
																</div>
																@error('transfer')
																<span class="text-danger" role="alert">
																<strong>{{ __($message) }}</strong>
															</span>
																@enderror
															</div>
														</div>
													@endif
													@if(checkPermission(15) == true)
														<div class="col-md-3">
															<div class="form-group">
																<label>@lang('Request Service')</label>
																<div class="selectgroup w-100">
																	<label class="selectgroup-item">
																		<input type="radio" name="request" value="0"
																			   class="selectgroup-input" {{ old('request', $serviceControl->request) == 0 ? 'checked' : ''}}>
																		<span
																			class="selectgroup-button">@lang('OFF')</span>
																	</label>
																	<label class="selectgroup-item">
																		<input type="radio" name="request" value="1"
																			   class="selectgroup-input" {{ old('request', $serviceControl->request) == 1 ? 'checked' : ''}}>
																		<span
																			class="selectgroup-button">@lang('ON')</span>
																	</label>
																</div>
																@error('request')
																<span class="text-danger" role="alert">
																<strong>{{ __($message) }}</strong>
															</span>
																@enderror
															</div>
														</div>
													@endif
													@if(checkPermission(3) == true)
														<div class="col-md-3">
															<div class="form-group">
																<label>@lang('Exchange Service')</label>
																<div class="selectgroup w-100">
																	<label class="selectgroup-item">
																		<input type="radio" name="exchange" value="0"
																			   class="selectgroup-input" {{ old('exchange', $serviceControl->exchange) == 0 ? 'checked' : ''}}>
																		<span
																			class="selectgroup-button">@lang('OFF')</span>
																	</label>
																	<label class="selectgroup-item">
																		<input type="radio" name="exchange" value="1"
																			   class="selectgroup-input" {{ old('exchange', $serviceControl->exchange) == 1 ? 'checked' : ''}}>
																		<span
																			class="selectgroup-button">@lang('ON')</span>
																	</label>
																</div>
																@error('exchange')
																<span class="text-danger" role="alert">
																<strong>{{ __($message) }}</strong>
															</span>
																@enderror
															</div>
														</div>
													@endif
													@if(checkPermission(4) == true)
														<div class="col-md-3">
															<div class="form-group">
																<label>@lang('Redeem Service')</label>
																<div class="selectgroup w-100">
																	<label class="selectgroup-item">
																		<input type="radio" name="redeem" value="0"
																			   class="selectgroup-input" {{ old('redeem', $serviceControl->redeem) == 0 ? 'checked' : ''}}>
																		<span
																			class="selectgroup-button">@lang('OFF')</span>
																	</label>
																	<label class="selectgroup-item">
																		<input type="radio" name="redeem" value="1"
																			   class="selectgroup-input" {{ old('redeem', $serviceControl->redeem) == 1 ? 'checked' : ''}}>
																		<span
																			class="selectgroup-button">@lang('ON')</span>
																	</label>
																</div>
																@error('redeem')
																<span class="text-danger" role="alert">
																<strong>{{ __($message) }}</strong>
															</span>
																@enderror
															</div>
														</div>
													@endif
													@if(checkPermission(5) == true)
														<div class="col-md-3">
															<div class="form-group">
																<label>@lang('Escrow Service')</label>
																<div class="selectgroup w-100">
																	<label class="selectgroup-item">
																		<input type="radio" name="escrow" value="0"
																			   class="selectgroup-input" {{ old('escrow', $serviceControl->escrow) == 0 ? 'checked' : ''}}>
																		<span
																			class="selectgroup-button">@lang('OFF')</span>
																	</label>
																	<label class="selectgroup-item">
																		<input type="radio" name="escrow" value="1"
																			   class="selectgroup-input" {{ old('escrow', $serviceControl->escrow) == 1 ? 'checked' : ''}}>
																		<span
																			class="selectgroup-button">@lang('ON')</span>
																	</label>
																</div>
																@error('escrow')
																<span class="text-danger" role="alert">
																<strong>{{ __($message) }}</strong>
															</span>
																@enderror
															</div>
														</div>
													@endif
													@if(checkPermission(6) == true)
														<div class="col-md-3">
															<div class="form-group">
																<label>@lang('Voucher Service')</label>
																<div class="selectgroup w-100">
																	<label class="selectgroup-item">
																		<input type="radio" name="voucher" value="0"
																			   class="selectgroup-input" {{ old('voucher', $serviceControl->voucher) == 0 ? 'checked' : ''}}>
																		<span
																			class="selectgroup-button">@lang('OFF')</span>
																	</label>
																	<label class="selectgroup-item">
																		<input type="radio" name="voucher" value="1"
																			   class="selectgroup-input" {{ old('voucher', $serviceControl->voucher) == 1 ? 'checked' : ''}}>
																		<span
																			class="selectgroup-button">@lang('ON')</span>
																	</label>
																</div>
																@error('voucher')
																<span class="text-danger" role="alert">
																<strong>{{ __($message) }}</strong>
															</span>
																@enderror
															</div>
														</div>
													@endif
													@if(checkPermission(1) == true)
														<div class="col-md-3">
															<div class="form-group">
																<label>@lang('Deposit Service')</label>
																<div class="selectgroup w-100">
																	<label class="selectgroup-item">
																		<input type="radio" name="deposit" value="0"
																			   class="selectgroup-input" {{ old('deposit', $serviceControl->deposit) == 0 ? 'checked' : ''}}>
																		<span
																			class="selectgroup-button">@lang('OFF')</span>
																	</label>
																	<label class="selectgroup-item">
																		<input type="radio" name="deposit" value="1"
																			   class="selectgroup-input" {{ old('deposit', $serviceControl->deposit) == 1 ? 'checked' : ''}}>
																		<span
																			class="selectgroup-button">@lang('ON')</span>
																	</label>
																</div>
																@error('deposit')
																<span class="text-danger" role="alert">
																<strong>{{ __($message) }}</strong>
															</span>
																@enderror
															</div>
														</div>
													@endif
													@if(checkPermission(8) == true)
														<div class="col-md-3">
															<div class="form-group">
																<label>@lang('Payout Service')</label>
																<div class="selectgroup w-100">
																	<label class="selectgroup-item">
																		<input type="radio" name="payout" value="0"
																			   class="selectgroup-input" {{ old('payout', $serviceControl->payout) == 0 ? 'checked' : ''}}>
																		<span
																			class="selectgroup-button">@lang('OFF')</span>
																	</label>
																	<label class="selectgroup-item">
																		<input type="radio" name="payout" value="1"
																			   class="selectgroup-input" {{ old('payout', $serviceControl->payout) == 1 ? 'checked' : ''}}>
																		<span
																			class="selectgroup-button">@lang('ON')</span>
																	</label>
																</div>
																@error('payout')
																<span class="text-danger" role="alert">
																<strong>{{ __($message) }}</strong>
															</span>
																@enderror
															</div>
														</div>
													@endif
													@if(checkPermission(9) == true)
														<div class="col-md-3">
															<div class="form-group">
																<label>@lang('Invoice Service')</label>
																<div class="selectgroup w-100">
																	<label class="selectgroup-item">
																		<input type="radio" name="invoice" value="0"
																			   class="selectgroup-input" {{ old('invoice', $serviceControl->invoice) == 0 ? 'checked' : ''}}>
																		<span
																			class="selectgroup-button">@lang('OFF')</span>
																	</label>
																	<label class="selectgroup-item">
																		<input type="radio" name="invoice" value="1"
																			   class="selectgroup-input" {{ old('invoice', $serviceControl->invoice) == 1 ? 'checked' : ''}}>
																		<span
																			class="selectgroup-button">@lang('ON')</span>
																	</label>
																</div>
																@error('invoice')
																<span class="text-danger" role="alert">
																<strong>{{ __($message) }}</strong>
															</span>
																@enderror
															</div>
														</div>
													@endif
													@if(checkPermission(7) == true)
														<div class="col-md-3">
															<div class="form-group">
																<label>@lang('Store Service')</label>
																<div class="selectgroup w-100">
																	<label class="selectgroup-item">
																		<input type="radio" name="store" value="0"
																			   class="selectgroup-input" {{ old('store', $serviceControl->store) == 0 ? 'checked' : ''}}>
																		<span
																			class="selectgroup-button">@lang('OFF')</span>
																	</label>
																	<label class="selectgroup-item">
																		<input type="radio" name="store" value="1"
																			   class="selectgroup-input" {{ old('store', $serviceControl->store) == 1 ? 'checked' : ''}}>
																		<span
																			class="selectgroup-button">@lang('ON')</span>
																	</label>
																</div>
																@error('store')
																<span class="text-danger" role="alert">
																<strong>{{ __($message) }}</strong>
															</span>
																@enderror
															</div>
														</div>
													@endif
													@if(checkPermission(17) == true)
														<div class="col-md-3">
															<div class="form-group">
																<label>@lang('QR Payment Service')</label>
																<div class="selectgroup w-100">
																	<label class="selectgroup-item">
																		<input type="radio" name="qr_payment" value="0"
																			   class="selectgroup-input" {{ old('qr_payment', $serviceControl->qr_payment) == 0 ? 'checked' : ''}}>
																		<span
																			class="selectgroup-button">@lang('OFF')</span>
																	</label>
																	<label class="selectgroup-item">
																		<input type="radio" name="qr_payment" value="1"
																			   class="selectgroup-input" {{ old('qr_payment', $serviceControl->qr_payment) == 1 ? 'checked' : ''}}>
																		<span
																			class="selectgroup-button">@lang('ON')</span>
																	</label>
																</div>
																@error('qr_payment')
																<span class="text-danger" role="alert">
																<strong>{{ __($message) }}</strong>
															</span>
																@enderror
															</div>
														</div>
													@endif
													@if(checkPermission(10) == true)
														<div class="col-md-3">
															<div class="form-group">
																<label>@lang('Virtual Card Service')</label>
																<div class="selectgroup w-100">
																	<label class="selectgroup-item">
																		<input type="radio" name="virtual_card"
																			   value="0"
																			   class="selectgroup-input" {{ old('virtual_card', $serviceControl->virtual_card) == 0 ? 'checked' : ''}}>
																		<span
																			class="selectgroup-button">@lang('OFF')</span>
																	</label>
																	<label class="selectgroup-item">
																		<input type="radio" name="virtual_card"
																			   value="1"
																			   class="selectgroup-input" {{ old('virtual_card', $serviceControl->virtual_card) == 1 ? 'checked' : ''}}>
																		<span
																			class="selectgroup-button">@lang('ON')</span>
																	</label>
																</div>
																@error('virtual_card')
																<span class="text-danger" role="alert">
																<strong>{{ __($message) }}</strong>
															</span>
																@enderror
															</div>
														</div>
													@endif
													@if(checkPermission(11) == true)
														<div class="col-md-3">
															<div class="form-group">
																<label>@lang('Bill Payment Service')</label>
																<div class="selectgroup w-100">
																	<label class="selectgroup-item">
																		<input type="radio" name="bill_payment"
																			   value="0"
																			   class="selectgroup-input" {{ old('bill_payment', $serviceControl->bill_payment) == 0 ? 'checked' : ''}}>
																		<span
																			class="selectgroup-button">@lang('OFF')</span>
																	</label>
																	<label class="selectgroup-item">
																		<input type="radio" name="bill_payment"
																			   value="1"
																			   class="selectgroup-input" {{ old('bill_payment', $serviceControl->bill_payment) == 1 ? 'checked' : ''}}>
																		<span
																			class="selectgroup-button">@lang('ON')</span>
																	</label>
																</div>
																@error('bill_payment')
																<span class="text-danger" role="alert">
																<strong>{{ __($message) }}</strong>
															</span>
																@enderror
															</div>
														</div>
													@endif
												</div>
												<div class="form-group">
													<button type="submit" name="submit"
															class="btn btn-primary btn-sm btn-block">@lang('Save changes')</button>
												</div>
											</form>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

		</section>
	</div>
@endsection
