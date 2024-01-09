@extends('user.layouts.master')
@section('page_title',__('Voucher Lists'))

@section('content')
<div class="main-content">
	<section class="section">
		<div class="section-header">
			<h1>@lang('Voucher Lists')</h1>
			<div class="section-header-breadcrumb">
				<div class="breadcrumb-item active">
					<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
				</div>
				<div class="breadcrumb-item">@lang('Voucher Lists')</div>
			</div>
		</div>

		<div class="row mb-3">
			<div class="container-fluid" id="container-wrapper">
				<div class="row">
					<div class="col-lg-12">
						<div class="card mb-4 card-primary shadow-sm">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
								<h6 class="m-0 font-weight-bold text-primary">@lang('Search')</h6>
							</div>
							<div class="card-body">
								<form action="{{ route('voucher.search') }}" method="get">
									@include('user.voucher.searchForm')
								</form>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<div class="card mb-4 card-primary shadow">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
								<h6 class="m-0 font-weight-bold text-primary">@lang('Voucher lists')</h6>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table class="table table-striped table-hover align-items-center table-borderless">
										<thead class="thead-light">
										<tr>
											<th>@lang('Sender')</th>
											<th>@lang('Receiver')</th>
											<th>@lang('Receiver E-Mail')</th>
											<th>@lang('Currency')</th>
											<th>@lang('Transaction ID')</th>
											<th>@lang('Requested Amount')</th>
											<th>@lang('Type')</th>
											<th>@lang('Status')</th>
											<th>@lang('Created time')</th>
											<th>@lang('Action')</th>
										</tr>
										</thead>
										<tbody>
										@forelse($vouchers as $key => $value)
											<tr>
												<td data-label="@lang('Sender')">{{ __(optional($value->sender)->name) ?? __('N/A') }}</td>
												<td data-label="@lang('Receiver')">{{ __(optional($value->receiver)->name) ?? __('N/A') }}</td>
												<td data-label="@lang('Receiver E-Mail')">{{ __($value->email) }}</td>
												<td data-label="@lang('Currency')">{{ __(optional($value->currency)->name) ?? __('N/A') }}</td>
												<td data-label="@lang('Transaction ID')">{{ __($value->utr) }}</td>
												<td data-label="@lang('Requested Amount')">{{ (getAmount($value->amount)).' '.__(optional($value->currency)->code) }}</td>
												<td data-label="@lang('Type')">
													@if($value->sender_id == Auth::id())
														<span class="badge badge-info">@lang('Sent')</span>
													@else
														<span class="badge badge-success">@lang('Received')</span>
													@endif
												</td>
												<td data-label="@lang('Status')">
													@if($value->status == 1)
														<span class="badge badge-info">@lang('Generated')</span>
													@elseif($value->status == 2)
														<span class="badge badge-success">@lang('Payment done')</span>
													@elseif($value->status == 3)
														<span class="badge badge-success">@lang('Sender request to payment disburse')</span>
													@elseif($value->status == 4)
														<span class="badge badge-success">@lang('Payment disbursed')</span>
													@elseif($value->status == 5)
														<span class="badge badge-danger">@lang('Canceled')</span>
													@elseif($value->status == 0)
														<span class="badge badge-warning">@lang('Pending')</span>
													@else
														<span class="badge badge-warning">@lang('N/A')</span>
													@endif
												</td>
												<td data-label="@lang('Created time')"> {{ dateTime($value->created_at)}} </td>
												<td data-label="@lang('Action')">
													@if($value->status == 0 && $value->sender_id == auth()->id())
														<a href="{{ route('voucher.confirmInit', $value->utr) }}"
														   target="_blank" class="btn btn-sm btn-primary">@lang('Generate')</a>
													@endif
													@if($value->receiver_id == Auth::id())
														<a href="{{ route('voucher.paymentView', $value->utr) }}"
														   class="btn btn-sm btn-primary">@lang('View')</a>
													@endif
												</td>
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
									{{ $vouchers->links() }}
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

