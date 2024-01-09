@extends('admin.layouts.master')
@section('page_title', __('Charges & Limits'))
@push('extra_styles')
	<link rel="stylesheet" href="{{ asset('assets/dashboard/css/dataTables.bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="main-content">
	<section class="section">
		<div class="section-header">
			<h1>@lang('Charges & Limits')</h1>
			<div class="section-header-breadcrumb">
				<div class="breadcrumb-item active">
					<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
				</div>
				<div class="breadcrumb-item">@lang('Charges & Limits')</div>
			</div>
		</div>


		<div class="section-body">
			<div class="row mt-sm-4">
				<div class="col-12 col-md-4 col-lg-3">
					@include('admin.control_panel.components.sidebar', ['settings' => config('generalsettings.settings'), 'suffix' => 'Settings'])
				</div>
				<div class="col-12 col-md-8 col-lg-9">
					<div class="container-fluid" id="container-wrapper">
						<div class="row justify-content-md-center">
							<div class="col-lg-12">
								<div class="card mb-4 card-primary shadow">
									<div class="card-header py-3 d-flex flex-wrap flex-row align-items-center justify-content-between">
										<h6 class="m-0 font-weight-bold text-primary">@lang('Charges & Limits')</h6>
									</div>
									<div class="card-body">
										<div class="table-responsive">
											<table class="table table-striped table-hover align-items-center table-flush" id="data-table">
												<thead class="thead-light">
												<tr>
													<th>@lang('Name')</th>
													<th>@lang('Txn Type')</th>
													<th>@lang('Min/Max Limit')</th>
													<th>@lang('Payment Method')</th>
													<th>@lang('Charge') %</th>
													<th>@lang('Fixed Charge')</th>
													<th>@lang('Logo')</th>
													<th>@lang('Status')</th>
													<th>@lang('Action')</th>
												</tr>
												</thead>
												<tbody>
												@foreach($chargesLimits as $key => $chargesLimit)
													<tr>
														<td data-label="@lang('Name')">{{ __(optional($chargesLimit->currency)->name ?? __('N/A')) }}</td>
														<td data-label="@lang('Txn Type')">{{ __(ucfirst(array_search($chargesLimit->transaction_type_id, config('transactionType')))) }}</td>
														<td data-label="@lang('Min/Max Limit')">{{ getAmount($chargesLimit->min_limit) }} / {{ getAmount($chargesLimit->max_limit) }}</td>
														<td data-label="@lang('Payment Method')">{{ __(optional($chargesLimit->gateway)->name ?? __('N/A')) }}</td>
														<td data-label="@lang('Charge') %">{{ getAmount($chargesLimit->percentage_charge) }}</td>
														<td data-label="@lang('Fixed Charge')">{{ getAmount($chargesLimit->fixed_charge) }}</td>
														<td data-label="@lang('Logo')"><img class="img-profile-custom rounded-circle" src="{{ getFile(config('location.currencyLogo.path').optional($chargesLimit->currency)->logo) }}"></td>
														<td data-label="@lang('Status')">
															@if($chargesLimit->is_active)
																<span class="badge badge-info">@lang('Active')</span>
															@else
																<span class="badge badge-warning">@lang('Inactive')</span>
															@endif
														</td>
														<td data-label="@lang('Action')">
															<a href="{{ route('charge.edit',$chargesLimit) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i> @lang('Edit')</a>
														</td>
													</tr>
												@endforeach
												</tbody>
											</table>
										</div>
									</div>
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
	<script src="{{ asset('assets/dashboard/js/jquery.dataTables.min.js') }}"></script>
	<script src="{{ asset('assets/dashboard/js/dataTables.bootstrap4.min.js') }}"></script>
@endpush
@section('scripts')
	<script>
		'use strict'
		$(document).ready(function (){
			$('#data-table').dataTable({
                "aaSorting": [],
                "ordering": false
			});
		});
	</script>
@endsection
