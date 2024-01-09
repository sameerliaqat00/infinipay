@extends('frontend.layouts.master')
@section('page_title',__('About Us'))

@section('content')
	<!-- Banner -->
	<section class="hero-section bg--title">
		<div class="hero-shapes2"
			 style="background:url({{ getFile(config('location.breadcrumb.path').'/breadcrumb.png') }}) no-repeat center center/cover;">
			&nbsp;
		</div>
		<div class="container">
			<div class="hero-breadcrumb">
				<h2 class="title">@lang('About Us')</h2>
				<ul class="breadcrumb">
					<li>
						<a href="{{route('home')}}">@lang('Home')</a>
					</li>
					<li>
						@lang('About Us')
					</li>
				</ul>
			</div>
		</div>
	</section>
	<!-- Banner -->

	<!-- Start About -->
	@if(isset($templates['about-us'][0]) && $aboutUs = $templates['about-us'][0])
		<section class="about-section pt-100 pb-100">
			<div class="container">
				<div class="row justify-content-between">
					<div class="col-lg-5">
						<div class="section__title sticky">
							<span class="section__cate">@lang(optional(@$aboutUs->description)->title)</span>
							<h3 class="section__title">@lang(optional(@$aboutUs->description)->sub_title)</h3>
							<p>
								{!! __(optional(@$aboutUs->description)->short_description) !!}
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

	<!-- Start Feature -->
	@if(isset($templates['feature'][0]) && $feature = $templates['feature'][0])
		<section class="why-choose-us bg--section pt-100 pb-50">
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

	<!-- Start Counter Section -->
	@if(isset($templates['customers-content'][0]) && $customersContent = $templates['customers-content'][0])
		<section class="counter-section bg--title pt-100 pb-100 position-relative">
			<div class="hero-shapes2">&nbsp;</div>
			<div class="container">
				<div class="section__title section__title-center text--white">
					<span class="section__cate">@lang(optional($customersContent->description)->title)</span>
					<h3 class="section__title">@lang(wordTruncate(optional($customersContent->description)->sub_title,-1,1))</h3>
					<p>
						{!! __(optional($customersContent->description)->short_description) !!}
					</p>
				</div>
				<div class="row justify-content-center g-4">
					<div class="col-xl-3 col-sm-6">
						<div class="counter-item">
							<div class="counter-thumb">
								<i class="las la-users"></i>
							</div>
							<div class="counter-content">
								<h5 class="title">@lang('Total Users')</h5>
								<div class="counter-header">
									<h5 class="subtitle">@lang(optional($customersContent->description)->total_users)</h5>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-3 col-sm-6">
						<div class="counter-item">
							<div class="counter-thumb">
								<i class="las la-server"></i>
							</div>
							<div class="counter-content">
								<h5 class="title">@lang('Total Currency')</h5>
								<div class="counter-header">
									<h5 class="subtitle">
										<sup>{{ __(basicControl()->currency_symbol) }}</sup>@lang(optional($customersContent->description)->total_currency)
									</h5>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-3 col-sm-6">
						<div class="counter-item">
							<div class="counter-thumb">
								<i class="las la-university"></i>
							</div>
							<div class="counter-content">
								<h5 class="title">@lang('Total Wallet')</h5>
								<div class="counter-header">
									<h5 class="subtitle">
										<sup>{{ __(basicControl()->currency_symbol) }}</sup>@lang(optional($customersContent->description)->total_wallet)
									</h5>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-3 col-sm-6">
						<div class="counter-item">
							<div class="counter-thumb">
								<i class="las la-chalkboard-teacher"></i>
							</div>
							<div class="counter-content">
								<h5 class="title">@lang('Gateways')</h5>
								<div class="counter-header">
									<h5 class="subtitle">@lang(optional($customersContent->description)->gateways)</h5>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	@endif
	<!-- End Counter Section -->

	<!-- Start FAQs Section -->
	@if(isset($templates['faq'][0]) && $faq = $templates['faq'][0])
		<section class="faqs-section pt-100 pb-100">
			<div class="container">
				<div class="section__title section__title-center">
					<span class="section__cate">@lang(optional($faq->description)->title)</span>
					<h3 class="section__title">@lang(optional($faq->description)->sub_title)</h3>
					<p>
						{!! __(optional($faq->description)->short_description) !!}
					</p>
				</div>
				<div class="faq-block">
					@if(isset($contentDetails['faq']) && $faqContents = $contentDetails['faq']->take(5))
						@foreach($faqContents as $faqContent)
							<div class="faq-block-item {{ $loop->first ? 'active open' : '' }}">
								<div class="faq-block-title">
									<h5 class="title"><i
											class="las la-question"></i>@lang(optional($faqContent->description)->title)
									</h5>
								</div>
								<div class="faq-block-content">
									<p>
										{!! __(optional($faqContent->description)->short_description) !!}
									</p>
								</div>
							</div>
						@endforeach
					@endif
				</div>
			</div>
		</section>
	@endif
	<!-- End FAQs Section -->

@endsection

