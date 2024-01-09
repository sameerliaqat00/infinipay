@extends('user.layouts.master')
@section('page_title',__('Create Security Pin'))

@section('content')
<div class="main-content">
	<section class="section">
		<div class="section-header">
			<h1>@lang('Create Security Pin')</h1>
			<div class="section-header-breadcrumb">
				<div class="breadcrumb-item active">
					<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
				</div>
				<div class="breadcrumb-item">@lang('Create Security Pin')</div>
			</div>
		</div>

		<div class="row mb-3">
			<div class="container-fluid" id="container-wrapper">
				<div class="row justify-content-md-center">
					<div class="col-lg-12">
						<div class="card mb-4 card-primary shadow">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
								<h6 class="m-0 font-weight-bold text-primary">@lang('Create Security Pin')</h6>
							</div>
							<div class="card-body">
								<form action="{{ route('securityPin.store') }}" method="post">
									@csrf
									<div class="form-group search-currency-dropdown">
										<label for="security_question">@lang('Security Question')</label>
										<select name="security_question" class="form-control @error('security_question') is-invalid @enderror">
											@foreach($securityQuestions as $key => $securityQuestion)
												<option value="{{ $securityQuestion->id }}" {{ old('security_question') == $securityQuestion->id ? 'selected' : '' }}>{{ __($securityQuestion->question) }}</option>
											@endforeach
										</select>
										<div class="invalid-feedback">
											@error('security_question') @lang($message) @enderror
										</div>
									</div>
									<div class="form-group">
										<label for="answer">@lang('Answer')</label>
										<input type="text" value="{{ old('answer') }}" name="answer" placeholder="@lang('Security question answer')"
											   class="form-control @error('answer') is-invalid @enderror" autocomplete="off">
										<div class="invalid-feedback">
											@error('answer') @lang($message) @enderror
										</div>
										<div class="valid-feedback"></div>
									</div>
									<div class="form-group">
										<label for="hints">@lang('Hints')</label>
										<input type="text" value="{{ old('hints') }}" name="hints" placeholder="@lang('In future helps you guess the answer')"
											   class="form-control @error('hints') is-invalid @enderror" autocomplete="off">
										<div class="invalid-feedback">
											@error('hints') @lang($message) @enderror
										</div>
										<div class="valid-feedback"></div>
									</div>
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="security_pin">@lang('Security Pin')</label>
												<input type="password" value="{{ old('security_pin') }}" name="security_pin" placeholder="@lang('eg:12345')"
													   class="form-control @error('security_pin') is-invalid @enderror" autocomplete="off">
												<div class="invalid-feedback">
													@error('security_pin') @lang($message) @enderror
												</div>
												<div class="valid-feedback"></div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="security_pin_confirmation">@lang('Security pin confirmation')</label>
												<input type="password" value="{{ old('security_pin_confirmation') }}" name="security_pin_confirmation" placeholder="@lang('eg:12345')"
													   class="form-control @error('security_pin_confirmation') is-invalid @enderror" autocomplete="off">
												<div class="invalid-feedback">
													@error('security_pin_confirmation') @lang($message) @enderror
												</div>
												<div class="valid-feedback"></div>
											</div>
										</div>
									</div>
									<button type="submit" id="submit" class="btn btn-primary btn-sm btn-block">@lang('Create security PIN')</button>
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
