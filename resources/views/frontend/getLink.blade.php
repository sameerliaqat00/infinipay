@extends('frontend.layouts.master')
@section('page_title')
    @lang($title)
@endsection

@section('content')

	<!-- Banner -->
	<section class="hero-section bg--title">
		<div class="hero-shapes2" style="background:url({{ getFile(config('location.breadcrumb.path').'/breadcrumb.png') }}) no-repeat center center/cover;">&nbsp;</div>
		<div class="container">
			<div class="hero-breadcrumb">
				<h2 class="title">@lang(@$title)</h2>
				<ul class="breadcrumb">
					<li>
						<a href="{{route('home')}}">@lang('Home')</a>
					</li>
					<li>
						@lang($title)
					</li>
				</ul>
			</div>
		</div>
	</section>
	<!-- Banner -->

	<!-- Details Section -->
	<section class="about-section pt-100 pb-100">
		<div class="container">
			<div class="about-item">
				<p>@lang(@$description)</p>

			</div>
		</div>
	</section>
	<!-- Details Section -->

@endsection
