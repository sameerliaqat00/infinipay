@extends('user.layouts.master')
@section('page_title',__('Invoice Lists'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Invoice Lists')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Invoice Lists')</div>
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
									<form action="{{ route('invoice.search') }}" method="get">
										@include('user.invoice.searchForm')
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
									<h6 class="m-0 font-weight-bold text-primary">@lang('Invoice lists')</h6>
								</div>
								<div class="card-body">
									<div class="table-responsive">
										<table
											class="table table-striped table-hover align-items-center table-borderless">
											<thead class="thead-light">
											<tr>
												<th>@lang('Sender')</th>
												<th>@lang('Receiver')</th>
												<th>@lang('Receiver E-Mail')</th>
												<th>@lang('Currency')</th>
												<th>@lang('Transaction ID')</th>
												<th>@lang('Requested Amount')</th>
												<th>@lang('Type')</th>
												<th>@lang('Status')</th>
												<th>@lang('Created time')</th>
												<th>@lang('Action')</th>
											</tr>
											</thead>
											<tbody>
											@forelse($invoices as $key => $value)
												<tr>
													<td data-label="@lang('Sender')">{{ __(optional($value->sender)->name) ?? __('N/A') }}</td>
													<td data-label="@lang('Receiver')">{{ __(optional($value->receiver)->name) ?? __('N/A') }}</td>
													<td data-label="@lang('Receiver E-Mail')">{{ __($value->customer_email) }}</td>
													<td data-label="@lang('Currency')">{{ __(optional($value->currency)->name) ?? __('N/A') }}</td>
													<td data-label="@lang('Transaction ID')">{{ __($value->has_slug) }}</td>
													<td data-label="@lang('Requested Amount')">{{ (getAmount($value->grand_total)).' '.__(optional($value->currency)->code) }}</td>
													<td data-label="@lang('Type')">
														@if($value->sender_id == Auth::id())
															<span class="badge badge-info">@lang('Sent')</span>
														@else
															<span class="badge badge-success">@lang('Received')</span>
														@endif
													</td>
													<td data-label="@lang('Status')">
														@if($value->status == null)
															<span class="badge badge-warning">@lang('Unpaid')</span>
														@elseif($value->status == 'paid')
															<span
																class="badge badge-success">@lang('Paid')</span>
														@elseif($value->status == 'rejected')
															<span
																class="badge badge-danger">@lang('Rejected')</span>
														@endif
													</td>
													<td data-label="@lang('Created time')"> {{ dateTime($value->created_at,'d/m/Y')}} </td>
													<td data-label="@lang('Action')">
														@if($value->status != 'paid' && $value->status != 'rejected')
															<a href="javascript:void(0)"
															   data-target="#reminder" data-toggle="modal"
															   class="btn btn-sm btn-outline-info reminder"
															   data-id="{{$value->id}}"
															   title="reminder"><i
																	class="fas fa-clock"></i></a>
														@endif
														<a href="{{ route('invoice.view', $value->id) }}"
														   class="btn btn-sm btn-outline-primary" title="view"><i
																class="fa fa-eye"></i></a>
														<a href="{{ route('downloadPdf', $value->id) }}"
														   class="btn btn-sm btn-outline-success" title="download"
														   target="_blank"><i
																class="fa fa-download"></i></a>
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
										{{ $invoices->links() }}
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
	<div class="modal fade" id="reminder" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
		 aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<form action="{{route('invoiceReminder')}}" method="post">
					@csrf
					<input type="hidden" name="invoiceId" value="" class="invId">
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel">@lang('Confirmation !')</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">@lang('&times;')</span>
						</button>
					</div>
					<div class="modal-body text-center">
						<p>@lang('Do you want to send reminder for this invoice?')</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-outline-primary"
								data-dismiss="modal">@lang('Close')</button>
						<button type="submit" class="btn btn-primary" id="cancelButton">@lang('Confirmed')</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
@section('scripts')
	<script>
		"use strict";
		$(document).on('click', '.reminder', function () {
			$('.invId').val($(this).data('id'));
		})
	</script>
@endsection
