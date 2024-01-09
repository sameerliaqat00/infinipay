@extends('admin.layouts.master')
@section('page_title',__('Bill Method List'))
@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Bill Method List')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Bill Method List')</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row justify-content-md-center">
						<div class="col-lg-12">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Bill Method List')</h6>
								</div>

								<div class="card-body">
									<div class="table-responsive">
										<table class="table table-striped table-hover align-items-center table-flush">
											<thead class="thead-light">
											<tr>
												<th>@lang('Name')</th>
												<th>@lang('Description')</th>
												<th>@lang('Min limit')</th>
												<th>@lang('Max limit')</th>
												<th>@lang('Logo')</th>
												<th>@lang('Status')</th>
												<th>@lang('Action')</th>
											</tr>
											</thead>
											<tbody>
											@foreach($billMethods as $key => $value)
												<tr>
													<td data-label="@lang('Name')">{{ __($value->methodName) }} @if($value->is_automatic == 1)
															<sup
																class="badge badge-primary badge-p-custom ml-2">@lang('Automatic')</sup>
														@endif</td>
													<td data-label="@lang('Description')">{{ __($value->description) }}</td>
													<td data-label="@lang('Min limit')">{{ (getAmount($value->min_limit)) }}</td>
													<td data-label="@lang('Max limit')">{{ (getAmount($value->max_limit)) }}</td>
													<td data-label="@lang('Logo')"><img
															class="img-profile-custom rounded-circle"
															src="{{asset('assets/upload/billPaymentMethod').'/'.$value->logo }}">
													</td>
													<td data-label="@lang('Status')">
														@if($value->is_default)
															<span class="badge badge-success">@lang('Default')</span>
														@elseif($value->is_active)
															<span class="badge badge-info">@lang('Active')</span>
														@else
															<span class="badge badge-warning">@lang('Inactive')</span>
														@endif
													</td>
													<td data-label="@lang('Action')">
														<a href="{{ route('bill.method.edit',$value->id) }}"
														   class="btn btn-sm btn-outline-primary"><i
																class="fas fa-edit"></i> @lang('Edit')
														</a>
													</td>
												</tr>
											@endforeach
											</tbody>
										</table>
									</div>
									<div class="card-footer">
										{{ $billMethods->links() }}
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


