@extends('user.layouts.master')
@section('page_title', __('KYC View'))

@section('content')
	<div class="main-content" id="invoice-app" v-cloak>
		<section class="section">
			<div class="section-header">
				<h1>@lang('KYC Form')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('KYC Form')</div>
				</div>
			</div>
			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row justify-content-md-center">
						<div class="col-md-6">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('KYC Information')</h6>
									<div class="d-flex flex-row align-items-center justify-content-end">
										@if($kyc->status == 0)
											<span class="badge badge-warning">@lang('Pending')</span>
										@elseif($kyc->status == 1)
											<span class="badge badge-success">@lang('Approved')</span>
										@elseif($kyc->status == 2)
											<span class="badge badge-danger">@lang('Rejected')</span>
										@endif
										@if($kyc->status == 2 && !empty($kyc->reason))
											<a href="javascript:void(0)" title="rejected reason" class="rejectIco"
											   data-target="#reject"
											   data-toggle="modal"><i
													class="fas fa-info-circle ml-2"></i></a>
										@endif
									</div>
								</div>
								<div class="card-body">
									<form action="" method="post"
										  class="form-row text-left preview-form">
										@if($kyc->kyc_info)
											@foreach($kyc->kyc_info as $k => $v)
												@if($v->type == "text")
													<div class="col-md-12">
														<div class="form-group mt-2">
															<label>{{$v->field_name}}</label>
															<input type="text"
																   class="form-control" value="{{$v->field_value}}">
														</div>
													</div>
												@elseif($v->type == "textarea")
													<div class="col-md-12">
														<div class="form-group">
															<label>{{$v->field_name}}</label>
															<textarea class="form-control"
																	  rows="3">{{$v->field_value}}</textarea>
														</div>
													</div>
												@elseif($v->type == "file")
													<div class="col-md-12 my-3">
														<label>{{$v->field_name}}</label>

														<div class="input-box mt-2">
															<div class="fileinput fileinput-new "
																 data-provides="fileinput">
																<div class="fileinput-new thumbnail withdraw-thumbnail"
																	 data-trigger="fileinput">
																	<img class="w-340px"
																		 src="{{ getFile(config('location.kyc.path').$v->field_value) }}"
																		 alt="...">
																</div>
															</div>
														</div>
													</div>
												@endif
											@endforeach
										@endif
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
	<div id="reject" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
		 aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title text-danger" id="primary-header-modalLabel">@lang('KYC Rejected Reason')</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				@csrf
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label>@lang('Rejected Reason')</label>
								<textarea class="form-control rejectReason" readonly></textarea>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
				</div>
			</div>
		</div>
	</div>
@endsection
@section('scripts')
	<script>
		'use script'
		var rejectReason = "{{$kyc->reason}}";
		$(document).on('click', '.rejectIco', function () {
			$('.rejectReason').text(rejectReason);
		})
	</script>
@endsection
