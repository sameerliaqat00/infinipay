<script src="{{ asset('assets/frontend/js/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('assets/frontend/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/frontend/js/viewport.jquery.js') }}"></script>
<script src="{{ asset('assets/frontend/js/odometer.min.js') }}"></script>
<script src="{{ asset('assets/frontend/js/lightbox.min.js') }}"></script>
<script src="{{ asset('assets/frontend/js/owl.min.js') }}"></script>
<script src="{{ asset('assets/frontend/js/main.js') }}"></script>
<script src="{{ asset('assets/dashboard/js/notiflix-aio-2.7.0.min.js') }}"></script>

@if($basicControl->analytic_status)
	<script async src="https://www.googletagmanager.com/gtag/js?id={{ $basicControl->MEASUREMENT_ID }}"></script>
	<script>
		$(document).ready(function () {
			var MEASUREMENT_ID = "{{ $basicControl->MEASUREMENT_ID }}";
			window.dataLayer = window.dataLayer || [];

			function gtag() {
				dataLayer.push(arguments);
			}

			gtag('js', new Date());
			gtag('config', MEASUREMENT_ID);
		});
	</script>
@endif

@if($basicControl->tawk_status)
	<script type="text/javascript">
		$(document).ready(function () {
			var Tawk_SRC = 'https://embed.tawk.to/' + "{{ trim($basicControl->tawk_id) }}";
			var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();
			(function () {
				var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
				s1.async = true;
				s1.src = Tawk_SRC;
				s1.charset = 'UTF-8';
				s1.setAttribute('crossorigin', '*');
				s0.parentNode.insertBefore(s1, s0);
			})();
		});
	</script>



@endif

@if($basicControl->fb_messenger_status)
	<div id="fb-root"></div>
	<script>
		$(document).ready(function () {
			var fb_app_id = "{{ $basicControl->fb_app_id }}";
			window.fbAsyncInit = function () {
				FB.init({
					appId: fb_app_id,
					autoLogAppEvents: true,
					xfbml: true,
					version: 'v10.0'
				});
			};
			(function (d, s, id) {
				var js, fjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(id)) return;
				js = d.createElement(s);
				js.id = id;
				js.src = 'https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js';
				fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));
		});
	</script>
	<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script>
	<div class="fb-customerchat" page_id="{{ $basicControl->fb_page_id }}"></div>
@endif

<script defer>
	$('.selectLanguage').change(function () {
		location.href = $(this).val();
	});
</script>


<script>
	"use strict";
	var root = document.querySelector(':root');
	root.style.setProperty('--base-clr', '{{$basic->primaryColor??'#f05822'}}');
	root.style.setProperty('--base-bg', 'linear-gradient(170.04deg, {{$basic->primaryColor??'#f05822'}} -8.91%, {{$basic->secondaryColor??'#f0aa22'}} 99.52%)');


</script>

@stack('extra_scripts')
