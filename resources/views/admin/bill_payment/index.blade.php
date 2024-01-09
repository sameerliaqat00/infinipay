@extends('admin.layouts.master')
@section('page_title',__('Bill List'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Bill List')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Bill List')</div>
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
									<form action="{{ route('bill.pay.list') }}" method="get">
										@include('admin.bill_payment.searchForm')
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
									<h6 class="m-0 font-weight-bold text-primary">@lang('Bill List')</h6>
								</div>
								<div class="card-body">
									<div class="table-responsive">
										<table
											class="table table-striped table-hover align-items-center table-borderless">
											<thead class="thead-light">
											<tr>
												<th>@lang('SL')</th>
												<th>@lang('User')</th>
												<th>@lang('Method')</th>
												<th>@lang('Category')</th>
												<th>@lang('Type')</th>
												<th>@lang('Amount')</th>
												<th>@lang('Charge')</th>
												<th>@lang('Status')</th>
												<th>@lang('Created time')</th>
												<th>@lang('Action')</th>
											</tr>
											</thead>
											<tbody>
											@forelse($bills as $key => $value)
												<tr>
													<td data-label="@lang('SL')">{{loopIndex($bills) + $key}}</td>
													<td data-label="@lang('User')">
														<a href="{{ route('user.edit', $value->user_id)}}"
														   class="text-decoration-none">
															<div class="d-lg-flex d-block align-items-center ">
																<div class="mr-3"><img
																		src="{{ optional($value->user)->profilePicture()??asset('assets/upload/boy.png') }}"
																		alt="user"
																		class="rounded-circle" width="35"
																		data-toggle="tooltip" title=""
																		data-original-title="{{optional($value->user)->name?? __('N/A')}}">
																</div>
																<div
																	class="d-inline-flex d-lg-block align-items-center">
																	<p class="text-dark mb-0 font-16 font-weight-medium">{{Str::limit(optional($value->user)->name?? __('N/A'),20)}}</p>
																	<span
																		class="text-muted font-14 ml-1">{{ '@'.optional($value->user)->username?? __('N/A')}}</span>
																</div>
															</div>
														</a>
													</td>
													<td data-label="@lang('Type')">{{ __(optional($value->method)->methodName) }}</td>
													<td data-label="@lang('Category')">{{ __(str_replace('_',' ',ucfirst($value->category_name))) }}</td>
													<td data-label="@lang('Type')">{{ __($value->type) }}</td>
													<td data-label="@lang('Amount')">{{ (getAmount($value->amount,2)).' '.__($value->currency) }}</td>
													<td data-label="@lang('Charge')"><span
															class="text-danger">{{ (getAmount($value->charge,2)).' '.__($value->currency) }}</span>
													</td>
													<td data-label="@lang('Status')">
														@if($value->status == 0)
															<span class="badge badge-warning">@lang('Generated')</span>
														@elseif($value->status == 1)
															<span class="badge badge-info">@lang('Pending')</span>
														@elseif($value->status == 2)
															<span
																class="badge badge-info">@lang('Payment Done')</span>
														@elseif($value->status == 3)
															<span class="badge badge-success">@lang('Completed')</span>
														@elseif($value->status == 4)
															<span class="badge badge-danger">@lang('Return')</span>
														@endif
													</td>
													<td data-label="@lang('Created time')"> {{ dateTime($value->created_at)}} </td>
													<td data-label="@lang('Action')">
														<a href="{{ route('bill.pay.view',$value->utr) }}"
														   class="btn btn-sm btn-outline-info">@lang('View')</a>
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
										{{ $bills->links() }}
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
