@extends('user.layouts.master')
@section('page_title',__('Payout List'))

@section('content')
<div class="main-content">
	<section class="section">
		<div class="section-header">
			<h1>@lang('Payout List')</h1>
			<div class="section-header-breadcrumb">
				<div class="breadcrumb-item active">
					<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
				</div>
				<div class="breadcrumb-item">@lang('Payout List')</div>
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
								<form action="{{ route('payout.search') }}" method="get">
									@include('user.payout.searchForm')
								</form>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<div class="card mb-4 card-primary shadow">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
								<h6 class="m-0 font-weight-bold text-primary">@lang('Payout List')</h6>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table class="table table-striped table-hover align-items-center table-borderless">
										<thead class="thead-light">
										<tr>
											<th>@lang('SL')</th>
											<th>@lang('Amount')</th>
											<th>@lang('Transaction ID')</th>
											<th>@lang('Status')</th>
											<th>@lang('Created time')</th>
											<th>@lang('Action')</th>
										</tr>
										</thead>
										<tbody>
										@forelse($payouts as $key => $value)
											<tr>
												<td data-label="@lang('SL')">{{loopIndex($payouts) + $key}}</td>
												<td data-label="@lang('Amount')">{{ (getAmount($value->amount)).' '.__(optional($value->currency)->code) }}</td>
												<td data-label="@lang('Transaction ID')">{{ __($value->utr) }}</td>
												<td data-label="@lang('Status')">
													@if($value->status == 0)
														<span class="badge badge-warning">@lang('Pending')</span>
													@elseif($value->status == 1)
														<span class="badge badge-info">@lang('Generated')</span>
													@elseif($value->status == 2)
														<span class="badge badge-success">@lang('Payment Done')</span>
													@elseif($value->status == 5)
														<span class="badge badge-danger">@lang('Canceled')</span>
													@elseif($value->status == 6)
														<span class="badge badge-danger">@lang('Failed')</span>
													@endif
												</td>
												<td data-label="@lang('Created time')"> {{ dateTime($value->created_at)}} </td>
												<td data-label="@lang('Action')">
													@if($value->status == 0)
														<a href="{{ route('payout.confirm',$value->utr) }}" target="_blank"
														   class="btn btn-sm btn-primary">@lang('Confirm')</a>
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
									{{ $payouts->links() }}
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

