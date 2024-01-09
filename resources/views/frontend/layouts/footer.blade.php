<!-- Footer -->
<footer class="bg--title">
	<div class="container">
		<div class="pt-100 pb-100">
			<div class="footer-link-wrap">

				<div class="link-item">
					<div class="footer-description">
						<div class="logo">
							<a href="{{ route('home') }}">
								<img src="{{ getFile(config('location.logo.path').'footer-logo.png') }}"
									 alt="@lang(basicControl()->site_title)">
							</a>
						</div>
						<p class="pt-4">
							@if(isset($templates['about-us'][0]) && $aboutUs = $templates['about-us'][0])
								@lang(optional($aboutUs->description)->short_description)
							@endif
						</p>
						<div class="social-icon">
							@if($socialLinks->count() > 0)
								@foreach($socialLinks as $socialLink)
									<a href="{{ optional($socialLink->description)->social_link }}" target="_blank"
									   class="icon">
										<i class="{{ optional($socialLink->description)->social_icon }}"></i>
									</a>
								@endforeach
							@endif
						</div>
					</div>
				</div>

				<div class="link-item">
					<h4 class="title">@lang('Useful Links')</h4>
					<ul>
						<li>
							<a href="{{ route('about') }}">@lang('About')</a>
						</li>
						<li>
							<a href="{{ route('faq') }}">@lang('Help') (@lang('FAQ'))</a>
						</li>
						<li>
							<a href="{{ route('blog') }}">@lang('Blog')</a>
						</li>
						<li>
							<a href="{{ route('contact') }}">@lang('Contact')</a>
						</li>
					</ul>
				</div>

				<div class="link-item">
					<h4 class="title">@lang('Support')</h4>
					<ul>
						@isset($contentDetails['extra-pages'])
							@foreach($contentDetails['extra-pages'] as $data)
								<li>
									<a href="{{route('getLink', [$data->content_id, Str::slug(optional($data->description)->title)])}}">
										@lang($data->description->title)
									</a>
								</li>
							@endforeach
						@endisset
					</ul>
				</div>

				<div class="link-item footer-contact-info">
					<h4 class="title">@lang('Contact Us')</h4>
					<ul>
						@if($contactDetails->count() > 0)
							@foreach($contactDetails as $contactDetail)
								<li>
									<div class="d-flex flex-row align-items-center">
										<div class="icon">
											<img
												src="{{ getFile(config('location.content.path').(isset($contactDetail->content->contentMedia->description->image) ? optional($contactDetail->content->contentMedia->description)->image : '')) }}">
										</div>
										<div>
											<p class="contant-details">{!! optional($contactDetail->description)->short_description !!}</p>
										</div>
									</div>
								</li>
							@endforeach
						@endif
					</ul>
				</div>
			</div>
		</div>
	</div>

	<div class="border-top py-3 text--light border--top">
		<div class="container text-center">
			@lang('Copyright') {{date('Y')}} <a href="javascript:void(0)"
												class="text--base">{{ __(basicControl()->site_title) }}</a> @lang('All rights reserved')
		</div>
	</div>
</footer>
<!-- Footer -->

<!-- Cookie-content -->
@if($cookie)
	<div class="cookie-content d-none">
		<div class="content">
			<h5 class="title">@lang(optional($cookie->description)->title)</h5>
			<p>
				@lang(optional($cookie->description)->popup_short_description)
				<a href="{{ route('getTemplate', ['cookie-consent']) }}" class="text--base">@lang('Read More')</a>
			</p>
			<div class="btn__grp">
				<a href="javascript:void(0)" class="cmn--btn btn-sm btn--success"
				   id="cookie-accept">@lang('Accept All')</a>
				<a href="javascript:void(0)" class="cmn--btn btn-sm btn--danger"
				   id="cookie-deny">@lang('Decline All')</a>
			</div>
		</div>
	</div>
@endif
<!-- Cookie-content -->
