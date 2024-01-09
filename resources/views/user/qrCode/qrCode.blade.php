@extends('user.layouts.master')
@section('page_title',__('QR Code'))
@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('QR Code')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('QR Code')</div>
				</div>
			</div>
			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row justify-content-md-center">
						<div class="col-md-6">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('QR Code')</h6>
								</div>
								<div class="card-body">
									<div class="row">
										<div class="col text-center">
											<input type="hidden" id="qrUrl"
												   value="{{route('public.qr.Payment',optional(auth()->user())->qr_link)}}">
											<div id="qrcode" class="mx-auto mb-3">

											</div>
											<a href="" id="download-qr"
											   class="custom-btn d-block btn-block mt-3 py-2 download-qr"
											   download="{{ auth()->user()->name . '.png' }}">
												<i class="fas fa-download"></i> {{ __('Download') }}
											</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>

@endsection
@push('extra_scripts')
	<script src="{{ asset('assets/dashboard/js/qrjs2.min.js') }}"></script>
	<script>
		var qr = QRCode.generatePNG(document.getElementById('qrUrl').value, {
			ecclevel: "M",
			format: "html",
			margin: 4,
			modulesize: 8
		});

		var img = document.createElement("img");
		img.src = qr;
		document.getElementById('qrcode').appendChild(img);

		//For download
		var download = document.getElementById('download-qr').href = qr;
	</script>
@endpush

