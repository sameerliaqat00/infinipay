@extends('user.layouts.master')
@section('page_title',__('2 Step Security'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('2 Step Security')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('2 Step Security')</div>
				</div>
			</div>
			<div class="row justify-content-between">
				<div class="col-lg-6">
					<div class="card mb-4 card-primary shadow">
						@if (auth()->user()->two_fa)
							<div class="col-md-12 shadow-none p-3 bg-gradient rounded">
								<div class="contact-box">
									<div class="card-header">
										<h5 class="card-title">@lang('Two Factor Authenticator')</h5>
									</div>

									<div class="form-group">
										<div class="form-group form-box">
											<div class="input-group append">
												<input type="text" value="{{ $previousCode }}" class="form-control"
													   id="referralURL" readonly>
												<button class="btn btn-success py-0 copytext" type="button" id="copyBoard"
														onclick="copyFunction()"><i
														class="fa fa-copy"></i> @lang('Copy')
												</button>
											</div>
										</div>
									</div>
									<div class="form-group mx-auto text-center my-3">
										<img class="mx-auto w-30" src="{{ $previousQR }}">
									</div>

									<div class="form-group mx-auto text-center">
										<a href="javascript:void(0)" class="btn btn-primary btn-sm btn-block" data-toggle="modal"
										   data-target="#disableModal">@lang('Disable Two Factor Authenticator')</a>
									</div>
								</div>
							</div>
						@else
							<div class="col-md-12 shadow-none p-3 bg-gradient rounded">
								<div class="contact-box">
									<div class="card-header">
										<h5 class="card-title">@lang('Two Factor Authenticator')</h5>
									</div>

									<div class="form-group form-box">
										<div class="input-group append">
											<input type="text" value="{{ $secret }}" class="form-control"
												   id="referralURL" readonly>
											<button class="btn btn-success py-0 copytext" type="button" id="copyBoard"
													onclick="copyFunction()"><i class="fa fa-copy"></i> @lang('Copy')
											</button>
										</div>
									</div>
									<div class="form-group mx-auto text-center">
										<img class="w-30 mx-auto" src="{{ $qrCodeUrl }}">
									</div>

									<div class="form-group mx-auto text-center">
										<a href="javascript:void(0)" class="btn btn-primary btn-block btn-sm mt-3" data-toggle="modal"
										   data-target="#enableModal">@lang('Enable Two Factor Authenticator')</a>
									</div>
								</div>

							</div>
						@endif
					</div>
				</div>

				<div class="col-lg-6">
					<div class="card mb-4 card-primary shadow">
						<div class="card-header">
							<h5 class="card-title">@lang('Google Authenticator')</h5>
						</div>
						<div class="card-body">

							<h6 class="text-uppercase my-3">@lang('Use Google Authenticator to Scan the QR code  or use the code')</h6>

							<p class="p-5">@lang('Google Authenticator is a multifactor app for mobile devices. It generates timed codes used during the 2-step verification process. To use Google Authenticator, install the Google Authenticator application on your mobile device.')</p>
							<div class="submit-btn-wrapper text-center text-md-left">
								<a class="btn btn-primary btn-sm btn-block"
								   href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=en"
								   target="_blank">@lang('DOWNLOAD APP')</a>
							</div>

						</div>
					</div>
				</div>

			</div>
		</section>
	</div>

	<div id="enableModal" class="modal fade" tabindex="-1" role="dialog"
		 aria-labelledby="primary-header-modalLabel"
		 aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title text-dark font-weight-bold"
						id="primary-header-modalLabel">@lang('Verify Your OTP')</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				<form action="{{ route('user.twoStepEnable') }}" method="POST">
					@csrf
					<div class="modal-body">
						<div class="form-group mt-3 security-block">
							<label for="Authenticator Code">@lang('Authenticator Code')</label>
							<input type="hidden" name="key" value="{{ $secret }}">
							<div class="form-group">
								<input type="text" class="form-control" name="code"
									   placeholder="@lang('Enter Google Authenticator Code')">
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
						<button type="submit" class="btn btn-primary">@lang('Verify')</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div id="disableModal" class="modal fade" tabindex="-1" role="dialog"
		 aria-labelledby="primary-header-modalLabel"
		 aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title text-dark font-weight-bold"
						id="primary-header-modalLabel">@lang('Verify Your OTP to Disable')</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				<form action="{{ route('user.twoStepDisable') }}" method="POST">
					@csrf
					<div class="modal-body">
						<div class="form-group mt-3 security-block">
							<label for="Authenticator Code">@lang('Authenticator Code')</label>
							<div class="form-group">
								<input type="text" class="form-control" name="code"
									   placeholder="@lang('Enter Google Authenticator Code')">
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
						<button type="submit" class="btn btn-primary">@lang('Verify')</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
@push('extra_scripts')
	@if ($errors->any())
		@php
			$collection = collect($errors->all());
			$errors = $collection->unique();
		@endphp
		<script>
			"use strict";
			@foreach ($errors as $error)
			Notiflix.Notify.Failure("{{ trans($error) }}");
			@endforeach
		</script>
	@endif
	<script>
		function copyFunction() {
			var copyText = document.getElementById("referralURL");
			copyText.select();
			copyText.setSelectionRange(0, 99999);
			/*For mobile devices*/
			document.execCommand("copy");
			Notiflix.Notify.Success(`Copied: ${copyText.value}`);
		}
	</script>
@endpush
