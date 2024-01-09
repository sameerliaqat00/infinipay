@extends('user.layouts.master')
@section('page_title',__('Re-Submit'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Re-Submit')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Re-Submit')</div>
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
									<form action="{{route('user.virtual.card.orderReSubmit')}}" method="post"
										  enctype="multipart/form-data">
										@csrf
										@if($virtualCardMethod)
											<div class="row mx-4">
												<label>@lang('Card Currency')</label>
												<div class="selectgroup w-100">
													@foreach($virtualCardMethod->currency as $singleCurrency)
														<label class="selectgroup-item">
															<input type="radio" name="currency"
																   value="{{$singleCurrency}}"
																   class="selectgroup-input" {{$cardOrder->currency == $singleCurrency ? 'checked':''}}>
															<span class="selectgroup-button">{{$singleCurrency}}</span>
														</label>
													@endforeach
												</div>
												@error('currency')
												<span class="text-danger">{{$message}}</span>
												@enderror
											</div>
										@endif
										@if(isset($cardOrder) && !empty($cardOrder->form_input))
											<div class="col-md-12 custom-back mb-4">
												<div class="dark-bg p-3">
													<div class="row">
														@forelse($cardOrder->form_input as $k => $v)

															<div class="col-md-6">
																@if ($v->type == 'text')
																	<div class="form-group">
																		<label
																			for="exampleFormControlInput1"
																			class="form-label">{{$v->field_name }}

																			@if ($v->validation == 'required')
																				<span class="text-danger">*</span>
																			@endif
																		</label>
																		<input name="{{ $k }}" type="text"
																			   class="form-control"
																			   value="{{ $v->field_value }}"
																		@if ($v->validation == 'required')  @endif />
																		@error($k)
																		<span
																			class="text-danger">{{ $message  }}</span>
																		@enderror
																	</div>
																@elseif ($v->type == 'date')
																	<div class="form-group">
																		<label
																			for="exampleFormControlInput1"
																			class="form-label"
																		>{{ $v->field_name }}

																			@if ($v->validation == 'required')
																				<span class="text-danger">*</span>
																			@endif
																		</label>
																		<input name="{{ $k }}" type="date"
																			   class="form-control"
																			   value="{{ $v->field_value }}"
																		@if ($v->validation == 'required')  @endif />

																		@error($k)
																		<span
																			class="text-danger">{{ $message  }}</span>
																		@enderror
																	</div>
																@elseif($v->type == 'textarea')
																	<div class="form-group">
																		<label
																			for="exampleFormControlInput1"
																			class="form-label"
																		>{{ $v->field_name }}

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
																	<div class="form-group">
																		<label
																			for="exampleFormControlInput1"
																			class="form-label"
																		>{{ $v->field_name }}

																			@if ($v->validation == 'required')
																				<span class="text-danger">*</span>
																			@endif
																		</label>

																		<input name="{{ $k }}" type="file"
																			   class="form-control"
																			   placeholder="{{ trans($v->field_value) }}"
																		@if ($v->validation == 'required')   @endif />

																		@if ($errors->has($k))
																			<span
																				class="text-danger">{{ trans($errors->first($k)) }}</span>
																		@endif
																	</div>
																@endif
															</div>
														@empty
														@endforelse
													</div>
												</div>
											</div>
										@endif
										<button type="submit"
												class="btn btn-primary btn-block mt-4">@lang('Re Submit')</button>
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

