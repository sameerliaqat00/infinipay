@extends('user.layouts.master')
@section('page_title',__('Banks & Cards'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Banks & Cards')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Banks & Cards')</div>
				</div>
			</div>
			<!------ alert ------>
			<div class="row ">
				<div class="col-md-12">
					<div class="bd-callout bd-callout-primary mx-2">
						<i class="fa-3x fas fa-info-circle text-primary"></i> @lang(optional($template->description)->short_description)
					</div>
				</div>
			</div>
			@if(!empty($cardOrder))
				@if($cardOrder->status == 0 || $cardOrder->status == 3)
					<div class="media align-items-center d-flex justify-content-between alert alert-warn mb-4">
						<div><i
								class="fas fa-exclamation-triangle"></i> @lang('Your virtual card request is pending.')
						</div>
					</div>
				@endif
				@if($cardOrder->status == 2)
					<div class="media align-items-center d-flex justify-content-between alert alert-warn mb-4">
						<div><i
								class="fas fa-exclamation-triangle"></i> @lang('Your virtual card request is rejected by authority.')
						</div>
						<div class="media align-items-center d-flex justify-content-between">
							<a href="javascript:void(0)" data-target="#rejectReason" data-toggle="modal"
							   class="btn btn-dark mr-2">@lang('Reason')</a>
							@if($cardOrder->resubmitted == 1)
								<a href="{{route('user.virtual.card.orderReSubmit')}}"
								   class="btn btn-primary">@lang('Resubmit Now')</a>
							@endif
						</div>
					</div>
				@endif
				@if($cardOrder->status == 4)
					<div class="media align-items-center d-flex justify-content-between alert alert-warn mb-4">
						<div><i
								class="fas fa-exclamation-triangle"></i> @lang('Your virtual card request is generated please make it complete.')
						</div>
						<div class="media align-items-center d-flex justify-content-between">
							<a href="{{route('order.confirm',$cardOrder->id)}}"
							   class="btn btn-primary">@lang('Confirm')</a>
						</div>
					</div>
				@endif
			@endif
			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row justify-content-md-center">
						@if(count($approveCards)>0)
							@foreach($approveCards as $card)
								<div class="col-md-4 p-2">
									<div class="bank-card-box">
										<div class="top-area">
											<div class="icon">
												<i class="fas fa-credit-card"></i>
											</div>
											<div class="text">
												<h5>{{$card->card_number}} | <span>{{$card->currency}}</span></h5>
												<span>{{$card->name_on_card}}</span>
											</div>
											@if($card->status == 5)
												<p class="ml-5 text-danger">@lang('Requested Block')</p>
											@endif

											<div class="setting-area">
												<div class="dropdown sidebar-dropdown-items">
													<button class="dropdown-toggle" type="button" data-toggle="dropdown"
															aria-expanded="false">
														<i class="fas fa-cog"></i>
													</button>
													<div class="dropdown-menu">
														@if($card->status != 9)
															<a class="dropdown-item"
															   href="{{route('fund.initialize',['card',$card->id])}}">@lang('Add Fund')</a>
															@if($card->status != 5 && $card->status != 6)
																<a class="dropdown-item blockRqst"
																   data-target="#blockRqst"
																   data-toggle="modal"
																   data-route="{{route('user.virtual.cardBlock',$card->id)}}"
																   href="javascript:void(0)">@lang('Block Card')</a>
															@endif
														@endif
														<a class="dropdown-item"
														   href="{{route('user.virtual.cardTransaction',$card->card_Id)}}">@lang('Transaction')</a>
													</div>
												</div>
											</div>
										</div>
										<div class="bottom-area d-flex justify-content-between align-items-end">
											<div>
												<span
													class="badge {{($card->is_active == 'Active') ? 'badge-success':'badge-danger'}} mb-3">{{$card->is_active}}</span>
												<p class="mb-0">@lang('Valid Thru:') {{\Carbon\Carbon::parse($card->expiry_date)->format('m/y')}}</p>
											</div>
											<div>
												<p class="mb-1">@lang('CVV:') {{$card->cvv}}</p>
												<h4 class="balance mb-1">{{getAmount($card->balance,2)}} {{$card->currency}}</h4>
											</div>
										</div>
									</div>

								</div>
							@endforeach
						@endif
						<div class="col-md-4 p-2">
							<a href="{{route('user.virtual.card.order')}}" class="decoration__none">
								<div class="bank-card-box">
									<div class="top-area">
										<div class="icon">
											<i class="fas fa-credit-card"></i>
										</div>
										<div class="text">
											<div class="d-flex justify-content-start">
												<h5 class="mr-2">@lang('Request a Card')</h5>
												@if($orderLock == 'true')
													<i class="fa fa-lock"></i>
												@endif
											</div>
											<span
												class="text-danger">@lang('Per Card Request Charge') {{basicControl()->v_card_charge}} {{config('basic.base_currency_code')}}</span>
										</div>
									</div>
								</div>
							</a>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
	@if(!empty($cardOrder))
		@if($cardOrder->status == 2)
			<div id="rejectReason" class="modal fade" tabindex="-1" role="dialog"
				 aria-labelledby="primary-header-modalLabel"
				 aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title text-dark font-weight-bold"
								id="primary-header-modalLabel">@lang('Rejected Reason ')</h4>
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						</div>
						<div class="modal-body">
							<div class="row mt-2">
								<div class="col-md-12">
									<lable>@lang('Reasons')</lable>
									<textarea class="form-control" readonly>{{$cardOrder->reason}}</textarea>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
						</div>
					</div>
				</div>
			</div>
		@endif
	@endif

	{{--	Block Request Modal--}}
	<div id="blockRqst" class="modal fade" tabindex="-1" role="dialog"
		 aria-labelledby="primary-header-modalLabel"
		 aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title text-dark font-weight-bold"
						id="primary-header-modalLabel">@lang('Block Confirmation')</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				<form action="" method="post" class="blockForm">
					@csrf
					<div class="modal-body">
						<p>@lang('Are You sure to send block request for this card ?')</p>
						<div class="row">
							<div class="col-md-12">
								<label>@lang('Reason For Block')</label>
								<textarea class="form-control" name="reason"></textarea>
								@error('reason')
								<span class="text-danger">{{$message}}</span>
								@enderror
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
						<button type="submit" class="btn btn-primary">@lang('Yes')</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{--	Add Fund Request Modal--}}
	<div id="fundRqst" class="modal fade" tabindex="-1" role="dialog"
		 aria-labelledby="primary-header-modalLabel"
		 aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title text-dark font-weight-bold"
						id="primary-header-modalLabel">@lang('Add Fund Confirmation')</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				<form action="{{route('fund.initialize')}}" method="post" class="blockForm">
					@csrf
					<div class="modal-body">
						<p>@lang('Are You sure to send fund request for this card ?')</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
						<button type="submit" class="btn btn-primary">@lang('Yes')</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection

@push('extra_styles')

@endpush
@section('scripts')
	<script>
		'use strict';
		$(document).on('click', '.blockRqst', function () {
			var route = $(this).data('route');
			$('.blockForm').attr('action', route);
		})
	</script>
@endsection
