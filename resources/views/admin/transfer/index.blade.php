@extends('admin.layouts.master')
@section('page_title', __('Transfer List'))

@section('content')
<div class="main-content">
	<section class="section">
		<div class="section-header">
			<h1>@lang('Transfer List')</h1>
			<div class="section-header-breadcrumb">
				<div class="breadcrumb-item active">
					<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
				</div>
				<div class="breadcrumb-item">@lang('Transfer List')</div>
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
									<form action="{{ route('admin.user.transfer.search',$userId) }}" method="get">
										@include('admin.transfer.searchForm')
									</form>
								@else
									<form action="{{ route('admin.transfer.search') }}" method="get">
										@include('admin.transfer.searchForm')
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
								<h6 class="m-0 font-weight-bold text-primary">@lang('Transfer List')</h6>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table class="table table-striped table-hover align-items-center table-borderless">
										<thead class="thead-light">
											<tr>
												<th>@lang('SL')</th>
												<th>@lang('Sender')</th>
												<th>@lang('Amount')</th>
												<th>@lang('Receiver')</th>
												<th>@lang('Receiver E-Mail')</th>
												<th>@lang('Transaction ID')</th>
												<th>@lang('Status')</th>
												<th>@lang('Transfer At')</th>
											</tr>
										</thead>
										<tbody>
										@forelse($transfers as $key => $value)
											<tr>
												<td data-label="@lang('Sl')">{{loopIndex($transfers) + $key }}</td>

												<td data-label="@lang('Sender')">
													<a href="{{ route('user.edit', $value->sender_id)}}" class="text-decoration-none">
														<div class="d-lg-flex d-block align-items-center ">
															<div class="mr-3"><img src="{{ optional($value->sender)->profilePicture()??asset('assets/upload/boy.png') }}" alt="user"
																				   class="rounded-circle" width="35" data-toggle="tooltip" title="" data-original-title="{{optional($value->sender)->name}}">
															</div>
															<div class="d-inline-flex d-lg-block align-items-center">
																<p class="text-dark mb-0 font-16 font-weight-medium">{{Str::limit(optional($value->sender)->name?? __('N/A'),20)}}</p>
																<span class="text-muted font-14 ml-1">{{ '@'.optional($value->sender)->username}}</span>
															</div>
														</div>
													</a>
												</td>


												<td data-label="@lang('Amount')">{{ (getAmount($value->amount)).' '.__(optional($value->currency)->code) }}</td>

												<td data-label="@lang('Receiver')">
													<a href="{{ route('user.edit', $value->receiver_id)}}" class="text-decoration-none">
														<div class="d-lg-flex d-block align-items-center ">
															<div class="mr-3"><img src="{{ optional($value->receiver)->profilePicture()??asset('assets/upload/boy.png') }}" alt="user"
																				   class="rounded-circle" width="35" data-toggle="tooltip" title="" data-original-title="{{optional($value->receiver)->name}}">
															</div>
															<div class="d-inline-flex d-lg-block align-items-center">
																<p class="text-dark mb-0 font-16 font-weight-medium">{{Str::limit(optional($value->receiver)->name?? __('N/A'),20)}}</p>
																<span class="text-muted font-14 ml-1">{{ '@'.optional($value->receiver)->username}}</span>
															</div>
														</div>
													</a>
												</td>

												<td data-label="@lang('Receiver E-Mail')">{{ __($value->email) }}</td>
												<td data-label="@lang('Transaction ID')">{{ __($value->utr)  }}</td>
												<td data-label="@lang('Status')">
													@if($value->status)
														<span class="badge badge-success">@lang('Success')</span>
													@else
														<span class="badge badge-warning">@lang('Pending')</span>
													@endif
												</td>
												<td data-label="@lang('Transfer At')"> {{ dateTime($value->created_at)}} </td>
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
									{{ $transfers->links() }}
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
