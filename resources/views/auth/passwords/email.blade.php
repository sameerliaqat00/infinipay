@extends('frontend.layouts.master')
@section('page_title',__('Reset Password'))

@section('content')
	<!-- Account -->
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
								<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
								</button>
							</div>
						@endif

						<form action="{{ route('password.email') }}" class="account-form" method="post">
							@csrf
							<div class="form--group">
								<input type="email" name="email"
									   class="form-control form--control @error('email') is-invalid @enderror"
									   id="username">
								<label for="email"
									   class="form--label prevent-select">@lang('Enter Email Address')</label>

								<div class="invalid-feedback">
									@error('email') @lang($message) @enderror
								</div>
							</div>

							<div class="form--group mb-4">
								<button type="submit"
										class="cmn--btn w-100 justify-content-center text--white border-0">@lang('Send Password Reset Link')</button>
							</div>
							<div class="form--group mb-0 text-center">
								@lang('Already have an account?') <a href="{{ route('login') }}"
																	 class="text--base">@lang('Sign In')</a>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- Account -->
@endsection

