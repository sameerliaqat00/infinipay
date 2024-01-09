@extends('admin.layouts.master')
@section('page_title', __('Card Providers'))

@push('extra_styles')
	<link href="{{ asset('assets/dashboard/css/jquery-ui.min.css') }}" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="{{ asset('assets/dashboard/css/dataTables.bootstrap4.min.css') }}">
@endpush
@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Card Providers')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Card Providers')</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row">
						<div class="col-lg-12">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Card Providers')</h6>
								</div>
								<div class="card-body">
									<div class="table-responsive">
										<table class="table align-items-center table-bordered"
											   id="payment-method-table">
											<thead class="thead-light">
											<tr>
												<th col="scope">@lang('Name')</th>
												<th col="scope">@lang('Status')</th>
												<th col="scope">@lang('Action')</th>
											</tr>
											</thead>
											<tbody id="sortable">
											@if(count($virtualCardMethods) > 0)
												@foreach($virtualCardMethods as $method)
													<tr data-code="{{ $method->code }}">
														<td data-label="@lang('Name')">{{ $method->name }} </td>
														<td data-label="@lang('Status')">
															{!!  $method->status == 1 ? '<span class="badge badge-success badge-sm">'.__('Active').'</span>' : '<span class="badge badge-danger badge-sm">'.__('Inactive').'</span>' !!}
														</td>
														<td data-label="@lang('Action')">
															<a href="{{ route('admin.virtual.cardEdit', $method->id) }}"
															   class="btn btn-sm btn-outline-primary btn-circle"
															   data-toggle="tooltip"
															   data-placement="top"
															   data-original-title="@lang('Edit this Methods info')">
																<i class="fa fa-edit"></i> @lang('Edit')
															</a>
															@if($method->status == 0)
																<a href="javascript:void(0)"
																   class="btn btn-sm btn-outline-success btn-circle changeBtn"
																   data-target="#statusChange"
																   data-toggle="modal"
																   data-route="{{ route('admin.virtual.cardStatusCng', $method->id) }}"
																   data-toggle="tooltip"
																   data-placement="top"
																   data-original-title="@lang('Active this method')">
																	<i class="fas fa-check-circle"></i> @lang('Active')
																</a>
															@endif
														</td>
													</tr>
												@endforeach
											@else
												<tr>
													<td class="text-center text-danger" colspan="8">
														@lang('No Data Found')
													</td>
												</tr>
											@endif
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

		</section>
	</div>
	<div id="statusChange" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
		 aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title text-dark font-weight-bold"
						id="primary-header-modalLabel">@lang('Active Confirmation')</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				@csrf
				<div class="modal-body">
					<p>@lang('Are you want to active this method ?')</p>
				</div>
				<input type="hidden" class="stage" value="">
				<div class="modal-footer">
					<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
					<form action="" method="post" class="actionChange">
						@csrf
						<button type="submit" class="btn btn-primary">@lang('Yes')</button>
					</form>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('extra_scripts')
	<script src="{{ asset('assets/dashboard/js/jquery-ui.min.js') }}"></script>
	<script src="{{ asset('assets/dashboard/js/jquery.dataTables.min.js') }}"></script>
	<script src="{{ asset('assets/dashboard/js/dataTables.bootstrap4.min.js') }}"></script>
@endpush
@section('scripts')
	<script>
		'use strict'
		$(document).on('click', '.changeBtn', function () {
			var route = $(this).data('route');
			$('.actionChange').attr('action', route);
		})
	</script>
@endsection
