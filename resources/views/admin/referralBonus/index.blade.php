@extends('admin.layouts.master')
@section('page_title', __('Commission List'))
@push('extra_styles')
	<link href="{{ asset('assets/dashboard/css/select2.min.css') }}" rel="stylesheet" type="text/css">
@endpush
@section('content')
<div class="main-content">
	<section class="section">
		<div class="section-header">
			<h1>@lang('Commission List')</h1>
			<div class="section-header-breadcrumb">
				<div class="breadcrumb-item active">
					<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
				</div>
				<div class="breadcrumb-item">@lang('Commission List')</div>
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
								<form action="{{ route('admin.commission.search') }}" method="get">
									@include('admin.referralBonus.searchForm')
								</form>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<div class="card mb-4 card-primary shadow">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
								<h6 class="m-0 font-weight-bold text-primary">@lang('Commission List')</h6>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table class="table table-striped table-hover align-items-center table-borderless">
										<thead class="thead-light">
										<tr>
											<th>@lang('SL')</th>
											<th>@lang('Profit From')</th>
											<th>@lang('Profit To')</th>

											<th>@lang('Amount')</th>
											<th>@lang('Level')</th>
											<th>@lang('Title')</th>

											<th>@lang('Transaction ID')</th>
											<th>@lang('Transaction At')</th>
										</tr>
										</thead>
										<tbody>
										@forelse($commissionEntries as $key => $value)
											<tr>
												<td data-label="@lang('SL')">{{loopIndex($commissionEntries) + $key}}</td>
												<td data-label="@lang('Profit From')">
													<a href="{{ route('user.edit', $value->from_user)}}" class="text-decoration-none">
														<div class="d-lg-flex d-block align-items-center ">
															<div class="mr-3"><img src="{{ optional($value->sender)->profilePicture()??asset('assets/upload/boy.png')  }}" alt="user"
																				   class="rounded-circle" width="35" data-toggle="tooltip" title="" data-original-title="{{optional($value->sender)->name?? __('N/A')}}">
															</div>
															<div class="d-inline-flex d-lg-block align-items-center">
																<p class="text-dark mb-0 font-16 font-weight-medium">{{Str::limit(optional($value->sender)->name?? __('N/A'),20)}}</p>
																<span class="text-muted font-14 ml-1">{{ '@'.optional($value->sender)->username?? __('N/A')}}</span>
															</div>
														</div>
													</a>

												</td>
												<td data-label="@lang('Profit To')">

													<a href="{{ route('user.edit', $value->to_user)}}" class="text-decoration-none">
														<div class="d-lg-flex d-block align-items-center ">
															<div class="mr-3"><img src="{{ optional($value->receiver)->profilePicture()??asset('assets/upload/boy.png') }}" alt="user"
																				   class="rounded-circle" width="35" data-toggle="tooltip" title="" data-original-title="{{optional($value->receiver)->name?? __('N/A')}}">
															</div>
															<div class="d-inline-flex d-lg-block align-items-center">
																<p class="text-dark mb-0 font-16 font-weight-medium">{{Str::limit(optional($value->receiver)->name?? __('N/A'),20)}}</p>
																<span class="text-muted font-14 ml-1">{{ '@'.optional($value->receiver)->username?? __('N/A')}}</span>
															</div>
														</div>
													</a>

												</td>

												<td data-label="@lang('Amount')">{{ (getAmount($value->commission_amount)).' '.__(optional($value->currency)->code) }}</td>

												<td data-label="@lang('Level')">{{ __($value->level) }}</td>

												<td data-label="@lang('Title')">{{ __($value->title) }}</td>
												<td data-label="@lang('Transaction ID')">{{ __($value->utr) }}</td>
												<td data-label="@lang('Transaction At')"> {{ dateTime($value->created_at)}} </td>
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
									{{ $commissionEntries->links() }}
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
@push('extra_scripts')
	<script src="{{ asset('assets/dashboard/js/select2.min.js') }}"></script>
@endpush
@section('scripts')
	<script>
		'use strict';
		$(document).ready(function () {
			$('.select2-single').select2();
		})
	</script>
@endsection
