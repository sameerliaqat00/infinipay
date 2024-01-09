@extends('user.layouts.master')
@section('page_title',__('Voucher payment'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Voucher payment')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Voucher payment')</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row justify-content-md-center">
						<div class="col-lg-4">
							<div class="card mb-4 card-primary shadow">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-center">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Voucher payment')</h6>
								</div>
								<div class="card-body">
									<ul class="list-group">
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Payable amount')</span>
											<span>{{ (getAmount($voucher->transfer_amount)) }} {{ __(optional($voucher->currency)->code) }}</span>
										</li>
										@if($voucher->sender_id == auth()->id())
											<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Percentage charge')
												({{ (getAmount($voucher->percentage)) }}@lang('%'))</span>
												<span>{{ (getAmount($voucher->charge_percentage)) }} {{ __(optional($voucher->currency)->code) }}</span>
											</li>
											<li class="list-group-item list-group-item-action d-flex justify-content-between">
												<span>@lang('Fixed charge')</span>
												<span>{{ (getAmount($voucher->charge_fixed)) }} {{ __(optional($voucher->currency)->code) }}</span>
											</li>
											<li class="list-group-item list-group-item-action d-flex justify-content-between">
												<span>@lang('Total charge')</span>
												<span>{{ (getAmount($voucher->charge)) }} {{ __(optional($voucher->currency)->code) }}</span>
											</li>
											<li class="list-group-item list-group-item-action d-flex justify-content-between">
												<span>@lang('Receivable Amount')</span>
												<span>{{ (getAmount($voucher->received_amount)) }} {{ __(optional($voucher->currency)->code) }}</span>
											</li>
										@endif
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Note')</span>
											<span> {{ __($voucher->note) }} </span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Status')</span>
											<span>
												@if($voucher->status == 1)
													<span class="badge badge-info text-white">@lang('Generated')</span>
												@elseif($voucher->status == 2)
													<span
														class="badge badge-success text-white">@lang('Deposited')</span>
												@elseif($voucher->status == 5)
													<span
														class="badge badge-success text-white">@lang('Canceled')</span>
												@elseif($voucher->status == 0)
													<span class="badge badge-warning text-white">@lang('Pending')</span>
												@else
													<span class="badge badge-warning text-white">@lang('N/A')</span>
												@endif
											</span>
										</li>
									</ul>
									<div class="row mt-3">
										@if($voucher->status == 1)
											@if($voucher->receiver_id == Auth::id())
												<div class="col-md-6">
													<button type="submit"
															class="btn btn-outline-success btn-sm btn-block confirmButton"
															name="status" value="2" data-toggle="modal"
															data-target="#confirmModal"
															data-text="@lang(' accept this request?')">
														<i class="fa fa-check-circle"></i> @lang('Accept')
													</button>
												</div>
											@endif
											<div class="col-md-6">
												<button type="submit"
														class="btn btn-outline-danger btn-sm btn-block confirmButton"
														name="status" value="5" data-toggle="modal"
														data-target="#confirmModal"
														data-text="@lang(' cancel this request?')">
													<i class="fa fa-times-circle"></i> @lang('Cancel')
												</button>
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
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body text-center">
					<p>@lang('Are you sure you want to') <span id="action-text"></span></p>
				</div>
				<div class="modal-footer">
					<form action="{{ route('voucher.paymentView', $voucher->utr) }}" method="post">
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
		$(document).on('click', '.confirmButton', function (e) {
			let statusValue = $(this).val();
			let text = $(this).data('text');
			$('#confirmButton').val(statusValue);
			$('#action-text').text(text);
		});
	</script>
@endsection
