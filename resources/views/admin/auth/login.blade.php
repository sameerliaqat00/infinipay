@extends('admin.layouts.login-register')
@section('page_title', __('Admin | Login'))

@section('content')
	<div id="app">
		<section class="section">
			<div class="container mt-5">
				<div class="row">
					<div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-6 offset-lg-3 col-xl-4 offset-xl-4">
						<div class="login-brand">
							<a href="{{ route('home') }}">
								<img src="{{ getFile(config('location.logo.path').'logo.png') }}" class="dashboard-logo" alt="@lang('Logo')">
							</a>
						</div>

						<div class="card card-primary shadow">
							<div class="card-header"><h4>@lang('Login')</h4></div>

							<div class="card-body">
								<form action="{{ route('admin.auth.login') }}" method="post" class="needs-validation" novalidate="">
									@csrf
									<div class="form-group">
										<label for="email">@lang('Email or Username')</label>
										<input id="email" type="text" class="form-control @error('username') is-invalid @enderror @error('email') is-invalid @enderror" name="identity" placeholder="@lang('Enter Email or Username')" tabindex="1" autofocus>
										<div class="invalid-feedback">
											@error('username') @lang($message) @enderror
											@error('email') @lang($message) @enderror
										</div>
										<div class="valid-feedback"></div>
									</div>

									<div class="form-group">
										<div class="d-block">
											<label for="password" class="control-label">@lang('Password')</label>
											<div class="float-right">
												@if (Route::has('password.request'))
													<a href="{{ route('admin.password.request') }}" class="text-small">
														@lang('Forgot Password?')
													</a>
												@endif
											</div>
										</div>

										<input id="password" type="password" class="form-control @error('password') is-invalid @enderror" placeholder="@lang('Enter Password')" name="password" tabindex="2" required>
										<div class="invalid-feedback">
											@error('password') @lang($message) @enderror
										</div>
										<div class="valid-feedback"></div>
									</div>

									<div class="form-group">
										<div class="custom-control custom-checkbox">
											<input type="checkbox" name="remember" class="custom-control-input" tabindex="3" id="remember-me" {{ old('remember') ? 'checked' : '' }}>
											<label class="custom-control-label" for="remember-me">@lang('Remember Me')</label>
										</div>
									</div>

									<div class="form-group">
										<button type="submit" class="btn btn-primary btn-lg btn-block" tabindex="4">
											@lang('Login')
										</button>
									</div>
								</form>

							</div>
						</div>

						<div class="simple-footer">
							@lang('Copyright') &copy; <b>{{ __(basicControl()->site_title) }}</b> {{ __(date('Y')) }}
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
@endsection

