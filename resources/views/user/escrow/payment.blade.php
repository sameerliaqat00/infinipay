@extends('user.layouts.master')
@section('page_title',__('Escrow Payment'))

@section('content')
<div class="main-content">
	<section class="section">
		<div class="section-header">
			<h1>@lang('Escrow Payment')</h1>
			<div class="section-header-breadcrumb">
				<div class="breadcrumb-item active">
					<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
				</div>
				<div class="breadcrumb-item">@lang('Escrow Payment')</div>
			</div>
		</div>

		<div class="row mb-3">
			<div class="container-fluid" id="container-wrapper">
				<div class="row justify-content-md-center">
					<div class="col-lg-12">
						<div class="card mb-4 card-primary shadow">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-center">
								<h6 class="m-0 font-weight-bold text-primary">@lang('Escrow Payment')</h6>
							</div>
							<div class="card-body">
								<ul class="list-group">
									<li class="list-group-item list-group-item-action d-flex justify-content-between">
										<span>@lang('Payable amount')</span><span>{{ (getAmount($escrow->transfer_amount)) .' '. __(optional($escrow->currency)->code) }}</span>
									</li>
									<li class="list-group-item list-group-item-action d-flex justify-content-between">
										<span>@lang('Percentage charge') ({{ (getAmount($escrow->percentage)) }}@lang('%')
											)</span>
										<span>{{ (getAmount($escrow->charge_percentage)) .' '. __(optional($escrow->currency)->code) }}</span>
									</li>
									<li class="list-group-item list-group-item-action d-flex justify-content-between">
										<span>@lang('Fixed charge')</span>
										<span>{{ (getAmount($escrow->charge_fixed)) .' '. __(optional($escrow->currency)->code) }}</span>
									</li>
									<li class="list-group-item list-group-item-action d-flex justify-content-between">
										<span>@lang('Total charge')</span>
										<span>{{ (getAmount($escrow->charge)) .' '. __(optional($escrow->currency)->code) }}</span>
									</li>
									<li class="list-group-item list-group-item-action d-flex justify-content-between">
										<span>@lang('Receivable Amount')</span>
										<span>{{ (getAmount($escrow->received_amount)) .' '. __(optional($escrow->currency)->code) }}</span>
									</li>
									<li class="list-group-item list-group-item-action d-flex justify-content-between">
										<span>@lang('Note')</span><span> {{ __($escrow->note) }} </span>
									</li>
									<li class="list-group-item list-group-item-action d-flex justify-content-between">
										<span>@lang('Status')</span>
										<span>
											@if($escrow->status == 1)
												<span class="badge badge-info text-white">@lang('Generated')</span>
											@elseif($escrow->status == 2)
												<span class="badge badge-success text-white">@lang('Deposited')</span>
											@elseif($escrow->status == 3)
												<span class="badge badge-success text-white">@lang('Request for payment')</span>
											@elseif($escrow->status == 4)
												<span class="badge badge-success text-white">@lang('Payment Disbursed')</span>
											@elseif($escrow->status == 5)
												<span class="badge badge-success text-white">@lang('Canceled')</span>
											@elseif($escrow->status == 6)
												<span class="badge badge-success text-white">@lang('Dispute')</span>
											@elseif($escrow->status == 0)
												<span class="badge badge-warning text-white">@lang('Pending')</span>
											@else
												<span class="badge badge-warning text-white">@lang('N/A')</span>
											@endif
											</span>
									</li>
								</ul>
								<div class="row mt-3">
									@if($escrow->receiver_id == Auth::id() && $escrow->status == 1)
										<div class="col-md-6">
											<button type="submit" class="btn btn-outline-success btn-sm btn-block confirmButton"
													name="status" value="2" data-toggle="modal" data-target="#confirmModal" data-text="@lang('accept this request?')">
												<i class="fa fa-check-circle"></i> @lang('Accept')
											</button>
										</div>
										<div class="col-md-6">
											<button type="submit" class="btn btn-outline-danger btn-sm btn-block confirmButton"
													name="status" value="5" data-toggle="modal" data-target="#confirmModal" data-text="@lang('cancel this request?')">
												<i class="fa fa-times-circle"></i> @lang('Cancel')
											</button>
										</div>
									@elseif($escrow->sender_id == Auth::id() && $escrow->status == 2)
										<div class="col-md-12">
											<button type="submit" class="btn btn-outline-info btn-sm btn-block confirmButton"
													name="status" value="3" data-toggle="modal" data-target="#confirmModal" data-text="@lang('request for payment?')">
												<i class="fa fa-check-circle"></i> @lang('Request Payment')
											</button>
										</div>
									@elseif($escrow->receiver_id == Auth::id() && $escrow->status == 3)
										<div class="col-md-6">
											<button type="submit" class="btn btn-outline-success btn-sm btn-block confirmButton"
													name="status" value="4" data-toggle="modal" data-target="#confirmModal" data-text="@lang('request for payment disbursed?')">
												<i class="fa fa-check-circle"></i> @lang('Payment disbursed')
											</button>
										</div>
										<div class="col-md-6">
											<a href="{{ route('user.dispute.view', $escrow->utr) }}"
											   class="btn btn-outline-danger btn-sm btn-block">
												<i class="fa fa-times-circle"></i> @lang('Dispute')</a>
										</div>
									@elseif($escrow->receiver_id == Auth::id() && $escrow->status == 6)
										<div class="col-md-12">
											<a href="{{ route('user.dispute.view', $escrow->utr) }}"
											   class="btn btn-outline-info btn-sm btn-block">
												<i class="fa fa-eye"></i> @lang('View Dispute Details')</a>
										</div>
									@endif
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</div>

<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
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
				<p>@lang('Are you sure you want to') <span id="action-text"></span></p>
			</div>
			<div class="modal-footer">
				<form action="{{ route('escrow.paymentView', $escrow->utr) }}" method="post">
					@csrf
					<button type="button" class="btn btn-outline-primary"
							data-dismiss="modal">@lang('Close')</button>
					<button type="submit" class="btn btn-primary" id="confirmButton" name="status" value="6">
						@lang('Confirmed')
					</button>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection

@section('scripts')
    <script>
        'use strict';
        $(document).ready(function () {
            $(document).on('click', '.confirmButton', function (e) {
                let statusValue = $(this).val();
                let text = $(this).data('text');
                $('#confirmButton').val(statusValue);
                $('#action-text').text(text);
            })
        });
    </script>
@endsection
