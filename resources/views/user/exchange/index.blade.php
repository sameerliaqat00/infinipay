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

		<div class="row mb-3">
			<div class="container-fluid" id="container-wrapper">
				<div class="row">
					<div class="col-lg-12">
						<div class="card mb-4 card-primary shadow-sm">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
								<h6 class="m-0 font-weight-bold text-primary">@lang('Search')</h6>
							</div>
							<div class="card-body">
								<form action="{{ route('exchange.search') }}" method="get">
									@include('user.exchange.searchForm')
								</form>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<div class="card mb-4 card-primary shadow">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
								<h6 class="m-0 font-weight-bold text-primary">@lang('Exchange Money')</h6>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table class="table table-striped table-hover align-items-center table-borderless">
										<thead class="thead-light">
										<tr>
											<th>@lang('Exchange')</th>
											<th>@lang('Amount')</th>
											<th>@lang('Charge')</th>
											<th>@lang('Exchange Rate')</th>
											<th>@lang('Exchange Amount')</th>
											<th>@lang('Transaction ID')</th>
											<th>@lang('Status')</th>
											<th>@lang('Created time')</th>
											<th>@lang('Action')</th>
										</tr>
										</thead>
										<tbody>
										@forelse($exchanges as $key => $value)
											<tr>
												<td data-label="@lang('Exchange')">
													{{ __(optional($value->fromWallet->currency)->code) ?? __('N/A') }}
													<i class="fa fa-exchange-alt text-indigo"></i> {{ __(optional($value->toWallet->currency)->code) ?? __('N/A') }}</td>
												<td data-label="@lang('Amount')">{{ (getAmount($value->amount)) .' '. __(optional(optional($value->fromWallet)->currency)->code) }}</td>
												<td data-label="@lang('Charge')">{{ (getAmount($value->charge)) .' '. __(optional(optional($value->fromWallet)->currency)->code) }}</td>
												<td data-label="@lang('Exchange Rate')">{{ (getAmount($value->exchange_rate)) .' '. __(optional(optional($value->toWallet)->currency)->code) }}</td>
												<td data-label="@lang('Exchange Amount')">{{ (getAmount($value->received_amount)) .' '. __(optional(optional($value->toWallet)->currency)->code) }}</td>
												<td data-label="@lang('Transaction ID')">{{ __($value->utr) }}</td>
												<td data-label="@lang('Status')">
													@if($value->status)
														<span class="badge badge-info">@lang('Completed')</span>
													@else
														<span class="badge badge-warning">@lang('Pending')</span>
													@endif
												</td>
												<td data-label="@lang('Created time')"> {{ dateTime($value->created_at)}} </td>
												<td data-label="@lang('Action')">
													@if(!$value->status)
														<a href="{{ route('exchange.confirm',$value->utr) }}" target="_blank" class="btn btn-sm btn-primary">@lang('Confirm')</a>
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
									{{ $exchanges->links() }}
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
