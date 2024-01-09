@extends('admin.layouts.master')
@section('page_title', __('Card Details'))
@section('content')
	<div>
		<div class="main-content">
			<section class="section">
				<div class="section-header">
					<h1>@lang('Card Details')</h1>
					<div class="section-header-breadcrumb">
						<div class="breadcrumb-item active">
							<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
						</div>
						<div class="breadcrumb-item">@lang('Card Details')</div>
					</div>
				</div>
				<div class="section-body">
					<div class="row mt-sm-4">
						<div class="col-12 col-md-12 col-lg-12">
							<div class="container-fluid" id="container-wrapper">
								@if($cardView->last_error)
									<div
										class="media align-items-center d-flex justify-content-between alert alert-warn mb-4">
										<div>
											<i class="fas fa-exclamation-triangle"></i> @lang('Last Api error message:-') {{$cardView->last_error}}
										</div>
									</div>
								@endif
								<div class="row justify-content-md-center">
									<div class="col-lg-6 col-md-6">
										<div class="card mb-4 card-primary shadow">
											<div
												class="card-header py-3 d-flex flex-wrap flex-row align-items-center justify-content-between">
												<h6 class="m-0 font-weight-bold text-primary">{{$cardView->cardMethod->name}} @lang('Virtual Card')</h6>
												<div class="d-flex justify-content-end">
													@if($cardView->status == 5 || $cardView->status == 7 )
														<a href="javascript:void(0)" data-target="#rejectReason"
														   data-toggle="modal"
														   class="btn btn-dark mr-2">@lang('Reason')</a>
													@endif
													<a href="{{route('admin.virtual.cardList')}}"
													   class="btn btn-primary">@lang('Back')</a>
												</div>
											</div>
											<div class="card-body">
												@if($cardView->card_info)
													@forelse($cardView->card_info as $k => $v)
														<li class="list-group-item d-flex justify-content-between">
															<span>{{ __(ucfirst(str_replace('_',' ', $v->field_name))) }} :</span>
															<span
																class="text-info">{{ @$v->field_value }}</span>
														</li>
													@empty
													@endforelse
												@endif
											</div>
										</div>
									</div>
									<div class="col-md-4">
										<div class="card mb-4 card-primary shadow">
											<div
												class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
												<h6 class="m-0 font-weight-bold text-primary">@lang('User Details')</h6>
												<?php echo $cardView->statusMessage; ?>
											</div>
											<div class="card-body">
												<li class="list-group-item d-flex justify-content-between">
													<span>{{ __('Full Name') }}</span>
													<a href="{{route('user.edit',$cardView->user_id)}}"
													   class="decoration__none"><span
															class="text-info">{{optional($cardView->user)->name}}</span></a>
												</li>
												<li class="list-group-item d-flex justify-content-between">
													<span>{{ __('Method') }}</span>
													<span
														class="text-info">{{optional($cardView->cardMethod)->name}}</span>
												</li>
												<li class="list-group-item d-flex justify-content-between">
													<span>{{ __('Currency') }}</span>
													<span
														class="text-info">{{$cardView->currency}}</span>
												</li>
												<li class="list-group-item d-flex justify-content-between text-success">
													<span>{{ __('Balance') }}</span>
													<span
														class="text-success">{{$cardView->balance}}</span>
												</li>
												@if($cardView->status == 8)
													<li class="list-group-item d-flex justify-content-between text-success">
														<span>{{ __('Fund Amount') }}</span>
														<span
															class="text-success">{{$cardView->fund_amount}} {{$cardView->currency}}</span>
													</li>
													<li class="list-group-item d-flex justify-content-between text-danger">
														<span>{{ __('Add Fund Charge') }}</span>
														<span
															class="text-danger">{{$cardView->fund_charge}} {{$cardView->currency}}</span>
													</li>
												@endif
												<li class="list-group-item d-flex justify-content-between text-danger">
													<span>{{ __('Charge') }}</span>
													<span
														class="text-danger">{{$cardView->charge}} {{optional($cardView->chargeCurrency)->code}}</span>
												</li>
												<li class="list-group-item d-flex justify-content-between text-danger">
													<span>{{ __('Expiry Date') }}</span>
													<span
														class="text-danger">{{$cardView->expiry_date}}</span>
												</li>
												@if($cardView->status == 8)
													<div class="d-flex justify-content-between mt-4">
														<button data-target="#returnFund" data-toggle="modal"
																class="btn btn-info btn-block mr-2 mt-2">@lang('Return') {{$cardView->fund_amount +$cardView->fund_charge}} {{$cardView->currency}}</button>
														<button class="btn btn-success btn-block"
																data-target="#approveFund"
																data-toggle="modal">@lang('Approve') {{$cardView->fund_amount}} {{$cardView->currency}}</button>
													</div>
												@endif
												<div class="d-flex justify-content-between mt-4">
													@if($cardView->status == 7)
														<button data-target="#unblockModal" data-toggle="modal"
																class="btn btn-success btn-block mr-2 mt-2">@lang('Unblock')</button>
													@endif
													@if($cardView->status != 7)
														<button class="btn btn-danger btn-block"
																data-target="#blockModal"
																data-toggle="modal">@lang('Block')</button>
													@endif
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
	</div>
	@if($cardView->status == 8)
		{{--	Virtual Card Return Fund--}}
		<div id="returnFund" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
			 aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title text-dark font-weight-bold"
							id="primary-header-modalLabel">@lang('Return Confirmation')</h4>
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					</div>
					<form action="{{route('admin.virtual.cardFundReturn',$cardView->id)}}" method="post">
						@csrf
						<div class="modal-body">
							<p>@lang('Are you really want to return this fund ?')</p>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
							<button type="submit" class="btn btn-primary change-yes">@lang('Yes')</button>
						</div>
					</form>
				</div>
			</div>
		</div>

		{{--	Virtual Card Approve Fund--}}
		<div id="approveFund" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
			 aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title text-dark font-weight-bold"
							id="primary-header-modalLabel">@lang('Approve Confirmation')</h4>
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					</div>
					<form action="{{route('admin.virtual.cardFundApprove',$cardView->id)}}" method="post">
						@csrf
						<div class="modal-body">
							<p>@lang('Are you really want to approve this fund ?')</p>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
							<button type="submit" class="btn btn-primary change-yes">@lang('Yes')</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	@endif
	{{--	Virtual Card Unblock--}}
	<div id="unblockModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
		 aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title text-dark font-weight-bold"
						id="primary-header-modalLabel">@lang('Unblock Confirmation')</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				<form action="{{route('admin.virtual.cardUnBlock',$cardView->id)}}" method="post">
					@csrf
					<div class="modal-body">
						<p>@lang('Are you want to unblock this card ?')</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
						<button type="submit" class="btn btn-primary change-yes">@lang('Yes')</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{--	Virtual Card Block--}}
	<div id="blockModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
		 aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title text-dark font-weight-bold"
						id="primary-header-modalLabel">@lang('Block Confirmation')</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				<form action="{{route('admin.virtual.cardBlock',$cardView->id)}}" method="post">
					@csrf
					<div class="modal-body">
						<div class="row mt-2">
							<div class="col-md-12">
								<lable>@lang('Reasons')</lable>
								<textarea class="form-control" name="reason" required></textarea>
							</div>
							@error('reason')
							<span class="text-danger ml-2">{{$message}}</span>
							@enderror
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
						<button type="submit" class="btn btn-primary change-yes">@lang('Yes')</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@if($cardView->status == 5 || $cardView->status == 7)
		<div id="rejectReason" class="modal fade" tabindex="-1" role="dialog"
			 aria-labelledby="primary-header-modalLabel"
			 aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title text-dark font-weight-bold"
							id="primary-header-modalLabel">@lang('Block Reason ')</h4>
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					</div>
					<div class="modal-body">
						<div class="row mt-2">
							<div class="col-md-12">
								<lable>@lang('Reasons')</lable>
								<textarea class="form-control" readonly>{{$cardView->reason}}</textarea>
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
@endsection

