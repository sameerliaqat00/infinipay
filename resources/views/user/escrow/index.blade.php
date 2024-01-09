@extends('user.layouts.master')
@section('page_title',__('Escrow lists'))

@section('content')
<div class="main-content">
	<section class="section">
		<div class="section-header">
			<h1>@lang('Escrow Lists')</h1>
			<div class="section-header-breadcrumb">
				<div class="breadcrumb-item active">
					<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
				</div>
				<div class="breadcrumb-item">@lang('Escrow Lists')</div>
			</div>
		</div>

		<div class="row mb-3">
			<div class="container-fluid" id="container-wrapper">
				<div class="row">
					<div class="col-lg-12">
						<div class="card mb-4 shadow-sm card-primary">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
								<h6 class="m-0 font-weight-bold text-primary">@lang('Search')</h6>
							</div>
							<div class="card-body">
								<form action="{{ route('escrow.search') }}" method="get">
									@include('user.escrow.searchForm')
								</form>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<div class="card mb-4 card-primary shadow">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
								<h6 class="m-0 font-weight-bold text-primary">@lang('Escrow lists')</h6>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table class="table table-striped table-hover align-items-center table-borderless">
										<thead class="thead-light">
										<tr>
											<th>@lang('SL')</th>
											<th>@lang('Receiver')</th>
											<th>@lang('Receiver E-Mail')</th>
											<th>@lang('Transaction ID')</th>
											<th>@lang('Amount')</th>
											<th>@lang('Type')</th>
											<th>@lang('Status')</th>
											<th>@lang('Created time')</th>
											<th>@lang('Action')</th>
										</tr>
										</thead>
										<tbody>
										@forelse($escrows as $key => $escrow)
											<tr>
												<td data-label="@lang('SL')">{{loopIndex($escrows) + $key}}</td>
												<td data-label="@lang('Receiver')">{{ __(optional($escrow->receiver)->name) ?? __('N/A') }}</td>
												<td data-label="@lang('Receiver E-Mail')">{{ __($escrow->email) }}</td>
												<td data-label="@lang('Transaction ID')">{{ __($escrow->utr) }}</td>
												<td data-label="@lang('Amount')">{{ (getAmount($escrow->amount)) .' '. __(optional($escrow->currency)->code) }}</td>
												<td data-label="@lang('Type')">
													@if($escrow->sender_id == Auth::id())
														<span class="badge badge-info">@lang('Sent')</span>
													@else
														<span class="badge badge-success">@lang('Received')</span>
													@endif
												</td>
												<td data-label="@lang('Status')">
													@if($escrow->status == 0)
														<span class="badge badge-warning">@lang('Pending')</span>
													@elseif($escrow->status == 1)
														<span class="badge badge-primary">@lang('Generated')</span>
													@elseif($escrow->status == 2)
														<span class="badge badge-secondary">@lang('Deposited')</span>
													@elseif($escrow->status == 3)
														<span class="badge badge-info">@lang('Request for payment')</span>
													@elseif($escrow->status == 4)
														<span class="badge badge-success">@lang('Payment Disbursed')</span>
													@elseif($escrow->status == 5)
														<span class="badge badge-danger">@lang('Canceled')</span>
													@elseif($escrow->status == 6 && optional($escrow->disputable)->status == 0)
														@if($escrow->sender_id == Auth::id())
															<span class="badge badge-warning">@lang('Hold')</span>
														@elseif($escrow->receiver_id == Auth::id())
															<span class="badge badge-warning">@lang('Dispute')</span>
														@endif
													@elseif($escrow->status == 6 && optional($escrow->disputable)->status == 1)
														<span class="badge badge-warning">@lang('Dispute')</span> <span
																class="badge badge-info">@lang('Refunded')</span>
													@elseif($escrow->status == 6 && optional($escrow->disputable)->status == 2)
														<span class="badge badge-warning">@lang('Dispute')</span> <span
																class="badge badge-info">@lang('Payment Disbursed')</span>
													@else
														<span class="badge badge-dark">@lang('N/A')</span>
													@endif
												</td>
												<td data-label="@lang('Created time')"> {{ __(date('Y-m-d h:i a',strtotime($escrow->created_at))) }} </td>
												<td data-label="@lang('Action')">
													@if($escrow->status != 0)
														<a href="{{ route('escrow.paymentView',$escrow->utr) }}"
														   class="btn btn-sm btn-info"> @lang('View')</a>
													@else
														<a href="{{ route('escrow.confirmInit',$escrow->utr) }}" target="_blank"
														   class="btn btn-sm btn-primary">@lang('Generate')</a>
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
									{{ $escrows->links() }}
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
