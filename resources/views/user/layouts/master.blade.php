<!DOCTYPE html>
<html lang="en" @if(session()->get('rtl') == 1) dir="rtl" @endif />
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link href="{{ getFile(config('location.logo.path').'favicon.png') }}" rel="icon">
	<title> @yield('page_title') | {{ basicControl()->site_title }} </title>

	@include('user.layouts.styles')
</head>

<body>


<div id="app">
	<div class="main-wrapper main-wrapper-1">
		@include('user.layouts.topbar')
		@include('user.layouts.sidebar')
		@section('content')
		@show
		@include('user.layouts.footer')
	</div>
</div>

@include('user.layouts.scripts')
@include('user.layouts.flash-message')

@yield('scripts')
@stack('load_scripts')
</body>
</html>
