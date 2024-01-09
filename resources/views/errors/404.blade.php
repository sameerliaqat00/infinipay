@extends('frontend.layouts.master')
@section('page_title',__('404'))
@section('content')
	<!-- Banner -->
	<section class="hero-section bg--title">
		<div class="hero-shapes2" style="background:url({{ getFile(config('location.breadcrumb.path').'/breadcrumb.png') }}) no-repeat center center/cover;">&nbsp;</div>
		<div class="container">
			<div class="hero-breadcrumb">
				<h2 class="title">@lang('404')</h2>
				<ul class="breadcrumb">
					<li>
						<a href="{{route('home')}}">@lang('Home')</a>
					</li>
					<li>
						@lang('Page Not Found')
					</li>
				</ul>
			</div>
		</div>
	</section>
	<!-- Banner -->

		<section class="faqs-section pt-100 pb-100">
			<div class="container">
				<div class="section__title section__title-center">
					<h3 class="section__title">{{trans('Opps!')}}</h3>
					<p>{{trans('The page you are looking for was not found.')}}</p>

					<a href="{{url('/')}}" class="ms-lg-3 cmn--btn">@lang('Back To Home')</a>
				</div>
			</div>
		</section>

@endsection
