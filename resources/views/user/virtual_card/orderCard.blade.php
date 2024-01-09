@extends('user.layouts.master')
@section('page_title',__('Request New Card'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Request New Card')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Request New Card')</div>
				</div>
			</div>
			<!------ alert ------>
			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row justify-content-md-center">
						<div class="col-md-6">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">{{$virtualCardMethod->name}} @lang('Card')</h6>
								</div>
								<div class="card-body">
									<label class="text-dark font-weight-bold mb-4">{{$virtualCardMethod->info_box}}</label>
									<form action="{{route('user.virtual.card.orderSubmit')}}" method="post"
										  enctype="multipart/form-data">
										@csrf
										@if($virtualCardMethod)
											<div class="row mx-1">
												<label>@lang('Card Currency')</label>
												<div class="selectgroup w-100">
													@foreach($virtualCardMethod->currency as $singleCurrency)
														<label class="selectgroup-item">
															<input type="radio" name="currency"
																   value="{{$singleCurrency}}"
																   class="selectgroup-input" checked>
															<span class="selectgroup-button">{{$singleCurrency}}</span>
														</label>
													@endforeach
												</div>
											</div>
										@endif
										@if(isset($virtualCardMethod->form_field))
											<div class="row">
												@forelse($virtualCardMethod->form_field as $k => $v)
													<div class="col-md-6 mt-3">
														@if ($v->type == 'text')
															<div class="input-box">
																<label
																	for="exampleFormControlInput1"
																	class="form-label">{{ trans($v->field_level) }}
																	@if ($v->validation == 'required')
																		<span class="text-danger">*</span>
																	@endif
																</label>
																<input name="{{ $k }}" type="text" class="form-control"
																	   value="{{old($k)}}"
																	   placeholder="{{ trans($v->field_place) }}"
																@if ($v->validation == 'required')  @endif />

																@error($k)
																<span
																	class="text-danger">{{ $message  }}</span>
																@enderror
															</div>
														@elseif($v->type == 'textarea')
															<div class="input-box form-group">
																<label
																	for="exampleFormControlInput1"
																	class="form-label">{{ trans($v->field_level) }}

																	@if ($v->validation == 'required')
																		<span class="text-danger">*</span>
																	@endif
																</label>

																<textarea name="{{ $k }}" class="form-control"
                                                                    @if ($v->validation == 'required')  @endif>{{old($k)}}</textarea>
																@if ($errors->has($k))
																	<span
																		class="text-danger">{{ trans($errors->first($k)) }}</span>
																@endif
															</div>
														@elseif($v->type == 'file')
															<div class="input-box form-group">
																<label
																	for="exampleFormControlInput1"
																	class="form-label">{{ trans($v->field_level) }}

																	@if ($v->validation == 'required')
																		<span class="text-danger">*</span>
																	@endif
																</label>

																<input name="{{ $k }}" type="file" class="form-control"
																	   placeholder="{{ trans($v->field_place) }}"
																@if ($v->validation == 'required')   @endif />

																@if ($errors->has($k))
																	<span
																		class="text-danger">{{ trans($errors->first($k)) }}</span>
																@endif
															</div>
														@elseif($v->type == 'date')
															<div class="input-box">
																<label
																	for="exampleFormControlInput1"
																	class="form-label">{{ trans($v->field_level) }}
																	@if ($v->validation == 'required')
																		<span class="text-danger">*</span>
																	@endif
																</label>
																<input name="{{ $k }}" type="date" class="form-control"
																	   value="{{old($k)}}"
																	   placeholder="{{ trans($v->field_place) }}"
																@if ($v->validation == 'required')  @endif />

																@error($k)
																<span
																	class="text-danger">{{ $message  }}</span>
																@enderror
															</div>
														@endif
													</div>
												@empty
												@endforelse
											</div>
										@endif
										<button type="submit"
												class="btn btn-primary btn-block mt-4">@lang('Apply')</button>
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

