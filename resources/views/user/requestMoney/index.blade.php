@extends('user.layouts.master')
@section('page_title',__('Request Money List'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Request Money List')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Request Money List')</div>
				</div>
			</div>

			<div class="container-fluid" id="container-wrapper">
				<div class="row">
					<div class="col-lg-12">
						<div class="card mb-4 card-primary shadow-sm">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
								<h6 class="m-0 font-weight-bold text-primary">@lang('Search')</h6>
							</div>
							<div class="card-body">
								<form action="{{ route('requestMoney.search') }}" method="get">
									<div class="row">
										<div class="col-md-2">
											<div class="form-group">
												<input placeholder="@lang('Sender')" name="sender"
													   value="{{ $search['sender'] ?? '' }}" type="text"
													   class="form-control form-control-sm">
											</div>
										</div>
										<div class="col-md-2">
											<div class="form-group">
												<input placeholder="@lang('Receiver')" name="receiver"
													   value="{{ $search['receiver'] ?? '' }}" type="text"
													   class="form-control form-control-sm">
											</div>
										</div>
										<div class="col-md-2">
											<div class="form-group">
												<input placeholder="@lang('E-mail')" name="email"
													   value="{{ $search['email'] ?? '' }}" type="text"
													   class="form-control form-control-sm">
											</div>
										</div>
										<div class="col-md-2">
											<div class="form-group">
												<input placeholder="@lang('Transaction ID')" name="utr"
													   value="{{ $search['utr'] ?? '' }}" type="text"
													   class="form-control form-control-sm">
											</div>
										</div>
										<div class="col-md-2">
											<div class="form-group">
												<input placeholder="@lang('Min Amount')" name="min"
													   value="{{ $search['min'] ?? '' }}" type="text"
													   class="form-control form-control-sm">
											</div>
										</div>
										<div class="col-md-2">
											<div class="form-group">
												<input placeholder="@lang('Maximum Amount')" name="max"
													   value="{{ $search['max'] ?? '' }}" type="text"
													   class="form-control form-control-sm">
											</div>
										</div>
										<div class="col-md-2">
											<div class="form-group">
												<input placeholder="@lang('Transaction Date')" name="created_at" id="created_at"
													   value="{{ $search['created_at'] ?? '' }}" type="date"
													   class="form-control form-control-sm" autocomplete="off">
											</div>
										</div>
										<div class="col-md-2">
											<div class="form-group search-currency-dropdown">
												<select name="currency_id" class="form-control form-control-sm">
													<option value="">@lang('All Currency')</option>
													@foreach($currencies as $key => $currency)
														<option value="{{ $currency->id }}" {{ isset($search['currency_id']) && $search['currency_id'] == $currency->id ? 'selected' : '' }}> {{ __($currency->code) }}
															- {{ __($currency->name) }} </option>
													@endforeach
												</select>
											</div>
										</div>
										<div class="col-md-2">
											<div class="form-group search-currency-dropdown">
												<select name="type" class="form-control form-control-sm">
													<option value="">@lang('All Type')</option>
													<option value="sent" {{ isset($search['type']) && $search['type'] == 'sent' ? 'selected' : '' }}>@lang('Sent')</option>
													<option value="received" {{ isset($search['type']) && $search['type'] == 'received' ? 'selected' : '' }}>@lang('Received')</option>
												</select>
											</div>
										</div>
										<div class="col-md-2">
											<div class="form-group">
												<button type="submit"
														class="btn btn-primary btn-sm btn-block">@lang('Search')</button>
											</div>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<div class="card mb-4 card-primary shadow">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
								<h6 class="m-0 font-weight-bold text-primary">@lang('Request Money')</h6>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table class="table table-striped table-hover align-items-center table-borderless">
										<thead class="thead-light">
										<tr>
											<th>@lang('SL')</th>
											<th>@lang('Request To')</th>
											<th>@lang('Sender E-Mail')</th>
											<th>@lang('Transaction ID')</th>
											<th>@lang('Amount')</th>
											<th>@lang('Status')</th>
											<th>@lang('Created time')</th>
											<th>@lang('Action')</th>
										</tr>
										</thead>
										<tbody>
										@forelse($requestMoney as $key => $value)
											<tr>
												<td data-label="@lang('SL')">{{ loopIndex($requestMoney) + $key }}</td>
												<td data-label="@lang('Request To')">{{ __(optional($value->receiver)->name) ?? __('N/A') }}</td>
												<td data-label="@lang('Sender E-Mail')">{{ __($value->email) }}</td>
												<td data-label="@lang('Transaction ID')">{{ __($value->utr) }}</td>
												<td data-label="@lang('Amount')">{{ (getAmount($value->amount)).' '.__(optional($value->currency)->code) }}</td>
												<td data-label="@lang('Status')">
													@if($value->status == 1)
														<span class="badge badge-info">@lang('Success')</span>
													@elseif($value->status == 2)
														<span class="badge badge-danger">@lang('Canceled')</span>
													@else
														<span class="badge badge-warning">@lang('Pending')</span>
													@endif
												</td>
												<td data-label="@lang('Created time')"> {{ dateTime($value->created_at)}} </td>
												<td data-label="@lang('Action')">
													@if($value->receiver_id == Auth::id())
														<a href="{{ route('requestMoney.check',[$value->utr]) }}"
														   class="btn btn-sm btn-primary">@lang('Yes')</a>
													@endif
													@if($value->status == 0)
														<a href="{{ route('requestMoney.cancel',[$value->utr]) }}"
														   class="btn btn-sm btn-danger cancelButton" data-toggle="modal"
														   data-target="#cancelModal">@lang('Cancel')</a>
													@endif
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
									{{ $requestMoney->links() }}
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>

	<!----- Cancel Modal ----->
	<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
	aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title text-danger" id="exampleModalLabel">@lang('Confirmation !')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">@lang('&times;')</span>
					</button>
				</div>
				<div class="modal-body text-center">
					<p>@lang('Do you want to cancel this request?')</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-primary" data-dismiss="modal">@lang('Close')</button>
					<a href="" class="btn btn-primary" id="cancelButton">@lang('Confirmed')</a>
				</div>
			</div>
		</div>
	</div>

@endsection

@section('scripts')
    <script>
        'use strict';
        $(document).ready(function () {
            $(document).on('click', '.cancelButton', function (e) {
                let cancelUrl = $(this).attr('href');
                $('#cancelButton').attr('href', cancelUrl)
            });
        });
    </script>
@endsection
