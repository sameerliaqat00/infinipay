@extends('user.layouts.master')
@section('page_title',__('Dashboard'))

@push('extra_styles')
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/dashboard/css/daterangepicker.css') }}"/>
@endpush

@section('content')
	<!-- Main Content -->
	<div class="main-content" id="firebase-app">
		<section class="section wallet-section">
			<div class="section-header">
				<h1>@lang('Dashboard')</h1>
			</div>
		</section>
		<div v-if="user_foreground == '1' || user_background == '1'">
			<div v-if="notificationPermission == 'default' && !is_notification_skipped" v-cloak>
				<div class="media align-items-center d-flex justify-content-between alert alert-warn mb-4">
					<div><i
							class="fas fa-exclamation-triangle"></i> @lang('Do not miss any single important notification! Allow your
                        browser to get instant push notification')
						<button class="btn btn-primary ml-3" id="allow-notification">@lang('Allow me')</button>
					</div>
					<button class="close-btn pt-1" @click.prevent="skipNotification"><i class="fas fa-times"></i>
					</button>
				</div>
			</div>
		</div>
		<div v-if="notificationPermission == 'denied' && !is_notification_skipped" v-cloak>
			<div class="media align-items-center d-flex justify-content-between alert alert-warn mb-4">
				<div><i
						class="fas fa-exclamation-triangle"></i> @lang('Please allow your browser to get instant push notification.
                        Allow it from
                        notification setting.')
				</div>
				<button class="close-btn pt-1" @click.prevent="skipNotification"><i class="fas fa-times"></i>
				</button>
			</div>
		</div>
		@if($kyc)
			@if($kyc->status ==  1 && \Illuminate\Support\Facades\Auth::user()->kyc_verified != 2)
				<div class="media align-items-center d-flex justify-content-between alert alert-warn mb-4">
					<div><i
							class="fas fa-exclamation-triangle"></i> @lang('You have information to submit in verification center.')
					</div>
					<a href="{{route('user.kycShow')}}"
					   class="btn btn-primary">@lang('Submit Now')</a>
				</div>
			@endif
		@endif

		<div class="section-body">
			<div class="row">
				<div class="col-lg-8 mb-4 order-0">
					<div class="card mb-4">
						<div class="d-flex align-items-end row">
							<div class="col-sm-7">
								<div class="card-body">
									<h5 class="card-title text-primary">@lang('Welcome') {{auth()->user()->name}}!
										🎉</h5>
									<p class="mb-">
										@lang('We are delighted to have you, and we hope you will have a great stay with us!')
									</p>
									<a href="{{route('fund.initialize')}}"
									   class="btn btn-outline-primary">@lang('Add Fund')</a>
								</div>
							</div>
							<div class="col-sm-5 text-center text-sm-left">
								<div class="card-body pb-0 px-0 px-md-4 text-right">
									<img
										src="https://demos.themeselection.com/sneat-bootstrap-html-admin-template-free/assets/img/illustrations/man-with-laptop-light.png"
										height="140"
										alt="View Badge User"
										data-app-dark-img="illustrations/man-with-laptop-dark.png"
										data-app-light-img="illustrations/man-with-laptop-light.png"
									/>
								</div>
							</div>
						</div>
					</div>

					<div class="card">
						<div class="card-header">
							<h5>@lang('Quick Links')</h5>
						</div>
						<div class="card-body">
							<div class="row">
								<div class="col-xl-2 col-lg-4 col-md-3 col-6">
									<div class="quick-link text-center">
										<a href="{{route('fund.initialize')}}"
										   class="btn btn-icon icon-left btn-primary py-3 w-100">
											<div class="icon">
												<i class="fas fa-wallet"></i>
											</div>
											<span>@lang('Add money')</span>
										</a>
									</div>
								</div>
								<div class="col-xl-2 col-lg-4 col-md-3 col-6">
									<div class="quick-link text-center">
										<a href="{{route('payout.request')}}"
										   class="btn btn-icon icon-left btn-primary py-3 w-100">
											<div class="icon">
												<i class="fas fa-money-bill-alt"></i>
											</div>
											<span>@lang('Money Out')</span>
										</a>
									</div>
								</div>
								<div class="col-xl-2 col-lg-4 col-md-3 col-6">
									<div class="quick-link text-center">
										<a href="{{route('pay.bill')}}"
										   class="btn btn-icon icon-left btn-primary py-3 w-100">
											<div class="icon">
												<i class="fas fa-shopping-bag"></i>
											</div>
											<span>@lang('Bill Payment')</span>
										</a>
									</div>
								</div>
								<div class="col-xl-2 col-lg-4 col-md-3 col-6">
									<div class="quick-link text-center">
										<a href="{{route('exchange.initialize')}}"
										   class="btn btn-icon icon-left btn-primary py-3 w-100">
											<div class="icon">
												<i class="fas fa-exchange-alt"></i>
											</div>
											<span>@lang('Exchange')</span>
										</a>
									</div>
								</div>
								<div class="col-xl-2 col-lg-4 col-md-3 col-6">
									<div class="quick-link text-center">
										<a href="{{route('voucher.createRequest')}}"
										   class="btn btn-icon icon-left btn-primary py-3 w-100">
											<div class="icon">
												<i class="fas fa-tags"></i>
											</div>
											<span>@lang('Create Voucher')</span>
										</a>
									</div>
								</div>
								<div class="col-xl-2 col-lg-4 col-md-3 col-6">
									<div class="quick-link text-center">
										<a href="{{route('invoice.create')}}"
										   class="btn btn-icon icon-left btn-primary py-3 w-100">
											<div class="icon">
												<i class="fas fa-file-invoice"></i>
											</div>
											<span>@lang('Invoice')</span>
										</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-4 col-md-12 order-1">
					<div class="row">
						@if($wallets)
							@foreach($wallets as $wallet)
								<div class="col-lg-6 col-md-4 col-sm-6">
									<div class="card wallet-card">
										<div class="card-body">
											<div
												class="card-title d-flex align-items-start justify-content-between">
												<div
													class="avatar d-flex align-items-center justify-content-center text-white">
													<img
														src="{{ getFile( config('location.currencyLogo.path').@$wallet['currency']['logo'] ) }}"
														alt="{{ __(@$wallet['currency']['code']) }}"/>
												</div>
											</div>
											<span>{{ __(@$wallet['currency']['name']) }}</span>
											<h3 class="card-title text-nowrap my-2">{{ __(@$wallet['currency']['symbol']) }} {{ (getAmount(@$wallet['totalBalance'])) }}</h3>
										</div>
									</div>
								</div>
							@endforeach
						@endif
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-9">
					@if($basic->transfer)
						<!---------- User Send Money Summary -------------->
						<div class="row mb-3">
							<div class="col-md-12">
								<h6 class="mb-3 text-darku">@lang('User Send Money Summary')</h6>
							</div>
							<div class="col-lg-3 col-md-6 col-sm-6 col-12">
								<div class="card card-statistic-1 shadow-sm">
									<div class="card-icon bg-danger">
										<i class="fas fa-calendar-check"></i>
									</div>
									<div class="card-wrap">
										<div class="card-header">
											<h4>@lang('This Year Send Money')</h4>
										</div>
										<div class="card-body">
											{{ __($basic->currency_symbol) }}
											{{ getAmount($transfer['transfer_1_year'],2) }}
										</div>
									</div>
								</div>
							</div>
							<div class="col-lg-3 col-md-6 col-sm-6 col-12">
								<div class="card card-statistic-1 shadow-sm">
									<div class="card-icon bg-danger">
										<i class="far fa-calendar"></i>
									</div>
									<div class="card-wrap">
										<div class="card-header">
											<h4>@lang("Last 30 Days Send Money")</h4>
										</div>
										<div class="card-body">
											{{ __($basic->currency_symbol) }}
											{{ (getAmount($transfer['transfer_30_days'],2)) }}
										</div>
									</div>
								</div>
							</div>
							<div class="col-lg-3 col-md-6 col-sm-6 col-12">
								<div class="card card-statistic-1 shadow-sm">
									<div class="card-icon bg-danger">
										<i class="far fa-calendar-alt"></i>
									</div>
									<div class="card-wrap">
										<div class="card-header">
											<h4>@lang("Last 7 Days Send Money")</h4>
										</div>
										<div class="card-body">
											{{ __($basic->currency_symbol) }}
											{{ (getAmount($transfer['transfer_7_days'],2)) }}
										</div>
									</div>
								</div>
							</div>
							<div class="col-lg-3 col-md-6 col-sm-6 col-12">
								<div class="card card-statistic-1 shadow-sm">
									<div class="card-icon bg-danger">
										<i class="fas fa-calendar-minus"></i>
									</div>
									<div class="card-wrap">
										<div class="card-header">
											<h4>@lang("Today Send Money")</h4>
										</div>
										<div class="card-body">
											{{ __($basic->currency_symbol) }}
											{{ (getAmount($transfer['transfer_today'],$basic->fraction_number)) }}
										</div>
									</div>
								</div>
							</div>
						</div>
					@endif
					@if($basic->request)
						<!---------- User Request Money Summary -------------->
						<div class="row mb-3">
							<div class="col-md-12">
								<h6 class="mb-3 text-darku">@lang('User Request Money Summary')</h6>
							</div>
							<div class="col-lg-3 col-md-6 col-sm-6 col-12">
								<div class="card card-statistic-1 shadow-sm">
									<div class="card-icon bg-warning img-div">
										<i class="fas fa-calendar-check"></i>
									</div>
									<div class="card-wrap">
										<div class="card-header">
											<h4>@lang('This Year Request Money')</h4>
										</div>
										<div class="card-body">
											{{ __($basic->currency_symbol) }}
											{{ (getAmount($requestMoney['request_money_1_year'],2)) }}
										</div>
									</div>
								</div>
							</div>
							<div class="col-lg-3 col-md-6 col-sm-6 col-12">
								<div class="card card-statistic-1 shadow-sm">
									<div class="card-icon bg-warning">
										<i class="far fa-calendar"></i>
									</div>
									<div class="card-wrap">
										<div class="card-header">
											<h4>@lang("Last 30 Days Request Money")</h4>
										</div>
										<div class="card-body">
											{{ __($basic->currency_symbol) }}
											{{ (getAmount($requestMoney['request_money_30_days'],2)) }}
										</div>
									</div>
								</div>
							</div>
							<div class="col-lg-3 col-md-6 col-sm-6 col-12">
								<div class="card card-statistic-1 shadow-sm">
									<div class="card-icon bg-warning">
										<i class="far fa-calendar-alt"></i>
									</div>
									<div class="card-wrap">
										<div class="card-header">
											<h4>@lang("Last 7 Days Request Money")</h4>
										</div>
										<div class="card-body">
											{{ __($basic->currency_symbol) }}
											{{ (getAmount($requestMoney['request_money_7_days'],2)) }}
										</div>
									</div>
								</div>
							</div>
							<div class="col-lg-3 col-md-6 col-sm-6 col-12">
								<div class="card card-statistic-1 shadow-sm">
									<div class="card-icon bg-warning">
										<i class="fas fa-calendar-minus"></i>
									</div>
									<div class="card-wrap">
										<div class="card-header">
											<h4>@lang("Today Request Money")</h4>
										</div>
										<div class="card-body">
											{{ __($basic->currency_symbol) }}
											{{ (getAmount($requestMoney['request_money_today'],2)) }}
										</div>
									</div>
								</div>
							</div>
						</div>
					@endif
					@if($basic->voucher)
						<!---------- User Voucher Payment Summary -------------->
						<div class="row mb-3">
							<div class="col-md-12">
								<h6 class="mb-3 text-darku">@lang('User Voucher Payment Summary')</h6>
							</div>
							<div class="col-lg-3 col-md-6 col-sm-6 col-12">
								<div class="card card-statistic-1 shadow-sm">
									<div class="card-icon bg-dark img-div">
										<i class="fas fa-calendar-check"></i>
									</div>
									<div class="card-wrap">
										<div class="card-header">
											<h4>@lang("This Year Voucher Payment")</h4>
										</div>
										<div class="card-body">
											{{ __($basic->currency_symbol) }}
											{{ (getAmount($voucher['voucher_1_year'],2)) }}
										</div>
									</div>
								</div>
							</div>
							<div class="col-lg-3 col-md-6 col-sm-6 col-12">
								<div class="card card-statistic-1 shadow-sm">
									<div class="card-icon bg-dark">
										<i class="far fa-calendar"></i>
									</div>
									<div class="card-wrap">
										<div class="card-header">
											<h4>@lang("Last 30 Days Voucher Payment")</h4>
										</div>
										<div class="card-body">
											{{ __($basic->currency_symbol) }}
											{{ (getAmount($voucher['voucher_30_days'],2)) }}
										</div>
									</div>
								</div>
							</div>
							<div class="col-lg-3 col-md-6 col-sm-6 col-12">
								<div class="card card-statistic-1 shadow-sm">
									<div class="card-icon bg-dark">
										<i class="far fa-calendar-alt"></i>
									</div>
									<div class="card-wrap">
										<div class="card-header">
											<h4>@lang('Last 7 Days Voucher Payment')</h4>
										</div>
										<div class="card-body">
											{{ __($basic->currency_symbol) }}
											{{ getAmount($voucher['voucher_7_days'],2) }}
										</div>
									</div>
								</div>
							</div>
							<div class="col-lg-3 col-md-6 col-sm-6 col-12">
								<div class="card card-statistic-1 shadow-sm">
									<div class="card-icon bg-dark">
										<i class="fas fa-calendar-minus"></i>
									</div>
									<div class="card-wrap">
										<div class="card-header">
											<h4>@lang('Today Voucher Payment')</h4>
										</div>
										<div class="card-body">
											{{ __($basic->currency_symbol) }}
											{{ getAmount($voucher['voucher_today'],2) }}
										</div>
									</div>
								</div>
							</div>
						</div>
					@endif
				</div>
				<div class="col-lg-3 col-md-6 mt-4">
					<div class="card card-success">
						<div class="card-body text-center px-0 pb-0">
							<h5 class="mb-0">@lang('Qr code')</h5>
							<div class="qr-box">
								<input
									type="hidden"
									id="qrUrl"
									value="{{route('public.qr.Payment',optional(auth()->user())->qr_link)}}"/>
								<div id="qrcode"></div>
							</div>
						</div>
						<div class="card-footer pt-0">
							<a href="" class="btn btn-success w-100" id="download-qr"
							   download="{{ auth()->user()->name . '.png' }}">
								<i class="fas fa-download"></i> @lang('Download')
							</a>
						</div>
					</div>
				</div>
			</div>
			<!---------- Transaction Summary -------------->
			<div class="row mb-3">
				<div class="col-md-12">
					<div class="card mb-4 shadow-sm">
						<div
							class="card-header py-3 d-flex flex-wrap flex-row align-items-center justify-content-between">
							<h5 class="card-title">@lang('Transaction Summary')</h5>
							<input type="button" class="btn btn-sm btn-primary" name="daterange" value=""/>
						</div>
						<div class="card-body">
							<div>
								<canvas id="line-chart" height="80"></canvas>
							</div>
						</div>
					</div>
				</div>
			</div>

			@if($basic->invoice)
				<!---------- User Invoice Payment Summary -------------->
				<div class="row mb-3">
					<div class="col-md-12">
						<h6 class="mb-3 text-darku">@lang('User Invoice Payment Summary')</h6>
					</div>
					<div class="col-lg-3 col-md-6 col-sm-6 col-12">
						<div class="card card-statistic-1 shadow-sm">
							<div class="card-icon bg-primary img-div">
								<i class="fas fa-calendar-check"></i>
							</div>
							<div class="card-wrap">
								<div class="card-header">
									<h4>@lang("This Year Invoice Payment")</h4>
								</div>
								<div class="card-body">
									{{ __($basic->currency_symbol) }}
									{{ (getAmount($invoice['invoice_1_year'],2)) }}
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-3 col-md-6 col-sm-6 col-12">
						<div class="card card-statistic-1 shadow-sm">
							<div class="card-icon bg-primary">
								<i class="far fa-calendar"></i>
							</div>
							<div class="card-wrap">
								<div class="card-header">
									<h4>@lang("Last 30 Days Invoice Payment")</h4>
								</div>
								<div class="card-body">
									{{ __($basic->currency_symbol) }}
									{{ (getAmount($invoice['invoice_30_days'],2)) }}
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-3 col-md-6 col-sm-6 col-12">
						<div class="card card-statistic-1 shadow-sm">
							<div class="card-icon bg-primary">
								<i class="far fa-calendar-alt"></i>
							</div>
							<div class="card-wrap">
								<div class="card-header">
									<h4>@lang('Last 7 Days Invoice Payment')</h4>
								</div>
								<div class="card-body">
									{{ __($basic->currency_symbol) }}
									{{ getAmount($invoice['invoice_7_days'],2) }}
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-3 col-md-6 col-sm-6 col-12">
						<div class="card card-statistic-1 shadow-sm">
							<div class="card-icon bg-primary">
								<i class="fas fa-calendar-minus"></i>
							</div>
							<div class="card-wrap">
								<div class="card-header">
									<h4>@lang('Today Invoice Payment')</h4>
								</div>
								<div class="card-body">
									{{ __($basic->currency_symbol) }}
									{{ getAmount($invoice['invoice_today'],2) }}
								</div>
							</div>
						</div>
					</div>
				</div>
			@endif
			@if($basic->store)
				<!---------- User Store Summary -------------->
				<div class="row mb-3">
					<div class="col-md-12">
						<h6 class="mb-3 text-darku">@lang('User Store Summary')</h6>
					</div>
					<div class="col-lg-3 col-md-6 col-sm-6 col-12">
						<div class="card card-statistic-1 shadow-sm">
							<div class="card-icon bg-success img-div">
								<i class="fas fa-calendar-check"></i>
							</div>
							<div class="card-wrap">
								<div class="card-header">
									<h4>@lang("Total Stores")</h4>
								</div>
								<div class="card-body">
									{{$totalStores}}
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-3 col-md-6 col-sm-6 col-12">
						<div class="card card-statistic-1 shadow-sm">
							<div class="card-icon bg-success">
								<i class="far fa-calendar-alt"></i>
							</div>
							<div class="card-wrap">
								<div class="card-header">
									<h4>@lang('Total Products')</h4>
								</div>
								<div class="card-body">
									{{$totalProducts}}
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-3 col-md-6 col-sm-6 col-12">
						<div class="card card-statistic-1 shadow-sm">
							<div class="card-icon bg-success">
								<i class="fas fa-calendar-minus"></i>
							</div>
							<div class="card-wrap">
								<div class="card-header">
									<h4>@lang('Total Orders')</h4>
								</div>
								<div class="card-body">
									{{$totalOrders}}
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-3 col-md-6 col-sm-6 col-12">
						<div class="card card-statistic-1 shadow-sm">
							<div class="card-icon bg-success">
								<i class="far fa-calendar"></i>
							</div>
							<div class="card-wrap">
								<div class="card-header">
									<h4>@lang("Total Shipping Address")</h4>
								</div>
								<div class="card-body">
									{{$totalShippings}}
								</div>
							</div>
						</div>
					</div>
				</div>
			@endif
		</div>
		</section>
	</div>
@endsection

@push('extra_scripts')
	<script src="{{ asset('assets/dashboard/js/Chart.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('assets/dashboard/js/moment.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('assets/dashboard/js/daterangepicker.min.js') }}"></script>
	<script src="{{ asset('assets/dashboard/js/qrjs2.min.js') }}"></script>
@endpush

@section('scripts')
	<script>
		'use strict';
		$(document).ready(function () {
			$('input[name="daterange"]').daterangepicker({
				opens: 'left',
				startDate: moment().startOf('month'),
				endDate: moment().endOf('month'),
				locale: {
					format: 'MMMM D, YYYY'
				}
			}, function (start, end, label) {
				getTransaction(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
			});

			function getTransaction(start, end) {
				$.ajax({
					method: "GET",
					url: "{{ route('user.get.transaction.chart') }}",
					dataType: "json",
					data: {
						'start': start,
						'end': end,
					}
				})
					.done(function (response) {
						new Chart(document.getElementById("line-chart"), {
							type: 'line',
							data: {
								labels: response.labels,
								datasets: [
										@if($basic->deposit)
									{
										data: response.dataDeposit,
										label: "Deposit",
										borderColor: "#1abc9c",
										fill: false
									},
										@endif
									{
										data: response.dataFund,
										label: "Add Fund",
										borderColor: "#00cec9",
										fill: false
									},
										@if($basic->transfer)
									{
										data: response.dataTransfer,
										label: "Send Money",
										borderColor: "#3498db",
										fill: false
									},
										@endif
										@if($basic->request)
									{
										data: response.dataRequestMoney,
										label: "Request Money",
										borderColor: "#9b59b6",
										fill: false
									},
										@endif
										@if($basic->voucher)
									{
										data: response.dataVoucher,
										label: "Voucher",
										borderColor: "#6b7b98",
										fill: false
									},
										@endif
										@if($basic->invoice)
									{
										data: response.dataInvoice,
										label: "Invoice",
										borderColor: "#6777ef",
										fill: false
									},
										@endif
										@if($basic->redeem)
									{
										data: response.dataRedeem,
										label: "Redeem Code",
										borderColor: "#f39c12",
										fill: false
									},
										@endif
										@if($basic->escrow)
									{
										data: response.dataEscrow,
										label: "Escrow",
										borderColor: "#273c75",
										fill: false
									},
										@endif
										@if($basic->payout)
									{
										data: response.dataPayout,
										label: "Payout",
										borderColor: "#bdc3c7",
										fill: false
									},
										@endif
										@if($basic->exchange)
									{
										data: response.dataExchange,
										label: "Exchange",
										borderColor: "#e74c3c",
										fill: false
									},
										@endif
										@if($basic->escrow)
									{
										data: response.dataDispute,
										label: "Dispute",
										borderColor: "#8e44ad",
										fill: false
									},
										@endif
									{
										data: response.dataCommissionEntry,
										label: "Commission",
										borderColor: "#5444ad",
										fill: false
									},
								]
							}
						});
					});
			}

			new Chart(document.getElementById("line-chart"), {
				type: 'line',
				data: {
					labels: {!! json_encode($labels) !!},
					datasets: [
							@if($basic->deposit)
						{
							data: {!! json_encode($dataDeposit) !!},
							label: "Deposit",
							borderColor: "#1abc9c",
							fill: false
						},
							@endif
						{
							data: {!! json_encode($dataFund) !!},
							label: "Add Fund",
							borderColor: "#00cec9",
							fill: false
						},
							@if($basic->transfer)
						{
							data: {!! json_encode($dataTransfer) !!},
							label: "Send Money",
							borderColor: "#3498db",
							fill: false
						},
							@endif
							@if($basic->request)
						{
							data: {!! json_encode($dataRequestMoney) !!},
							label: "Request Money",
							borderColor: "#9b59b6",
							fill: false
						},
							@endif
							@if($basic->qr_payment)
						{
							data: {!! json_encode($dataQRPaymentAmount) !!},
							label: "QR Payment",
							borderColor: "#CCFB5D",
							fill: false
						},
							@endif
							@if($basic->voucher)
						{
							data: {!! json_encode($dataVoucher) !!},
							label: "Voucher",
							borderColor: "#6b7b98",
							fill: false
						},
							@endif
							@if($basic->invoice)
						{
							data: {!! json_encode($dataInvoice) !!},
							label: "Invoice",
							borderColor: "#6777ef",
							fill: false
						},
							@endif
							@if($basic->redeem)
						{
							data: {!! json_encode($dataRedeem) !!},
							label: "Redeem Code",
							borderColor: "#f39c12",
							fill: false
						},
							@endif
							@if($basic->escrow)
						{
							data: {!! json_encode($dataEscrow) !!},
							label: "Escrow",
							borderColor: "#273c75",
							fill: false
						},
							@endif
							@if($basic->payout)
						{
							data: {!! json_encode($dataPayout) !!},
							label: "Payout",
							borderColor: "#bdc3c7",
							fill: false
						},
							@endif
							@if($basic->exchange)
						{
							data: {!! json_encode($dataExchange) !!},
							label: "Exchange",
							borderColor: "#e74c3c",
							fill: false
						},
							@endif
							@if($basic->escrow)
						{
							data: {!! json_encode($dataDispute) !!},
							label: "Dispute",
							borderColor: "#8e44ad",
							fill: false
						},
							@endif

						{
							data: {!! json_encode($dataCommissionEntry) !!},
							label: "Commission",
							borderColor: "#5444ad",
							fill: false
						},
					]
				}
			});
		});

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
@endsection
@if($firebaseNotify)
	@push('extra_scripts')
		<script type="module">
			import {initializeApp} from "https://www.gstatic.com/firebasejs/9.17.1/firebase-app.js";
			import {
				getMessaging,
				getToken,
				onMessage
			} from "https://www.gstatic.com/firebasejs/9.17.1/firebase-messaging.js";

			const firebaseConfig = {
				apiKey: "{{$firebaseNotify->api_key}}",
				authDomain: "{{$firebaseNotify->auth_domain}}",
				projectId: "{{$firebaseNotify->project_id}}",
				storageBucket: "{{$firebaseNotify->storage_bucket}}",
				messagingSenderId: "{{$firebaseNotify->messaging_sender_id}}",
				appId: "{{$firebaseNotify->app_id}}",
				measurementId: "{{$firebaseNotify->measurement_id}}"
			};

			const app = initializeApp(firebaseConfig);
			const messaging = getMessaging(app);
			if ('serviceWorker' in navigator) {
				navigator.serviceWorker.register('{{ getProjectDirectory() }}' + `/firebase-messaging-sw.js`, {scope: './'}).then(function (registration) {
						requestPermissionAndGenerateToken(registration);
					}
				).catch(function (error) {
				});
			} else {
			}

			onMessage(messaging, (payload) => {
				if (payload.data.foreground || parseInt(payload.data.foreground) == 1) {
					const title = payload.notification.title;
					const options = {
						body: payload.notification.body,
						icon: payload.notification.icon,
					};
					new Notification(title, options);
				}
			});

			function requestPermissionAndGenerateToken(registration) {
				document.addEventListener("click", function (event) {
					if (event.target.id == 'allow-notification') {
						Notification.requestPermission().then((permission) => {
							if (permission === 'granted') {
								getToken(messaging, {
									serviceWorkerRegistration: registration,
									vapidKey: "{{$firebaseNotify->vapid_key}}"
								})
									.then((token) => {
										$.ajax({
											url: "{{ route('user.save.token') }}",
											method: "post",
											data: {
												token: token,
											},
											success: function (res) {
											}
										});
										window.newApp.notificationPermission = 'granted';
									});
							} else {
								window.newApp.notificationPermission = 'denied';
							}
						});
					}
				});
			}
		</script>
		<script>
			window.newApp = new Vue({
				el: "#firebase-app",
				data: {
					user_foreground: '',
					user_background: '',
					notificationPermission: Notification.permission,
					is_notification_skipped: sessionStorage.getItem('is_notification_skipped') == '1'
				},
				mounted() {
					this.user_foreground = "{{$firebaseNotify->user_foreground}}";
					this.user_background = "{{$firebaseNotify->user_background}}";
				},
				methods: {
					skipNotification() {
						sessionStorage.setItem('is_notification_skipped', '1');
						this.is_notification_skipped = true;
					}
				}
			});
		</script>
	@endpush
@endif
