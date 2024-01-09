@extends('frontend.layouts.master')
@section('page_title',__(optional($blogDetail->description)->title))

@section('content')
	<!-- Banner -->
	<section class="hero-section bg--title">
		<div class="hero-shapes2" style="background:url({{ getFile(config('location.breadcrumb.path').'/breadcrumb.png') }}) no-repeat center center/cover;">&nbsp;</div>
		<div class="container">
			<div class="hero-breadcrumb">
				<h2 class="title">@lang('Blog Details')</h2>
				<ul class="breadcrumb">
					<li>
						<a href="{{route('home')}}">@lang('Home')</a>
					</li>
					<li>
						@lang('Blog Details')
					</li>
				</ul>
			</div>
		</div>
	</section>
	<!-- Banner -->


	<!-- Blog Details -->
	<section class="blog-section pt-100 pb-100">
		<div class="container">
			<div class="row justify-content-between gy-5">
				<div class="col-lg-8">
					<div class="post-details">
						<img src="{{ getFile(config('location.content.path').(isset($blogDetail->content->contentMedia->description->image) ? optional(optional(optional($blogDetail->content)->contentMedia)->description)->image : '')) }}" alt="@lang(optional($blogDetail->description)->title)" class="w-100">
						<div class="d-flex flex-wrrap post-info">
							<div>
								<i class="fas fa-calendar-alt"></i>
								@lang(date('F d, Y',strtotime($blogDetail->created_at)) )
							</div>
						</div>
						<h4 class="title mt-2">@lang(optional($blogDetail->description)->title)</h4>
						<div class="content">
							<p>{!! __(optional($blogDetail->description)->short_description) !!}</p>
						</div>
					</div>
				</div>
				<div class="col-lg-4">
					<div class="ps-xxl-5">
						<div class="sidebar">
							<div class="sidebar-item">
								<h3 class="sidebar-title">@lang('Popular Posts')</h3>

								<ul class="sidebar-posts">
									@foreach($latestBlogs as $latestBlog)
									<li>
										<div class="image">
											<a href="{{ route('blogDetails',$latestBlog) }}">
												<img src="{{ getFile(config('location.content.path').(isset($latestBlog->content->contentMedia->description->image) ? optional(optional(optional($latestBlog->content)->contentMedia)->description)->image : '')) }}">
											</a>
										</div>
										<div class="content">
											<a href="{{ route('blogDetails',$latestBlog) }}">@lang(optional($latestBlog->description)->title)</a>
											<span>@lang(date('d F, Y',strtotime($latestBlog->created_at)))</span>
										</div>
									</li>
									@endforeach
								</ul><!-- sidebar-posts -->
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- Blog Details -->

@endsection
