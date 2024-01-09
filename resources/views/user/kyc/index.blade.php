@extends('user.layouts.master')
@section('page_title', __('My KYC List'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('My KYC List')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('My KYC List')</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row">
						<div class="col-lg-12">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('My KYC List')</h6>
								</div>
								<div class="card-body">
									<div class="table-responsive">
										<table
											class="table table-striped table-hover align-items-center table-borderless">
											<thead class="thead-light">
											<tr>
												<th>@lang('SL')</th>
												<th>@lang('User')</th>
												<th>@lang('Status')</th>
												<th>@lang('Submitted At')</th>
												<th>@lang('Action')</th>
											</tr>
											</thead>
											<tbody>
											@forelse($kycLists as $key => $value)
												<tr>
													<td data-label="@lang('SL')">
														{{loopIndex($kycLists) + $key}}
													</td>
													<td data-label="@lang('Receiver')">
														<a href="javascript:void(0)"
														   class="text-decoration-none">
															<div class="d-lg-flex d-block align-items-center ">
																<div class="mr-3"><img
																		src="{{ optional($value->user)->profilePicture()??asset('assets/upload/boy.png') }}"
																		alt="user"
																		class="rounded-circle" width="35"
																		data-toggle="tooltip" title=""
																		data-original-title="{{optional($value->user)->name}}">
																</div>
																<div
																	class="d-inline-flex d-lg-block align-items-center">
																	<p class="text-dark mb-0 font-16 font-weight-medium">{{Str::limit(optional($value->user)->name?? __('N/A'),20)}}</p>
																	<span
																		class="text-muted font-14 ml-1">{{ '@'.optional($value->user)->username}}</span>
																</div>
															</div>
														</a>
													</td>
													<td data-label="@lang('Status')">
														@if($value->status == '1')
															<span class="badge badge-success">@lang('Approved')</span>
														@elseif($value->status == '0')
															<span class="badge badge-warning">@lang('Pending')</span>
														@elseif($value->status == '2')
															<span class="badge badge-danger">@lang('Rejected')</span>
														@endif
													</td>
													<td data-label="@lang('Submitted At')">{{dateTime($value->created_at,'d/m/Y H:i')}}</td>
													<td data-label="@lang('Action')">
														<a href="{{route('user.kycView',$value->id)}}"
														   class="btn btn-outline-primary btn-sm">@lang('View')</a>
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
										{{ $kycLists->links() }}
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

