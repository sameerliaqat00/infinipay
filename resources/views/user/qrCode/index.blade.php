@extends('user.layouts.master')
@section('page_title',__('QR Payment List'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('QR Payment List')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('QR Payment List')</div>
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
									<form action="{{ route('user.qr.payment') }}" method="get">
										@include('user.qrCode.searchForm')
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
									<h6 class="m-0 font-weight-bold text-primary">@lang('QR Payment List')</h6>
								</div>
								<div class="card-body">
									<div class="table-responsive">
										<table
											class="table table-striped table-hover align-items-center table-borderless">
											<thead class="thead-light">
											<tr>
												<th>@lang('SL.')</th>
												<th>@lang('Sender Email')</th>
												<th>@lang('Amount')</th>
												<th>@lang('Charge')</th>
												<th>@lang('Gateway')</th>
												<th>@lang('Time')</th>
											</tr>
											</thead>
											<tbody>
											@forelse($qrPayments as $key => $value)
												<tr>
													<td data-label="@lang('SL.')">{{++$key }}</td>
													<td data-label="@lang('Sender Email')">{{$value->email }}</td>
													<td data-label="@lang('Amount')">{{$value->amount }} {{optional($value->currency)->code}}</td>
													<td data-label="@lang('Charge')">
														<span
															class="text-danger">{{$value->charge }} {{optional($value->currency)->code}}</span>
													</td>
													<td data-label="@lang('Gateway')">{{optional($value->gateway)->name }}</td>
													<td data-label="@lang('Time')">{{dateTime($value->created_at)}}</td>
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
										{{ $qrPayments->links() }}
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
