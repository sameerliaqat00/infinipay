@extends('admin.layouts.master')
@section('page_title', __('Currencies'))
@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Currencies')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Currencies')</div>
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
											class="card-header py-3 d-flex flex-wrap flex-row align-items-center justify-content-between">
											<h6 class="m-0 font-weight-bold text-primary">@lang('Currencies')</h6>
											<span>
											<a href="{{ route('currency.create') }}"
											   class="btn btn-sm btn-outline-primary">@lang('Add New')</a>
										</span>
										</div>
										<div class="card-body">
											<div class="table-responsive">
												<table
													class="table table-striped table-hover align-items-center table-flush">
													<thead class="thead-light">
													<tr>
														<th>@lang('Name')</th>
														<th>@lang('Code')</th>
														<th>@lang('Rate')</th>
														<th>@lang('Status')</th>
														<th>@lang('Type')</th>
														<th colspan="2">@lang('Action')</th>
													</tr>
													</thead>
													<tbody>
													@foreach($currencies as $key => $currency)
														<tr>
															<td data-label="@lang('Name')">
																<div class="d-flex no-block align-items-center">
																	<div class="mr-2"><img
																			class="img-profile-custom rounded-circle"
																			src="{{asset('assets/upload/currencyLogo').'/'.$currency->logo }}"
																			alt="{{ __($currency->name) }}"
																			class="rounded-circle" width="35"
																			height="35"></div>
																	<div class="">
																		<p class="text-dark mb-0 font-weight-medium">{{ __($currency->name) }}</p>
																	</div>
																</div>
															</td>
															<td data-label="@lang('Code')">{{ __($currency->code) }}</td>
															<td data-label="@lang('Rate')"><span
																	class="font-weight-bold">{{ __($currency->symbol) }}</span>{{ (getAmount($currency->exchange_rate)) }}
															</td>
															<td data-label="@lang('Status')">
																@if($currency->is_default)
																	<span
																		class="badge badge-success">@lang('Default')</span>
																@elseif($currency->is_active)
																	<span
																		class="badge badge-info">@lang('Active')</span>
																@else
																	<span
																		class="badge badge-warning">@lang('Inactive')</span>
																@endif
															</td>
															<td data-label="@lang('Type')">
																@if($currency->currency_type == 0)
																	<span
																		class="badge badge-warning">@lang('Crypto')</span>
																@elseif($currency->currency_type == 1)
																	<span class="badge badge-dark">@lang('Fiat')</span>
																@endif
															</td>
															<td data-label="@lang('Action')">
																<a class="btn btn-sm btn-outline-primary"
																   href="{{ route('currency.edit',$currency) }}">
																	<i class="fas fa-edit"></i>
																</a>
															</td>
															<td data-label="@lang('Action')">
																<div class="dropdown">
																	<button
																		class="btn btn-sm btn-primary dropdown-toggle"
																		type="button" data-toggle="dropdown"
																		data-boundary="window" aria-haspopup="true"
																		aria-expanded="false">@lang('Edit charges')
																	</button>
																	<div class="dropdown-menu">
																		<a class="dropdown-item"
																		   href="{{ route('charge.chargeEdit',[config('transactionType.transfer'),$currency->id]) }}">@lang('Transfer')</a>
																		<a class="dropdown-item"
																		   href="{{ route('charge.chargeEdit',[config('transactionType.request'),$currency->id]) }}">@lang('Request')</a>
																		<a class="dropdown-item"
																		   href="{{ route('charge.chargeEdit',[config('transactionType.exchange'),$currency->id]) }}">@lang('Exchange')</a>
																		<a class="dropdown-item"
																		   href="{{ route('charge.chargeEdit',[config('transactionType.redeem'),$currency->id]) }}">@lang('Redeem')</a>
																		<a class="dropdown-item"
																		   href="{{ route('charge.chargeEdit',[config('transactionType.escrow'),$currency->id]) }}">@lang('Escrow')</a>
																		<a class="dropdown-item"
																		   href="{{ route('charge.chargeEdit',[config('transactionType.voucher'),$currency->id]) }}">@lang('Voucher')</a>
																		<a class="dropdown-item"
																		   href="{{ route('charge.chargeEdit',[config('transactionType.invoice'),$currency->id]) }}">@lang('Invoice')</a>
																		<a class="dropdown-item"
																		   href="{{ route('charge.payment.method',[$currency->id]) }}">@lang('Payment Methods')</a>
																	</div>
																</div>
															</td>
														</tr>
													@endforeach
													</tbody>
												</table>
											</div>
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
