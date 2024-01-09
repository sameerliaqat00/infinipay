@extends('frontend.layouts.master')
@section('page_title',__('500'))
@section('content')
	<!-- Banner -->
	<section class="hero-section bg--title">
		<div class="hero-shapes2" style="background:url({{ getFile(config('location.breadcrumb.path').'/breadcrumb.png') }}) no-repeat center center/cover;">&nbsp;</div>
		<div class="container">
			<div class="hero-breadcrumb">
				<h2 class="title">@lang('500')</h2>
				<ul class="breadcrumb">
					<li>
						<a href="{{route('home')}}">@lang('Home')</a>
					</li>
					<li>
						@lang('500')
					</li>
				</ul>
			</div>
		</div>
	</section>
	<!-- Banner -->

	<section class="faqs-section pt-100 pb-100">
		<div class="container">
			<div class="section__title section__title-center">
				<h3 class="section__title">{{trans('500')}}</h3>
				<p>@lang("The server encountered an internal error misconfiguration and was unable to complate your request. Please contact the server administrator.")</p>

				<a href="{{url('/')}}" class="ms-lg-3 cmn--btn">@lang('Back To Home')</a>
			</div>
		</div>
	</section>

@endsection
