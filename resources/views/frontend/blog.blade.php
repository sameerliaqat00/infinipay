@extends('frontend.layouts.master')
@section('page_title',__('Blog'))
@section('content')

	<!-- Banner -->
	<section class="hero-section bg--title">
		<div class="hero-shapes2"
			 style="background:url({{ getFile(config('location.breadcrumb.path').'/breadcrumb.png') }}) no-repeat center center/cover;">
			&nbsp;
		</div>
		<div class="container">
			<div class="hero-breadcrumb">
				<h2 class="title">@lang('Blog')</h2>
				<ul class="breadcrumb">
					<li>
						<a href="{{route('home')}}">@lang('Home')</a>
					</li>
					<li>
						@lang('Blog')
					</li>
				</ul>
			</div>
		</div>
	</section>
	<!-- Banner -->

	<!-- Blog Section -->
	<section class="blog-section pt-100 pb-100">
		<div class="container">
			<div class="row g-4">
				@if($contentDetails)
					@foreach($contentDetails as $blogContent)
						<div class="col-xl-4 col-lg-4 col-md-6 col-sm-10">
							<div class="post-item">
								<div class="post-inner">
									<div class="post-img">
										<a href="{{ route('blogDetails', $blogContent) }}">
											<img
												src="{{ getFile(config('location.content.path').(isset($blogContent->content->contentMedia->description->image) ? optional(optional(optional($blogContent->content)->contentMedia)->description)->image : '')) }}"
												alt="@lang('Blog-image')">
										</a>
									</div>
									<div class="post-content text-start">
										<h6 class="title mb-2">
											<a href="{{ route('blogDetails', $blogContent) }}">
												@lang(optional($blogContent->description)->title)
											</a>
										</h6>
										<a href="{{ route('blogDetails', $blogContent) }}" class="text--base">
											@lang('Read More')
										</a>
									</div>
								</div>
							</div>
						</div>
					@endforeach
				@endif
			</div>
		</div>
	</section>
	<!-- Blog Section -->
@endsection

