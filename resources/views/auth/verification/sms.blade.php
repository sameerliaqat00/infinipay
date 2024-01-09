@extends('frontend.layouts.master')
@section('page_title',__('SMS Verification'))
@section('content')
	<section class="account-section bg--title" style="border-bottom: 1px solid #5f5f5f">
		<div class="container">
			<div class="row justify-content-center flex-wrap-reverse gy-4 align-items-center">
				@if($template)
					<div class="col-lg-6 col-xl-5 col-xxl-4">
						<div class="section__title text--white text-center text-lg-start">
							<span class="section__cate">@lang(optional($template->description)->title)</span>
							<h3 class="section__title">@lang(optional($template->description)->sub_title)</h3>
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
						<form class="account-form" action="{{ route('user.smsVerify') }}" method="post">
							@csrf
							<div class="form--group">
								<input type="text" name="code"
									   class="form-control form--control @error('code') is-invalid @enderror @error('error') is-invalid @enderror">
								<label for="code" class="form--label prevent-select">@lang('Code')</label>

								<div class="invalid-feedback">
									@error('code') @lang($message) @enderror
									@error('error') @lang($message) @enderror
								</div>
							</div>
							<div class="form--group mb-4">
								<button type="submit"
										class="cmn--btn w-100 justify-content-center text--white border-0">@lang('Submit')</button>
							</div>
							@if (Route::has('user.resendCode'))
								<div class="form--group mb-0 text-center">
									@lang("Don't have an account?") <a href="{{route('user.resendCode')}}?type=phone"
																	   class="text--base">@lang('Resend code')</a>
									@error('resend')
									<p class="text-danger mt-1">@lang($message)</p>
									@enderror
								</div>
							@endif
						</form>
					</div>
				</div>
			</div>
		</div>
	</section>
@endsection
