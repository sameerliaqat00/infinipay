@extends('frontend.layouts.master')
@section('page_title',__('403 Forbidden'))
@section('content')
	<!-- Banner -->
	<section class="hero-section bg--title">
		<div class="hero-shapes2" style="background:url({{ getFile(config('location.breadcrumb.path').'/breadcrumb.png') }}) no-repeat center center/cover;">&nbsp;</div>
		<div class="container">
			<div class="hero-breadcrumb">
				<h2 class="title">@lang('403')</h2>
				<ul class="breadcrumb">
					<li>
						<a href="{{route('home')}}">@lang('Home')</a>
					</li>
					<li>
						@lang('403')
					</li>
				</ul>
			</div>
		</div>
	</section>
	<!-- Banner -->

	<section class="faqs-section pt-100 pb-100">
		<div class="container">
			<div class="section__title section__title-center">
				<h3 class="section__title">{{trans('Forbidden!')}}</h3>
				<p>{{trans("You don't have permission to access ‘/’ on this server")}}</p>

				<a href="{{url('/')}}" class="ms-lg-3 cmn--btn">@lang('Back To Home')</a>
			</div>
		</div>
	</section>

@endsection
