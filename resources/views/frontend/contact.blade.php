@extends('frontend.layouts.master')
@section('page_title',__('Contact Us'))
@section('content')

	<!-- Banner -->
	<section class="hero-section bg--title">
		<div class="hero-shapes2"
			 style="background:url({{ getFile(config('location.breadcrumb.path').'/breadcrumb.png') }}) no-repeat center center/cover;">
			&nbsp;
		</div>
		<div class="container">
			<div class="hero-breadcrumb">
				<h2 class="title">@lang('Contact Us')</h2>
				<ul class="breadcrumb">
					<li>
						<a href="{{route('home')}}">@lang('Home')</a>
					</li>
					<li>
						@lang('Contact Us')
					</li>
				</ul>
			</div>
		</div>
	</section>
	<!-- Banner -->
	<!-- Contact Section -->

	<section class="contact-section pt-100 pb-100">
		<div class="container">
			<div class="contact-wrapper">
				<div class="contact-wrapper-left">

					@if(isset($contact[0]) && $contactContent = $contact[0])
						<div>
							<h3 class="title">@lang(optional($contactContent->description)->title)</h3>
							<p>
								@lang(optional($contactContent->description)->sub_title)
							</p>
						</div>
					@endif
					<ul class="contact-lists">
						@if(isset($contentDetails))
							@foreach($contentDetails as $contentDetail)
								<li class="contact-list-item py-3">
									<span class="icon">
										<img
											src="{{ getFile(config('location.content.path').(isset($contentDetail->content->contentMedia->description->image) ? optional($contentDetail->content->contentMedia->description)->image : '')) }}">
									</span>
									<span
										class="txt">{!! __(optional($contentDetail->description)->short_description) !!}</span>
								</li>
							@endforeach
						@endif
					</ul>
				</div>

				<div class="contact-wrapper-right">
					<form method="post" action="{{ route('contact') }}">
						@csrf
						<div class="row gy-3 gy-md-4">
							<div class="col-sm-6">
								<div class="form-group">
									<label for="name" class="form-label contact-label">@lang('Your Name')</label>
									<input type="text"
										   class="form-control contact-control @error('name') is-invalid @enderror"
										   id="name" name="name" value="{{old('name')}}">

									@error('name')
									<span class="invalid-feedback" role="alert">@lang($message)</span>
									@enderror
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label for="email" class="form-label contact-label">@lang('Your Email')</label>
									<input type="text"
										   class="form-control contact-control @error('email') is-invalid @enderror"
										   name="email" id="email" value="{{old('email')}}">

									@error('email')
									<span class="invalid-feedback" role="alert">@lang($message)</span>
									@enderror
								</div>
							</div>
							<div class="col-sm-12">
								<div class="form-group">
									<label for="subject" class="form-label contact-label">@lang('Your Subject')</label>
									<input type="text"
										   class="form-control contact-control @error('subject') is-invalid @enderror"
										   name="subject" id="subject" value="{{old('subject')}}">

									@error('subject')
									<span class="invalid-feedback" role="alert">@lang($message)</span>
									@enderror
								</div>
							</div>
							<div class="col-sm-12">
								<div class="form-group">
									<label for="message" class="form-label contact-label">@lang('Your Message')</label>
									<textarea name="message" id="message"
											  class="form-control contact-control @error('message') is-invalid @enderror">{{old('message')}}
										</textarea>

									@error('message')
									<span class="invalid-feedback" role="alert">@lang($message)</span>
									@enderror
								</div>
							</div>
							<div class="col-sm-12">
								<div class="form-group">
									<button class="cmn--btn" type="submit">@lang('Send Message')</button>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</section>

	<!-- Contact Section -->

@endsection
