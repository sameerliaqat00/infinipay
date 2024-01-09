<section class="hero-section bg--title">
	<div class="hero-shapes2"
		 style="background:url({{ getFile(config('location.breadcrumb.path').'/breadcrumb.png') }}) no-repeat center center/cover;">
	</div>
	<div class="container">
		<div class="hero-breadcrumb">
			<h2 class="title">{{ __('Pay with ').__(optional($deposit->gateway)->name) }}</h2>
			<ul class="breadcrumb">
				<li>
					<a href="{{route('voucher.public.payment', $deposit->utr)}}">@lang('Voucher Payment')</a>
				</li>
				<li>
					{{ __('Pay with ').__(optional($deposit->gateway)->name) }}
				</li>
			</ul>
		</div>
	</div>
</section>
