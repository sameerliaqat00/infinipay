@extends('user.layouts.master')
@section('page_title',__('Reset Security Pin'))

@section('content')
<div class="main-content">
	<section class="section">
		<div class="section-header">
			<h1>@lang('Reset Security Pin')</h1>
			<div class="section-header-breadcrumb">
				<div class="breadcrumb-item active">
					<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
				</div>
				<div class="breadcrumb-item">@lang('Reset Security Pin')</div>
			</div>
		</div>

		<div class="row mb-3">
			<div class="container-fluid" id="container-wrapper">
				<div class="row justify-content-md-center">
					<div class="col-lg-12">
						<div class="card mb-4 card-primary shadow">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
								<h6 class="m-0 font-weight-bold text-primary">@lang('Reset Security Pin')</h6>
								<a href="{{ route('securityPin.manage') }}" class="btn btn-sm btn-outline-primary">
									<i class="fas fa-user-lock"></i> @lang('Manage PIN uses')</a>
							</div>
							<div class="card-body">
								<form action="{{ route('securityPin.reset') }}" method="post">
									@csrf
									<div class="form-group">
										<label for="answer">{{ optional($twoFactorSetting->securityQuestion)->question }}</label>
										<input type="text" value="{{ old('answer') }}" name="answer"
											   placeholder="@lang("Security question's answer")"
											   class="form-control @error('answer') is-invalid @enderror"
											   autocomplete="off">
										<div class="invalid-feedback">
											@error('answer') @lang($message) @enderror
										</div>
										<div class="form-text text-muted">
											@lang('Hints'): {{ __($twoFactorSetting->hints) }}
										</div>
									</div>
									<div class="form-group">
										<label for="old_security_pin">@lang('Old security pin')</label>
										<input type="password" value="{{ old('old_security_pin') }}" name="old_security_pin"
											   placeholder="@lang('eg:12345')"
											   class="form-control @error('old_security_pin') is-invalid @enderror"
											   autocomplete="off">
										<div class="invalid-feedback">
											@error('old_security_pin') @lang($message) @enderror
										</div>
										<div class="valid-feedback"></div>
									</div>
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="security_pin">@lang('Security Pin')</label>
												<input type="password" value="{{ old('security_pin') }}" name="security_pin"
													   placeholder="@lang('eg:12345')"
													   class="form-control @error('security_pin') is-invalid @enderror"
													   autocomplete="off">
												<div class="invalid-feedback">
													@error('security_pin') @lang($message) @enderror
												</div>
												<div class="valid-feedback"></div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="security_pin_confirmation">@lang('Confirm pin')</label>
												<input type="password" value="{{ old('security_pin_confirmation') }}"
													   name="security_pin_confirmation" placeholder="@lang('eg:12345')"
													   class="form-control @error('security_pin_confirmation') is-invalid @enderror"
													   autocomplete="off">
												<div class="invalid-feedback">
													@error('security_pin_confirmation') @lang($message) @enderror
												</div>
												<div class="valid-feedback"></div>
											</div>
										</div>
									</div>
									<button type="submit" id="submit"
											class="btn btn-primary btn-sm btn-block">@lang('Reset Security PIN')</button>
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
