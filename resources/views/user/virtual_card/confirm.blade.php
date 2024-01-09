@extends('user.layouts.master')
@section('page_title',__('Confirm Card Request'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Confirm Card Request')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Confirm Card Request')</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row justify-content-md-center">
						<div class="col-lg-6">
							<div class="card mb-4 shadow card-primary">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-center">
									<h6 class="m-0 font-weight-bold text-primary">{{optional($order->cardMethod)->name}} @lang('Card Request')</h6>
								</div>
								<div class="card-body">
									<form action="{{ route('order.confirm',$orderId) }}" method="post">
										@csrf
										<li class="list-group-item d-flex justify-content-between">
											<span> {{ trans('Currency') }} :</span>
											<span
												class="text-info">{{ trans($order->currency) }}</span>
										</li>
										@if($order->form_input)
											@forelse($order->form_input as $k => $v)

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
														<span> {{ @$v->field_level }} :</span>
														<span
															class="text-info">{{ @$v->field_value }}</span>
													</li>
												@endif
											@empty
											@endforelse
										@endif
										<div class="form-group mt-3 security-block">
											@if(in_array('virtual_card',$enable_for))
												<label for="security_pin">@lang('Security Pin')</label>
												<input type="password" name="security_pin"
													   placeholder="@lang('Please enter your security PIN')"
													   autocomplete="off"
													   value="{{ old('security_pin') }}"
													   class="form-control @error('security_pin') is-invalid @enderror">
												<div class="invalid-feedback">
													@error('security_pin') @lang($message) @enderror
												</div>
												<div class="valid-feedback"></div>
											@endif
										</div>
										<button type="submit" id="submit"
												class="btn btn-primary btn-sm btn-block btn-security">@lang('Confirm')</button>
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
