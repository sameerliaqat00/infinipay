<!DOCTYPE html />
<!--[if lt IE 7 ]>
<html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]>
<html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]>
<html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!-->
<html class="no-js" lang="en" @if(session()->get('rtl') == 1) dir="rtl" @endif >
<!--<![endif]-->
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <link rel="preconnect" href="https://fonts.gstatic.com"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="keywords" content="{{ __($basicControl->meta_keywords) }}"/>
    <meta name="description" content="{{ __($basicControl->meta_description) }}">
    <meta name="keywords" content="{{ __($basicControl->meta_keywords) }}">
    <link rel="shortcut icon" href="{{ getFile(config('location.logo.path').'favicon.png') }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ getFile(config('location.logo.path').'favicon.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ getFile(config('location.logo.path').'favicon.png')}}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta itemprop="description" content="{{ __($basicControl->meta_description) }}">
    <meta itemprop="image" content="{{ getFile(config('location.logo.path').'meta.png','600x315') }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ __($basicControl->social_title) }}">
    <meta property="og:description" content="{{ __($basicControl->social_description) }}">
    <meta property="og:image" content="{{ getFile(config('location.logo.path') .'meta.png' ) }}"/>
    <meta property="og:image:type"
          content="image/{{ pathinfo(getFile(config('location.logo.path') . 'meta.png' ))['extension'] }}"/>
    <meta property="og:url" content="{{ url()->current() }}">
    <link href="{{ getFile(config('location.logo.path').'logo.png') }}" rel="icon">
    <title> @yield('page_title') | {{ __($basicControl->site_title) }} </title>

	@include('frontend.layouts.styles')
    <!--[if lt IE 9]>
    <script type="application/javascript" src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script type="application/javascript" src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body class="home">
	@include('frontend.layouts.topbar')
	@section('content')
	@show
	@include('frontend.layouts.footer')
	@include('frontend.layouts.scripts')
	@include('frontend.layouts.flash-message')
	@yield('scripts')
</body>
</html>
