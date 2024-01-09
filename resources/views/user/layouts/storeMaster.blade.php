<!DOCTYPE html>
<html lang="en" @if(session()->get('rtl') == 1) dir="rtl" @endif />
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="icon" type="image/x-icon" href="{{asset('assets/store/img/icon/fav.png')}}"/>
	<title> @yield('page_title') | {{ basicControl()->site_title }} </title>
	<script src="https://telegram.org/js/telegram-web-app.js"></script>
	@include('user.layouts.store.styles')
</head>

<body class="">
@include('user.layouts.store.nav')
@include('user.layouts.store.banner')
@yield('content')
@include('user.layouts.store.footer')


@include('user.layouts.store.scripts')
@include('user.layouts.flash-message')
@yield('scripts')

</body>
</html>
