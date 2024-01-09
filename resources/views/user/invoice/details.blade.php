@extends('user.layouts.master')
@section('page_title',__('Invoice View'))

@section('content')
	<div class="main-content" id="invoice-app" v-cloak>
		<section class="section">
			<div class="section-header">
				<h1>@lang('Invoice View')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Invoice View')</div>
				</div>
			</div>
			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row justify-content-md-center">
						<div class="col-md-4">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Invoice Info')</h6>
								</div>
								<div class="card-body">
									<div class="d-flex justify-content-between mb-3">
										<lable>@lang('Status')</lable>
										@if($invoice->status == null)
											<label class="badge badge-info">@lang('Unpaid')</label>
										@elseif($invoice->status == 'paid')
											<label class="badge badge-success">@lang('Paid')</label>
										@elseif($invoice->status == 'rejected')
											<label class="badge badge-danger">@lang('Rejected')</label>
										@endif
									</div>
									<div class="d-flex justify-content-between mb-3">
										<lable>@lang('Request Link')
										</lable>
										<a href="{{route('public.invoice.show',$invoice->has_slug)}}" target="_blank"
										   title="click">@lang('Invoice Link')</a>
									</div>
									@if($invoice->paid_at)
										<div class="d-flex justify-content-between mb-3">
											<lable>@lang('Paid At')</lable>
											<label>{{dateTime($invoice->paid_at,'d/m/Y')}}</label>
										</div>
									@endif

									@if($invoice->rejected_at)
										<div class="d-flex justify-content-between mb-3">
											<lable>@lang('Paid At')</lable>
											<label>{{dateTime($invoice->rejected_at,'d/m/Y')}}</label>
										</div>
									@endif
									@if($invoice->status == null && $invoice->reminder_at)
										<div class="d-flex justify-content-between mb-3">
											<lable>@lang('Last Remind At')</lable>
											<label>{{dateTime($invoice->reminder_at,'d/m/Y')}}</label>
										</div>
									@endif
									<div class="d-flex justify-content-between mb-3">
										<lable>@lang('Invoice Number')</lable>
										<label>{{$invoice->invoice_number}}</label>
									</div>
									<div class="d-flex justify-content-between mb-3">
										<lable>@lang('Invoice Issue Date')</lable>
										<label>{{dateTime($invoice->created_at,'d/m/Y')}}</label>
									</div>
									@if($invoice->charge_pay == 0)
										<hr>
										<div class="d-flex justify-content-between mb-3">
											<lable>@lang('Percent Charge') ({{$invoice->percentage+0}}%)</lable>
											<label
												class="text-danger">{{optional($invoice->currency)->symbol}}{{$invoice->charge_percentage}}</label>
										</div>
										<div class="d-flex justify-content-between mb-3">
											<lable>@lang('Fixed Charge')</lable>
											<label
												class="text-danger">{{optional($invoice->currency)->symbol}}{{$invoice->charge_fixed}}</label>
										</div>
										<div class="d-flex justify-content-between mb-3">
											<lable>@lang('Overall Charge')</lable>
											<label
												class="text-danger">{{optional($invoice->currency)->symbol}}{{$invoice->charge}}</label>
										</div>
									@endif
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Invoice View')</h6>
								</div>
								<div class="invoice-page preview">
									<div id="printableArea">
										@include('user.invoice.preview')
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
@section('scripts')
	<script>
		"use strict";
		var newApp = new Vue({
			el: "#invoice-app",
			data: {
				item: {
					invoiceNumber: "",
				},
				invoice: {
					customer_email: "",
					payment: "",
					first_pay_date: "", items: [], due_date: '', note: ''
				},
				symbol: "", subtotal: 0, taxRate: 0, tax: 0, vatRate: 0, vat: 0, grandTotal: 0
			},
			mounted() {
				this.item.invoiceNumber = "{{$invoice->invoice_number}}"
				this.invoice.customer_email = "{{$invoice->customer_email}}"
				this.invoice.payment = "{{$invoice->frequency}}"
				this.invoice.due_date = "{{$invoice->due_date}}"
				this.invoice.note = "{{$invoice->note}}"
				this.invoice.first_pay_date = "{{$invoice->due_date}}"
				this.invoice.items = @json($invoice->items);
				this.symbol = "{{optional($invoice->currency)->symbol}}"
				this.subtotal = "{{$invoice->subtotal}}"
				this.taxRate = "{{$invoice->tax_rate}}"
				this.tax = "{{$invoice->tax}}"
				this.vatRate = "{{$invoice->vat_rate}}"
				this.vat = "{{$invoice->vat}}"
				this.grandTotal = "{{$invoice->grand_total}}"
			},
			methods: {}
		})
	</script>
@endsection
