@extends('user.layouts.master')
@section('page_title',__('Manage PIN uses'))

@section('content')
<div class="main-content">
	<section class="section">
		<div class="section-header">
			<h1>@lang('Manage PIN uses')</h1>
			<div class="section-header-breadcrumb">
				<div class="breadcrumb-item active">
					<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
				</div>
				<div class="breadcrumb-item">@lang('Manage PIN uses')</div>
			</div>
		</div>

		<div class="row mb-3">
			<div class="container-fluid" id="container-wrapper">
				<div class="row justify-content-md-center">
					<div class="col-lg-12">
						<div class="card mb-4 card-primary shadow">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
								<h6 class="m-0 font-weight-bold text-primary">@lang('Manage PIN uses')</h6>
							</div>
							<div class="card-body">
								<form action="{{ route('securityPin.manage') }}" method="post">
									@csrf
									<div class="row">
										@foreach(config('transactionType') as $key => $value)
											@if($basic->{$key})
												<div class="col-md-6">
													<div class="form-group">
														<label class="custom-switch" for="{{ $key.'-'.$value }}">
															<input type="checkbox" class="custom-switch-input" id="{{ $key.'-'.$value }}" name="enable_for[]" value="{{$key}}" {{ in_array($key,$enable_for) ? 'checked' : '' }}>
															<span class="custom-switch-indicator"></span>
															<span class="custom-switch-description">{{ __(ucfirst(str_replace('_',' ', $key))) }}</span>
														</label>
													</div>
												</div>
											@endif
										@endforeach
									</div>
									@if(isset($twoFactorSetting->security_pin))
										<div class="form-group mt-3 security-block">
											<label for="security_pin">@lang('Security Pin')</label>
											<input type="password" name="security_pin"
												   placeholder="@lang('Please enter your security PIN')" autocomplete="off"
												   value="{{ old('security_pin') }}"
												   class="form-control @error('security_pin') is-invalid @enderror">
											<div class="invalid-feedback">
												@error('security_pin') @lang($message) @enderror
											</div>
											<div class="valid-feedback"></div>
										</div>
									@endif
									<button type="submit" id="submit"
											class="btn btn-primary btn-sm btn-block">@lang('Save Changes')</button>
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
