@extends('admin.layouts.master')
@section('page_title', __('Request Details'))
@section('content')
	<div>
		<div class="main-content">
			<section class="section">
				<div class="section-header">
					<h1>@lang('Request Details')</h1>
					<div class="section-header-breadcrumb">
						<div class="breadcrumb-item active">
							<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
						</div>
						<div class="breadcrumb-item">@lang('Request Details')</div>
					</div>
				</div>
				<div class="section-body">
					<div class="row mt-sm-4">
						<div class="col-12 col-md-12 col-lg-12">
							<div class="container-fluid" id="container-wrapper">
								@if($cardOrderDetail->last_error)
									<div
										class="media align-items-center d-flex justify-content-between alert alert-warn mb-4">
										<div>
											<i class="fas fa-exclamation-triangle"></i> @lang('Last Api error message:-') {{$cardOrderDetail->last_error}}
										</div>
									</div>
								@endif
								<div class="row justify-content-md-center">
									<div class="col-lg-6 col-md-6">
										<div class="card mb-4 card-primary shadow">
											<div
												class="card-header py-3 d-flex flex-wrap flex-row align-items-center justify-content-between">
												<h6 class="m-0 font-weight-bold text-primary">@lang('Request For')
													- {{$cardOrderDetail->cardMethod->name}} @lang('Virtual Card')</h6>
												<div class="d-flex justify-content-end">
													@if($cardOrderDetail->status == 2 || $cardOrderDetail->status == 3)
														<a href="javascript:void(0)" data-target="#rejectReason"
														   data-toggle="modal"
														   class="btn btn-dark mr-2">@lang('Rejected Reason')</a>
													@endif
													<a href="{{route('admin.virtual.cardOrder')}}"
													   class="btn btn-primary">@lang('Back')</a>
												</div>
											</div>
											<div class="card-body">
												@forelse($cardOrderDetail->form_input as $k => $v)
													@if ($v->type == 'text')
														<li class="list-group-item d-flex justify-content-between">
															<span> {{ @$v->field_level }} :</span>
															<span
																class="text-info">{{ @$v->field_value }}</span>
														</li>
													@elseif($v->type == 'textarea')
														<li class="list-group-item d-flex justify-content-between">
															<span> {{ @$v->field_level }} :</span>
															<span
																class="text-info">{{ @$v->field_value }}</span>
														</li>
													@elseif($v->type == 'file')
														<li class="list-group-item d-flex justify-content-between">
															<span> {{ @$v->field_level }} :</span>
														</li>
													@elseif($v->type == 'date')
														<li class="list-group-item d-flex justify-content-between">
															<span> {{ @$v->field_level}} :</span>
															<span
																class="text-info">{{ @$v->field_value }}</span>
														</li>
													@endif
												@empty
												@endforelse
											</div>
										</div>
									</div>
									<div class="col-md-4">
										<div class="card mb-4 card-primary shadow">
											<div
												class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
												<h6 class="m-0 font-weight-bold text-primary">@lang('Requester Details')</h6>
												<?php echo $cardOrderDetail->statusMessage; ?>
											</div>
											<div class="card-body">
												<li class="list-group-item d-flex justify-content-between">
													<span>{{ __('Full Name') }}</span>
													<a href="{{route('user.edit',$cardOrderDetail->user_id)}}"
													   class="decoration__none"><span
															class="text-info">{{optional($cardOrderDetail->user)->name}}</span></a>
												</li>
												<li class="list-group-item d-flex justify-content-between">
													<span>{{ __('Method') }}</span>
													<span
														class="text-info">{{optional($cardOrderDetail->cardMethod)->name}}</span>
												</li>
												<li class="list-group-item d-flex justify-content-between">
													<span>{{ __('Currency') }}</span>
													<span
														class="text-info">{{$cardOrderDetail->currency}}</span>
												</li>
												<div class="d-flex justify-content-between mt-4">
													<button data-target="#approveModal" data-toggle="modal"
															class="btn btn-success btn-block mr-2 mt-2">@lang('Approved')</button>
													@if($cardOrderDetail->status != 2)
														<button class="btn btn-danger btn-block"
																data-target="#rejectModal"
																data-toggle="modal">@lang('Rejected')</button>
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

	{{--	Virtual Card Approve--}}
	<div id="approveModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
		 aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title text-dark font-weight-bold"
						id="primary-header-modalLabel">@lang('Approve Confirmation')</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				<form action="{{route('admin.virtual.cardOrderApprove',$cardOrderDetail->id)}}" method="get">
					<div class="modal-body">
						<p>@lang('Are you want to approve this card request ?')</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
						<button type="submit" class="btn btn-primary change-yes">@lang('Yes')</button>
					</div>
				</form>
			</div>
		</div>
	</div>


	<div id="rejectModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
		 aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title text-dark font-weight-bold"
						id="primary-header-modalLabel">@lang('Reject Confirmation')</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				<form action="{{route('admin.virtual.cardOrderRejected',$cardOrderDetail->id)}}" method="post">
					@csrf
					<div class="modal-body">
						<div class="row mx-1">
							<label>@lang('Allow to user resubmitted ?')</label>
							<div class="selectgroup w-100">
								<label class="selectgroup-item">
									<input type="radio" name="resubmitted"
										   value="1"
										   class="selectgroup-input" checked>
									<span class="selectgroup-button">@lang('Yes')</span>
								</label>
								<label class="selectgroup-item">
									<input type="radio" name="resubmitted"
										   value="0"
										   class="selectgroup-input">
									<span class="selectgroup-button">@lang('No')</span>
								</label>
							</div>
						</div>
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
	@if($cardOrderDetail->status == 2 || $cardOrderDetail->status == 3)
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
								<textarea class="form-control" readonly>{{$cardOrderDetail->reason}}</textarea>
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

