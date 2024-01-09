@extends('frontend.layouts.master')
@section('page_title',__('Home'))

@section('content')

	<!-- Start hero-section -->
	<section class="hero-section bg--section">
		<div class="hero-shapes bg_img d-none d-md-block"
			 data-img="{{getFile('assets/frontend/images/banner-shapes.png')}}"></div>
		<div class="hero-shapes2"
			 style="background: url({{getFile('assets/frontend/images/banner-shape-2.png')}}) no-repeat center center/cover;">
			&nbsp;
		</div>
		<div class="container">
			<div class="hero-slider owl-theme owl-carousel">
				@if(isset($contentDetails['banner']) && $bannerContents = $contentDetails['banner'])
					@foreach($bannerContents as $bannerContent)
						<div class="hero-item">
							<div class="hero-cont">
								<h1 class="title">@lang(optional($bannerContent->description)->title)</h1>
								<p class="banner-txt">
									@lang(optional($bannerContent->description)->subtitle)
								</p>
								<a href="{{ optional($bannerContent->content->contentMedia->description)->button_link }}"
								   class="cmn--btn">@lang(optional($bannerContent->description)->button_name)</a>
							</div>
							<div class="hero-img">
								<img
									src="{{ getFile(config('location.content.path').(isset($bannerContent->content->contentMedia->description->image) ? optional($bannerContent->content->contentMedia->description)->image : '')) }}"
									alt="@lang('hero-banner')">
							</div>
						</div>
					@endforeach
				@endif
			</div>
		</div>
		<div class="container owl--dots">
			<div class="hero-dots owl-dots"></div>
		</div>
		<div class="scrollNext">&nbsp;</div>
	</section>
	<!-- End hero-section -->

	<!-- Start About -->
	@if(isset($templates['about-us'][0]) && $aboutUs = $templates['about-us'][0])
		<section class="about-section pt-100 pb-100">
			<div class="container">
				<div class="row justify-content-between">
					<div class="col-lg-5">
						<div class="section__title sticky">
							<span class="section__cate">@lang(optional($aboutUs->description)->title)</span>
							<h3 class="section__title">@lang(optional($aboutUs->description)->sub_title)</h3>
							<p>
								{!! __(optional($aboutUs->description)->short_description) !!}
							</p>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="section__title about-content">
							<div class="row">
								@if(isset($contentDetails['about-us']) && $aboutUsContents = $contentDetails['about-us']->take(2))
									@foreach($aboutUsContents as $aboutUsContent)
										<div class="col-md-12 mb-5">
											<span
												class="section__cate">@lang(optional($aboutUsContent->description)->title)</span>
											<p>{!! __(optional($aboutUsContent->description)->short_description) !!}</p>
										</div>
									@endforeach
								@endif
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	@endif
	<!-- End About -->

	<!-- Start Service -->
	@if(isset($templates['services'][0]) && $services = $templates['services'][0])
		<section class="get-payment-section pt-100 pb-100 bg--section">
			<div class="container">
				<div class="row justify-content-between flex-wrap-reverse">
					<div class="col-lg-6">
						@if(isset($contentDetails['services']) && $serviceContents = $contentDetails['services'])
							@foreach($serviceContents as $serviceContent)
								<div class="get-payment-item bg--body">
									<div class="img">
										<img
											src="{{ getFile(config('location.content.path').(isset($serviceContent->content->contentMedia->description->image) ? optional($serviceContent->content->contentMedia->description)->image : '')) }}">
									</div>
									<div class="cont">
										<h5 class="title">@lang(optional( $serviceContent->description)->title)</h5>
										<p>
											{!! __(optional($serviceContent->description)->short_description) !!}
										</p>
									</div>
								</div>
							@endforeach
						@endif
					</div>
					<div class="col-lg-5">
						<div class="section__title sticky">
							<span class="section__cate">@lang(optional($services->description)->title)</span>
							<h3 class="section__title">@lang(optional($services->description)->sub_title)</h3>
							<p>
								{!! __(optional($services->description)->short_description) !!}
							</p>
						</div>
					</div>
				</div>
			</div>
		</section>
	@endif
	<!-- End Service -->

	<!-- Start How To Get -->
	@if(isset($templates['get-started'][0]) && $getStarted = $templates['get-started'][0])
		<section class="how-to-section bg--title pt-100">
			<div class="container">
				<div class="section__title section__title-center text--white">
					<span class="section__cate">@lang(optional($getStarted->description)->title)</span>
					<h3 class="section__title">@lang(optional($getStarted->description)->sub_title)</h3>
					<p>
						{!! __(optional($getStarted->description)->short_description) !!}
					</p>
				</div>

				@if(isset($contentDetails['get-started']) && $getStartedContents = $contentDetails['get-started'])
					<div class="row gy-4">
						<div class="col-lg-6 align-self-center py-lg-5">
							<div class="process-wrapper">
								@foreach($getStartedContents as $getStartedContent)
									<div class="process-block-main">
										<div class="icon center-div">
											<i class="las la-user vertical-center"></i>
										</div>
										<div class="cont">
											<h5 class="title">@lang(optional($getStartedContent->description)->title)</h5>
											<p>
												{!! __(optional($getStartedContent->description)->short_description) !!}
											</p>
										</div>
									</div>
								@endforeach
							</div>
						</div>
						@endif

						<div class="col-lg-6 align-self-end">
							<div class="how-thumb">
								<img
									src="{{ getFile(config('location.template.path').(isset($getStarted->templateMedia()->description->image) ? $getStarted->templateMedia()->description->image : '')) }}"
									alt="@lang(optional($getStarted->description)->title)">
							</div>
						</div>
					</div>
			</div>
		</section>
	@endif
	<!-- End How To Get -->

	<!-- Start Feature -->
	@if(isset($templates['feature'][0]) && $feature = $templates['feature'][0])
		<section class="why-choose-us pt-100 pb-50">
			<div class="container">
				<div class="section__title section__title-center">
					<span class="section__cate">@lang(optional($feature->description)->title)</span>
					<h3 class="section__title">@lang(optional($feature->description)->sub_title)</h3>
					<p>
						{!! __(optional($feature->description)->short_description) !!}
					</p>
				</div>

				<div class="app-services-block">
					<div class="app-services-inner">
						@if(isset($contentDetails['feature']) && $featureContents = $contentDetails['feature'])
							@foreach($featureContents as $key => $featureContent)
								@if($key%2==0)
									<div class="app-services-point">
										<img
											src="{{ getFile(config('location.content.path').(isset(optional(optional(optional($featureContent->content)->contentMedia)->description)->image) ? optional(optional(optional($featureContent->content)->contentMedia)->description)->image : '')) }}">
										<h5 class="title">@lang(optional($featureContent->description)->title)</h5>
										<p>{!!  __(optional($featureContent->description)->short_description)  !!}</p>
									</div>
								@endif
							@endforeach
					</div>
					@endif

					<div class="app-services-inner middle-img">
						<img
							src="{{ getFile(config('location.template.path').(isset($feature->templateMedia()->description->image) ? $feature->templateMedia()->description->image : '')) }}"
							alt="@lang(optional($feature->description)->title)">
					</div>

					@if(isset($contentDetails['feature']) && $featureContents = $contentDetails['feature'])
						<div class="app-services-inner">
							@foreach($featureContents as $key => $featureContent)
								@if($key%2!==0)
									<div class="app-services-point">
										<img
											src="{{ getFile(config('location.content.path').(isset(optional(optional(optional($featureContent->content)->contentMedia)->description)->image) ? optional(optional(optional($featureContent->content)->contentMedia)->description)->image : '')) }}">
										<h5 class="title">@lang(optional($featureContent->description)->title)</h5>
										<p>{!!  __(optional($featureContent->description)->short_description)  !!}</p>
									</div>
								@endif
							@endforeach
						</div>
					@endif
				</div>
			</div>
		</section>
	@endif
	<!-- End Feature -->


	@if(0 < count($gateways))
		<!-- Start Partner Section -->
		<section class="partner-section pt-50 pb-50">
			<div class="container">
				<div class="partner-slider owl-theme owl-carousel">
					@foreach($gateways as $gateway)
						<div class="partner-item">
							<img src="{{ getFile(config('location.gateway.path').$gateway) }}">
						</div>
					@endforeach
				</div>
			</div>
		</section>
		<!-- End Partner Section -->
	@endif

	<!-- Start Testimonial Section -->
	@if(isset($templates['clients-feedback'][0]) && $clientsFeedback = $templates['clients-feedback'][0])
		<section class="testimonial-section pt-50 pb-50">
			<div class="container">
				<div class="section__title section__title-center">
					<span class="section__cate">@lang(optional($clientsFeedback->description)->title)</span>
					<h3 class="section__title">{!! __(optional($clientsFeedback->description)->sub_title) !!}</h3>
				</div>

				@if(isset($contentDetails['clients-feedback']) && $clientsFeedbackContents = $contentDetails['clients-feedback'])
					<div class="client-slider owl-theme owl-carousel">
						@foreach($clientsFeedbackContents as $clientsFeedbackContent)
							<div class="client-item">
								<img
									src="{{ getFile(config('location.content.path').(isset($clientsFeedbackContent->content->contentMedia->description->image) ? optional(optional(optional($clientsFeedbackContent->content)->contentMedia)->description)->image : '')) }}">
								<p>
									{!! __(optional($clientsFeedbackContent->description)->short_description) !!}
								</p>
								<h6 class="name">@lang(optional($clientsFeedbackContent->description)->title)</h6>
								<span
									class="info">@lang(optional($clientsFeedbackContent->description)->sub_title)</span>
								<div class="icon center-div">
									<i class="fas fa-quote-right vertical-center"></i>
								</div>
							</div>
						@endforeach
					</div>
				@endif
			</div>
		</section>
	@endif
	<!-- End Testimonial Section -->

	<!-- Start Blog Section -->
	@if(isset($templates['blog'][0]) && $blog = $templates['blog'][0])
		<section class="blog-section pt-50 pb-100">
			<div class="container">
				<div class="section__title section__title-center">
					<span class="section__cate">@lang(optional($blog->description)->title)</span>
					<h3 class="section__title">@lang(optional($blog->description)->sub_title)</h3>
					<p>
						{!! __(optional($blog->description)->short_description) !!}
					</p>
				</div>
				<div class="row g-4 justify-content-center">
					@if(isset($contentDetails['blog']) && $blogContents = $contentDetails['blog']->take(3))
						@foreach($blogContents as $blogContent)
							<div class="col-lg-4 col-md-6 col-sm-10">
								<div class="post-item">
									<div class="post-inner">
										<div class="post-img">
											<a href="{{ route('blogDetails', $blogContent) }}">
												<img
													src="{{ getFile(config('location.content.path').(isset($blogContent->content->contentMedia->description->image) ? optional(optional(optional($blogContent->content)->contentMedia)->description)->image : '')) }}">
											</a>
										</div>
										<div class="post-content text-start">
											<h6 class="title mb-2">
												<a href="{{ route('blogDetails', $blogContent) }}">
													{{\Illuminate\Support\Str::limit(@$blogContent->description->title,65)}}</
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
	@endif
	<!-- End Blog Section -->

@endsection
