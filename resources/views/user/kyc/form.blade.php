@extends('user.layouts.master')
@section('page_title',__('KYC Form'))

@section('content')
	<div class="main-content">
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
			<div class="row ">
				<div class="col-md-12">
					<div class="bd-callout bd-callout-primary mx-2">
						<i class="fa-3x fas fa-info-circle text-primary"></i> @lang('Please submit your kyc information')
					</div>
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
								</div>
								<div class="card-body">
									<form action="{{route('user.kycStore')}}" method="post"
										  enctype="multipart/form-data"
										  class="form-row text-left preview-form">
										@csrf
										@if($kyc->input_form)
											@foreach($kyc->input_form as $k => $v)
												@if($v->type == "text")
													<div class="col-md-12">
														<div class="form-group mt-2">
															<label><strong>{{trans($v->field_level)}} @if($v->validation == 'required')
																		<span class="text-danger">*</span>
																	@endif</strong></label>
															<input type="text" name="{{$k}}"
																   class="form-control"
																   @if($v->validation == "required") required @endif>
															@if ($errors->has($k))
																<span
																	class="text-danger">{{ trans($errors->first($k)) }}</span>
															@endif
														</div>
													</div>
												@elseif($v->type == "textarea")
													<div class="col-md-12">
														<div class="form-group">
															<label><strong>{{trans($v->field_level)}} @if($v->validation == 'required')
																		<span class="text-danger">*</span>
																	@endif
																</strong></label>
															<textarea name="{{$k}}" class="form-control" rows="3"
																	  @if($v->validation == "required") required @endif></textarea>
															@if ($errors->has($k))
																<span
																	class="text-danger">{{ trans($errors->first($k)) }}</span>
															@endif
														</div>
													</div>
												@elseif($v->type == "file")

													<div class="col-md-12 my-3">
														<label><strong>{{trans($v->field_level)}} @if($v->validation == 'required')
																	<span class="text-danger">*</span>
																@endif
															</strong></label>

														<div class="input-box mt-2">
															<div class="fileinput fileinput-new "
																 data-provides="fileinput">
																<div class="fileinput-new thumbnail withdraw-thumbnail"
																	 data-trigger="fileinput">
																	<img class="w-150px"
																		 src="{{ getFile(config('location.default2')) }}"
																		 alt="...">
																</div>
																<div
																	class="fileinput-preview fileinput-exists thumbnail wh-200-150"></div>
																<div class="img-input-div">
                                                                <span class="btn btn-info btn-file">
                                                                    <span
																		class="fileinput-new"> @lang('Select') {{$v->field_level}}</span>
                                                                    <span
																		class="fileinput-exists"> @lang('Change')</span>
                                                                    <input type="file" name="{{$k}}" accept="image/*"
																		   @if($v->validation == "required") required @endif>
                                                                </span>
																	<a href="#" class="btn btn-danger fileinput-exists"
																	   data-dismiss="fileinput"> @lang('Remove')</a>
																</div>

															</div>
															@if ($errors->has($k))
																<br>
																<span
																	class="text-danger">{{ __($errors->first($k)) }}</span>
															@endif
														</div>
													</div>
												@endif
											@endforeach
										@endif

										<div class="col-md-12">
											<div class="mt-4">
												<button type="submit" class="btn btn-primary">
													<span>@lang('Confirm Now')</span>
												</button>
											</div>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
@endsection
@push('extra_styles')
	<link rel="stylesheet" href="{{ asset('assets/dashboard/css/bootstrap-fileinput.css') }}">
@endpush
@section('scripts')
	<script src="{{ asset('assets/dashboard/js/bootstrap-fileinput.js') }}"></script>
@endsection
