@extends('user.layouts.master')
@section('page_title',__('Redeem codes'))

@section('content')
<div class="main-content">
	<section class="section">
		<div class="section-header">
			<h1>@lang('Redeem Codes')</h1>
			<div class="section-header-breadcrumb">
				<div class="breadcrumb-item active">
					<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
				</div>
				<div class="breadcrumb-item">@lang('Redeem Codes')</div>
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
								<form action="{{ route('redeem.search') }}" method="get">
									@include('user.redeem.searchForm')
								</form>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<div class="card mb-4 shadow card-primary">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
								<h6 class="m-0 font-weight-bold text-primary">@lang('Redeem codes')</h6>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table class="table table-striped table-hover align-items-center table-borderless">
										<thead class="thead-light">
										<tr>
											<th>@lang('Sender')</th>
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
										@forelse($redeemCodes as $key => $value)
											<tr>
												<td data-label="@lang('Sender')">{{ __(optional($value->sender)->name) ?? __('N/A') }}</td>
												<td data-label="@lang('Receiver')">{{ __(optional($value->receiver)->name) ?? __('N/A') }}</td>
												<td data-label="@lang('Receiver E-Mail')">{{ __($value->email) }}</td>
												<td data-label="@lang('Transaction ID')">{{ __($value->utr) }}</td>
												<td data-label="@lang('Amount')">{{ (getAmount($value->amount)) .' '. __(optional($value->currency)->code) }}</td>
												<td data-label="@lang('Type')">
													@if($value->sender_id == Auth::id())
														<span class="badge badge-info">@lang('Sent')</span>
													@else
														<span class="badge badge-success">@lang('Received')</span>
													@endif
												</td>
												<td data-label="@lang('Status')">
													@if($value->status == 1)
														<span class="badge badge-info">@lang('Unused')</span>
													@elseif($value->status == 2)
														<span class="badge badge-success">@lang('Used')</span>
													@else
														<span class="badge badge-warning">@lang('Pending')</span>
													@endif
												</td>
												<td data-label="@lang('Created time')"> {{ dateTime($value->created_at)}} </td>
												<td data-label="@lang('Action')">
													@if($value->status == 0)
														<a href="{{ route('redeem.confirm',$value->utr) }}" target="_blank"
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
									{{ $redeemCodes->links() }}
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
