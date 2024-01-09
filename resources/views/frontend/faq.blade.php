@extends('frontend.layouts.master')
@section('page_title',__('FAQ'))
@section('content')
	<!-- Banner -->
	<section class="hero-section bg--title">
		<div class="hero-shapes2"
			 style="background:url({{ getFile(config('location.breadcrumb.path').'/breadcrumb.png') }}) no-repeat center center/cover;">
			&nbsp;
		</div>
		<div class="container">
			<div class="hero-breadcrumb">
				<h2 class="title">@lang('FAQ')</h2>
				<ul class="breadcrumb">
					<li>
						<a href="{{route('home')}}">@lang('Home')</a>
					</li>
					<li>
						@lang('FAQ')
					</li>
				</ul>
			</div>
		</div>
	</section>
	<!-- Banner -->

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
					@if(isset($contentDetails['faq']) && $faqContents = $contentDetails['faq'])
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
