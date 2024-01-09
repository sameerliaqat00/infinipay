@extends('admin.layouts.master')
@section('page_title', __('Exchange Money List'))

@section('content')
<div class="main-content">
	<section class="section">
		<div class="section-header">
			<h1>@lang('Exchange Money List')</h1>
			<div class="section-header-breadcrumb">
				<div class="breadcrumb-item active">
					<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
				</div>
				<div class="breadcrumb-item">@lang('Exchange Money List')</div>
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
								@if(isset($userId))
									<form action="{{ route('admin.user.exchange.search',$userId) }}" method="get">
										@include('admin.exchange.searchForm')
									</form>
								@else
									<form action="{{ route('admin.exchange.search') }}" method="get">
										@include('admin.exchange.searchForm')
									</form>
								@endif
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<div class="card mb-4 card-primary shadow">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
								<h6 class="m-0 font-weight-bold text-primary">@lang('Exchange Money List')</h6>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table class="table table-striped table-hover align-items-center table-borderless">
										<thead class="thead-light">
										<tr>
											<th>@lang('SL')</th>
											<th>@lang('User')</th>
											<th>@lang('Exchange')</th>
											<th>@lang('Amount')</th>
											<th>@lang('Charge')</th>
											<th>@lang('Exchange Rate')</th>
											<th>@lang('Exchange Amount')</th>
											<th>@lang('Transaction ID')</th>
											<th>@lang('Status')</th>
											<th>@lang('Exchanged At')</th>
										</tr>
										</thead>
										<tbody>
										@forelse($exchanges as $key => $value)
											<tr>
												<td data-label="@lang('SL')">
													{{loopIndex($exchanges) + $key}}
												</td>
												<td data-label="@lang('User')">
													<a href="{{ route('user.edit', $value->user_id)}}" class="text-decoration-none">
														<div class="d-lg-flex d-block align-items-center ">
															<div class="mr-3">
																<img src="{{ optional($value->user)->profilePicture()??asset('assets/upload/boy.png') }}" alt="user"
																				   class="rounded-circle" width="35" data-toggle="tooltip" title="" data-original-title="{{optional($value->user)->name?? __('N/A')}}">
															</div>
															<div class="d-inline-flex d-lg-block align-items-center">
																<p class="text-dark mb-0 font-16 font-weight-medium">{{Str::limit(optional($value->user)->name?? __('N/A'),20)}}</p>
																<span class="text-muted font-14 ml-1">{{ '@'.optional($value->user)->username?? __('N/A')}}</span>
															</div>
														</div>
													</a>
												</td>

												<td data-label="@lang('Exchange')">
													{{ __(optional($value->fromWallet->currency)->code ?? __('N/A')) }} <i class="fa fa-exchange-alt"></i> {{ __(optional($value->toWallet->currency)->code ?? __('N/A')) }}</td>
												<td data-label="@lang('Amount')">{{ (getAmount($value->amount)) .' '. __(optional($value->fromWallet->currency)->code) }}</td>
												<td data-label="@lang('Charge')">{{ (getAmount($value->charge)) .' '. __(optional($value->fromWallet->currency)->code) }}</td>
												<td data-label="@lang('Exchange Rate')">{{ (getAmount($value->exchange_rate)) .' '. __(optional($value->fromWallet->currency)->code) }}</td>
												<td data-label="@lang('Exchange Amount')">{{ (getAmount($value->received_amount)) .' '. __(optional($value->toWallet->currency)->code) }}</td>
												<td data-label="@lang('Transaction ID')">{{ __($value->utr) }}</td>
												<td data-label="@lang('Status')">
													@if($value->status)
														<span class="badge badge-info">@lang('Completed')</span>
													@else
														<span class="badge badge-warning">@lang('Pending')</span>
													@endif
												</td>
												<td data-label="@lang('Exchanged At')"> {{ dateTime($value->created_at) }} </td>
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
