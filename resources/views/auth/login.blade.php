@extends('frontend.layouts.master')
@section('page_title',__('Login'))
@section('content')
	<section class="account-section bg--title" style="border-bottom: 1px solid #5f5f5f">
		<div class="container">
			<div class="row justify-content-center flex-wrap-reverse gy-4 align-items-center">
				@if($template)
					<div class="col-lg-6 col-xl-5 col-xxl-4">
						<div class="section__title text--white text-center text-lg-start">
							<span class="section__cate">@lang(optional($template->description)->title)</span>
							<h3 class="section__title">@lang(optional($template->description)->sub_title)</h3>
							<p>
								@lang(optional($template->description)->short_description)
							</p>
							<a href="{{ @$template->templateMedia()->description->button_link }}"
							   class="cmn--btn btn-outline btn-sm mt-3"><i class="las la-angle-left"></i>
								@lang(optional($template->description)->button_name)
							</a>
						</div>
					</div>
				@endif

				<div class="col-lg-6 col-xxl-5">
					<div class="account__wrapper bg--body">
						<div class="account-logo">
							<a href="{{ route('home') }}">
								<img src="{{ getFile(config('location.logo.path').'logo.png') }}"
									 alt="@lang(basicControl()->site_title)">
							</a>
						</div>

						@if (session('status'))
							<div class="alert alert-success alert-dismissible fade show" role="alert">
								{{ trans(session('status')) }}
								<button type="button" class="btn-close removeStatus" data-bs-dismiss="alert"
										aria-label="Close">
								</button>
							</div>
						@endif

						<form class="account-form" action="{{ route('login') }}" method="post">
							@csrf
							<div class="form--group">
								<input type="text" name="identity" value="{{ old('identity') }}"
									   class="form-control form--control @error('username') is-invalid @enderror @error('email') is-invalid @enderror"
									   id="username">
								<label for="identity"
									   class="form--label prevent-select">@lang('Username or Email')</label>

								<div class="invalid-feedback">
									@error('username') @lang($message) @enderror
									@error('email') @lang($message) @enderror
								</div>
							</div>
							<div class="form--group">
								<input type="password" name="password"
									   class="form-control form--control @error('password') is-invalid @enderror"
									   id="password">
								<label for="password" class="form--label prevent-select">@lang('Password')</label>

								<div class="invalid-feedback">
									@error('password') @lang($message) @enderror
								</div>
							</div>
							@if(basicControl()->reCaptcha_status_login)
								<div class="form-group">
									{!! NoCaptcha::renderJs() !!}
									{!! NoCaptcha::display() !!}
									@error('g-recaptcha-response')
									<div class="alert alert-danger mt-1 mb-1">@lang($message)</div>
									@enderror
								</div>
							@endif
							<div class="form--group checkgroup d-flex flex-row justify-content-between">
								<div class="form-check">
									<input class="form-check-input form--check-input" type="checkbox" id="check1">
									<label class="form-check-label" for="check1">
										@lang('Remember Me')
									</label>
								</div>
								<div>
									@if (Route::has('password.request'))
										<a href="{{ route('password.request') }}"
										   class="text--base">@lang('Forgot Your Password')?</a>
									@endif
								</div>
							</div>

							<div class="form--group mb-4">
								<button type="submit"
										class="cmn--btn w-100 justify-content-center text--white border-0">@lang('Sign In')</button>
							</div>
							<div class="form--group mb-0 text-center">
								@lang("Don't have an account?") <a href="{{ route('register') }}"
																   class="text--base">@lang('Sign Up')</a>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</section>
@endsection
