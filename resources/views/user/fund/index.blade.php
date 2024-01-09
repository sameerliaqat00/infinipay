@extends('user.layouts.master')
@section('page_title',__('Fund Added List'))

@section('content')
	<!-- Main Content -->
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Fund Added List')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Fund Added List')</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row">
						<div class="col-lg-12">
							<div class="card card-primary mb-4 shadow-sm">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Search')</h6>
								</div>
								<div class="card-body">
									<form action="{{ route('fund.search') }}" method="get">
										@include('user.fund.searchForm')
									</form>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<div class="card card-primary mb-4 shadow">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Fund Added List')</h6>
								</div>
								<div class="card-body">
									<div class="table-responsive">
										<table class="table table-striped table-hover align-items-center table-borderless">
											<thead class="thead-light">
											<tr>
												<th>@lang('SL')</th>
												<th>@lang('Method')</th>
												<th>@lang('Transaction ID')</th>
												<th>@lang('Requested Amount')</th>
												<th>@lang('Status')</th>
												<th>@lang('Created time')</th>
											</tr>
											</thead>
											<tbody>
											@forelse($funds as $key => $value)
												<tr>
													<td data-label="@lang('SL')">{{loopIndex($funds) + $key }}</td>
													<td data-label="@lang('Method')">{{ __(optional(optional($value->depositable)->gateway)->name) ?? __('N/A') }}</td>
													<td data-label="@lang('Transaction ID')">{{ __($value->utr) }}</td>
													<td data-label="@lang('Requested Amount')">{{ (getAmount($value->amount)).' '.__(optional($value->currency)->code) }}</td>
													<td data-label="@lang('Status')">
														@if($value->status)
															<span class="badge badge-success">@lang('Success')</span>
														@else
															<span class="badge badge-warning">@lang('Pending')</span>
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
										{{ $funds->links() }}
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
