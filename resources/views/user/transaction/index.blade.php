@extends('user.layouts.master')
@section('page_title',__('Transaction List'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Transaction List')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Transaction List')</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row">
						<div class="col-lg-12">
							<div class="card mb-4 card-primary shadow-sm">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Search')</h6>
								</div>
								<div class="card-body">
									<form action="{{ route('user.transaction.search') }}" method="get">
										@include('user.transaction.searchForm')
									</form>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Transaction List')</h6>
								</div>
								<div class="card-body">
									<div class="table-responsive">
										<table
											class="table table-striped table-hover align-items-center table-borderless">
											<thead class="thead-light">
											<tr>
												<th>@lang('Sender')</th>
												<th>@lang('Receiver')</th>
												<th>@lang('Receiver E-Mail')</th>
												<th>@lang('Transaction ID')</th>
												<th>@lang('Requested Amount')</th>
												<th>@lang('Type')</th>
												<th>@lang('Status')</th>
												<th>@lang('Created time')</th>
											</tr>
											</thead>
											<tbody>
											@forelse($transactions as $key => $value)
												<tr>
													<td data-label="@lang('Sender')">
														@if($value->transactional_type == App\Models\Exchange::class)
															{{ __(optional(optional(optional($value->transactional)->fromWallet)->currency)->name) ?? __('N/A') }}
														@elseif($value->transactional_type == App\Models\Invoice::class)
															{{ __(optional($value->transactional->sender)->name) ?? __('N/A') }}
														@elseif($value->transactional_type == App\Models\ProductOrder::class || $value->transactional_type == App\Models\QRCode::class)
															{{ __(optional($value->transactional)->email) ?? __('N/A') }}
														@elseif($value->transactional_type == App\Models\VirtualCardTransaction::class)
															{{ __(optional($value->transactional->user)->name) ?? __('N/A') }}
														@elseif($value->transactional_type == 'App\Models\VirtualCardOrder')
															@lang('Admin')
														@elseif($value->transactional_type == \App\Models\Fund::class && optional($value->transactional)->card_order_id != null)
															{{ __(optional(optional($value->transactional)->receiver)->name) ?? __('N/A') }}
														@elseif($value->transactional_type == App\Models\BillPay::class && $value->currency_code != null)
															{{ __(optional($value->transactional->user)->name) ?? __('N/A') }}
														@elseif($value->transactional_type == App\Models\BillPay::class && $value->currency_code == null)
															@lang('Admin')
														@else
															{{ __(optional(optional($value->transactional)->sender)->name) ?? __('N/A') }}
														@endif
													</td>
													<td data-label="@lang('Receiver')">
														@if($value->transactional_type == App\Models\Exchange::class)
															{{ __(optional(optional(optional($value->transactional)->toWallet)->currency)->name) ?? __('N/A') }}
														@elseif($value->transactional_type == App\Models\ProductOrder::class || $value->transactional_type == App\Models\ApiOrder::class || $value->transactional_type == App\Models\QRCode::class || $value->transactional_type == App\Models\VirtualCardOrder::class)
															{{ __(optional($value->transactional->user)->name) ?? __('N/A') }}
														@elseif($value->transactional_type == \App\Models\Fund::class && optional($value->transactional)->card_order_id != null)
															@lang('Admin')
														@elseif($value->transactional_type == App\Models\BillPay::class && $value->currency_code == null)
															{{ __(optional($value->transactional->user)->name) ?? __('N/A') }}
														@else
															{{ __(optional(optional($value->transactional)->receiver)->name) ?? __('N/A') }}
														@endif
													</td>
													<td data-label="@lang('Receiver E-Mail')">
														@if($value->transactional_type == App\Models\ProductOrder::class || $value->transactional_type == App\Models\QRCode::class || $value->transactional_type == App\Models\VirtualCardTransaction::class )
															-
														@elseif($value->transactional_type == App\Models\VirtualCardOrder::class || $value->transactional_type == App\Models\ApiOrder::class)
															{{ __(optional($value->transactional->user)->email)?? 'N/A'}}
														@elseif($value->transactional_type == \App\Models\Fund::class && optional($value->transactional)->card_order_id != null)
															-
														@elseif($value->transactional_type == App\Models\BillPay::class && $value->currency_code == null)
															{{ __(optional($value->transactional->user)->email) ?? __('N/A') }}
														@else
															{{ __(optional($value->transactional)->email)?? __(optional($value->transactional)->customer_email)}}
														@endif
													</td>
													<td data-label="@lang('Transaction ID')">
														@if($value->transactional_type == App\Models\ProductOrder::class || $value->transactional_type == App\Models\QRCode::class || $value->transactional_type == App\Models\VirtualCardTransaction::class || $value->transactional_type == App\Models\VirtualCardOrder::class)
															-
														@else
															{{ __(optional($value->transactional)->utr) ?? __(optional($value->transactional)->has_slug) }}
														@endif
													</td>
													@if($value->transactional_type == 'App\Models\VirtualCardOrder')
														<td data-label="@lang('Amount')">{{ (getAmount($value->amount)).' '.__(optional($value->transactional)->currency) }}</td>
													@elseif($value->transactional_type == 'App\Models\BillPay' && $value->currency_code != null)
														<td data-label="@lang('Amount')">{{ getAmount(optional($value->transactional)->amount)}} {{$value->currency_code}}</td>
													@elseif($value->transactional_type == 'App\Models\BillPay' && $value->currency_code == null)
														<td data-label="@lang('Amount')">{{ getAmount(optional($value->transactional)->amount)}} {{optional($value->transactional->baseCurrency)->code}}</td>
													@elseif(optional($value->transactional)->amount != 0)
														<td data-label="@lang('Amount')">{{ (getAmount(optional($value->transactional)->amount)).' '.__(optional(optional($value->transactional)->currency)->code) }}</td>
													@else
														@if($value->transactional_type == 'App\Models\ProductOrder')
															<td data-label="@lang('Amount')">{{ (getAmount(optional($value->transactional)->total_amount))+(getAmount(optional($value->transactional)->shipping_charge)).' '.__(optional(optional($value->transactional)->currency)->code) }}</td>
														@else
															<td data-label="@lang('Amount')">
																{{ (getAmount(optional($value->transactional)->grand_total)).' '.__(optional(optional($value->transactional)->currency)->code) }}</td>
														@endif
													@endif
													<td data-label="@lang('Type')">
														@if($value->transactional_type == \App\Models\QRCode::class)
															{{__('QR Payment')}}
														@elseif($value->transactional_type == \App\Models\VirtualCardOrder::class)
															{{__('VirtualCard')}}
														@elseif($value->transactional_type == \App\Models\Fund::class && optional($value->transactional)->card_order_id != null)
															{{__('VirtualCard')}}
														@else
															{{ __(str_replace('App\Models\\', '', $value->transactional_type)) }}
														@endif
													</td>
													<td data-label="@lang('Status')">
														@if($value->transactional_type == 'App\Models\Invoice')
															@if($value->transactional->status == 'paid')
																<span
																	class="badge badge-success">@lang('Paid')</span>
															@elseif($value->transactional->status == 'rejected')
																<span
																	class="badge badge-danger">@lang('Rejected')</span>
															@endif
														@elseif($value->transactional_type == 'App\Models\Voucher')
															@if($value->transactional->status)
																<span
																	class="badge badge-success">@lang('Success')</span>
															@else
																<span
																	class="badge badge-warning">@lang('Pending')</span>
															@endif
														@elseif($value->transactional_type == 'App\Models\ProductOrder')
															@if($value->transactional->status == 1)
																<span
																	class="badge badge-success">@lang('Paid')</span>
															@else
																<span
																	class="badge badge-warning">@lang('Pending')</span>
															@endif
														@elseif($value->transactional_type == 'App\Models\QRCode' || $value->transactional_type == 'App\Models\ApiOrder')
															@if($value->transactional->status == 1)
																<span
																	class="badge badge-success">@lang('Paid')</span>
															@else
																<span
																	class="badge badge-warning">@lang('Pending')</span>
															@endif
														@elseif($value->transactional_type == 'App\Models\VirtualCardTransaction')
															<span
																class="badge badge-success">@lang('Completed')</span>
														@elseif($value->transactional_type == 'App\Models\VirtualCardOrder')
															<span
																class="badge badge-danger">@lang('Return')</span>
														@elseif($value->transactional_type == \App\Models\Fund::class && optional($value->transactional)->card_order_id != null)
															<span
																class="badge badge-success">@lang('Send')</span>
														@elseif($value->transactional_type == 'App\Models\BillPay' && $value->currency_code != null)
															<span
																class="badge badge-success">@lang('Paid')</span>
														@elseif($value->transactional_type == 'App\Models\BillPay' && $value->currency_code == null)
															<span
																class="badge badge-danger">@lang('Return')</span>
														@endif
													</td>
													<td data-label="@lang('Created time')"> {{ dateTime($value->created_at)}} </td>
												</tr>
											@empty
												<tr>
													<th colspan="100%" class="text-center">@lang('No data found')</th>
												</tr>
											@endforelse
											</tbody>
										</table>
									</div>
									<div class="card-footer">
										{{ $transactions->links() }}
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
