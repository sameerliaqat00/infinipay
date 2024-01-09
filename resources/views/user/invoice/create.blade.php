@extends('user.layouts.master')
@section('page_title',__('Invoice Create'))

@section('content')
	<div class="main-content" id="invoice-app" v-cloak>
		<section class="section">
			<div class="section-header">
				<h1>@lang('Invoice Create')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Invoice Create')</div>
				</div>
			</div>

			<div class="row ">
				<div class="col-md-12">
					<div class="bd-callout bd-callout-primary mx-2">
						<i class="fa-3x fas fa-info-circle text-primary"></i> @lang(@$template->description->short_description)
					</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row justify-content-md-center">
						<div class="col-md-6">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Invoice Create')</h6>
								</div>
								<div class="card-body">
									<form action="">
										<div class="row">
											<div class="form-group col-md-6">
												<label for="">@lang('Customer Email')</label>
												<input type="email" name="email" v-model="invoice.customer_email"
													   v-on:keyup="customerEmail" class="form-control">
												<span class="text-danger customer_email"></span>
											</div>
											<div class="form-group col-md-6">
												<label for="">@lang('Invoice Number')</label>
												<input type="text" class="form-control" v-model="invoice.invoice_number"
													   v-on:keyup="invoiceNumber" placeholder=""/>
												<span class="text-danger invoice_number"></span>
											</div>
										</div>
										<div class="row">
											<div class="col-md-6">
												<div class="form-group search-currency-dropdown">
													<label for="currency">@lang('Currency')</label>
													<a href="javascript:void(0)" title="charges and limits"
													   data-target="#chargeLimit" data-toggle="modal"
													   v-if="invoice.currency != ''"><i
															class="fa fa-info-circle"></i></a>
													<select id="currency" name="currency" v-model="invoice.currency"
															@change="onChange($event)"
															class="form-control form-control-sm">
														<option v-for="(obj, index) in currencies" :value="obj.id"
																:currencySymbol="obj.symbol" :currencycode="obj.code">
															@{{obj.code}} - @{{
															obj.name }}
														</option>
													</select>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="form-group col-12">
												<label for="note">@lang('Note')</label>
												<textarea
													class="form-control"
													v-model="invoice.note"
													placeholder="@lang('Payment request note (optional)')"
													cols="30"
													rows="10"></textarea>
											</div>
										</div>
										<div class="row">
											<div class="form-group col-12">
												<label class="mb-3" for="">@lang('Payment Frequency')</label>
												<div class="d-flex">
													<div class="form-check mr-3">
														<input
															class="form-check-input"
															type="radio"
															@click="payment('1')"
															id="flexRadioDefault1"
															:checked="invoice.payment == '1'"/>
														<label
															class="form-check-label"
															for="flexRadioDefault1">
															@lang('One time')
														</label>
													</div>
													<div class="form-check mr-3">
														<input
															class="form-check-input"
															type="radio"
															name="flexRadioDefault"
															@click="payment('2')"
															id="flexRadioDefault2"
															:checked="invoice.payment == '2'"/>
														<label
															class="form-check-label"
															for="flexRadioDefault2">
															@lang('Weekly')
														</label>
													</div>
													<div class="form-check me-3">
														<input
															class="form-check-input"
															type="radio"
															name="flexRadioDefault"
															@click="payment('3')"
															id="flexRadioDefault3"
															:checked="invoice.payment == '3'"/>
														<label
															class="form-check-label"
															for="flexRadioDefault3">
															@lang('Monthly')
														</label>
													</div>

												</div>
											</div>
										</div>
										<div class="row">
											<div class="form-group col-md-6" v-if="invoice.payment == 1">
												<label for="">@lang('Due Date')</label>
												<date-picker v-model="invoice.due_date" :config="options"></date-picker>
												<span class="text-danger due_date"></span>
											</div>
										</div>
										<div class="row">
											<div class="form-group col-md-6"
												 v-if="invoice.payment == 2 || invoice.payment == 3">
												<label for="">@lang('Number of payments')</label>
												<input
													type="number"
													v-model="invoice.num_payment"
													class="form-control"
													placeholder=""/>
												<span class="text-danger num_payment"></span>
											</div>
											<div class="form-group col-md-6"
												 v-if="invoice.payment == 2 || invoice.payment == 3">
												<label for="">@lang('First Payment Date')</label>
												<date-picker v-model="invoice.first_pay_date"
															 :config="options"></date-picker>
												<span class="text-danger first_pay_date"></span>
											</div>
										</div>
										<div class="row  float-right">
											<div class="col-md-12">
												<button
													type="button"
													@click="makeEmptyItem"
													class="btn btn-primary btn-sm btn-block add-service-btn"
													data-target="#addService"
													data-toggle="modal">
													@lang('add services')
												</button>
											</div>
										</div>
									</form>
									@include('user.invoice.serviceList')
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Preview')</h6>
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
		@include('user.invoice.modalForm')
	</div>
@endsection
@push('extra_scripts')
	<script src="{{ asset('assets/dashboard/js/invoicejs/moment@2.22.min.js') }}"></script>
	<script src="{{ asset('assets/dashboard/js/invoicejs/bootstrap-datetimepicker.min.js') }}"></script>
	<link rel="stylesheet" href="{{ asset('assets/dashboard/css/invoicecss/bootstrap-datetimepicker.min.css') }}">
	<script src="{{ asset('assets/dashboard/js/invoicejs/vue-bootstrap-datetimepicker@5.min.js') }}"></script>
@endpush
@section('scripts')
	<script>
		"use strict";
		Vue.component('date-picker', VueBootstrapDatetimePicker);
		var newApp = new Vue({
			el: "#invoice-app",
			data: {
				date: new Date(),
				options: {
					format: 'DD/MM/YYYY',
					useCurrent: false,
				},
				item: {
					invoice_tab: "",
					themeName: "",
					frontColor: "",
					title: '',
					invoiceNumber: '',
					description: '',
					price: '',
					quantity: '',
					customer: []
				},
				currencies: [], symbol: '', code: '',
				subtotal: 0, maxLimit: '', minLimit: '', charges: '',
				oldPrice: 0,
				oldQuantity: 0,
				taxRate: 0,
				vatRate: 0,
				tax: 0,
				vat: 0,
				grandTotal: 0,
				customers: [], clickBtn: '',
				itemId: '',
				showModal: false,
				title_error: '',
				description_error: '',
				price_error: '',
				quantity_error: '',
				customer_email_error: '', due_date_error: '',
				invoice: {
					invoice_number: '', payment: '', due_date: '', num_payment: '', currency: '',
					first_pay_date: '', customer_email: '', note: '', items: []
				},
				items: {title: '', description: '', price: '', quantity: ''},
			},
			mounted() {
				this.item.invoice_tab = 1;
				this.item.themeName = 'city-lights';
				this.item.frontColor = 'black';
				this.invoice.payment = 1;
				this.currencies = @json($currencies);
			},
			methods: {
				toggleModal() {
					this.showModal = !this.showModal;
				},
				saveInvoice(buttonName) {
					if (buttonName == 'send') {
						this.saveAndSendError()
					}

					let invoice = this.invoice;
					invoice.customer_id = this.item.customer.id;
					invoice.invoice_tab = this.item.invoice_tab;
					invoice.theme_name = this.item.themeName;
					invoice.front_color = this.item.frontColor;
					invoice.subtotal = this.subtotal;
					invoice.tax = this.tax;
					invoice.taxRate = this.taxRate;
					invoice.vat = this.vat;
					invoice.vatRate = this.vatRate;
					invoice.garndtotal = this.grandTotal;
					invoice.button_name = buttonName;


					axios.post("{{ route('invoice.store') }}", this.invoice)
						.then(function (response) {
							if (response.data.status == 'success') {
								window.location.href = response.data.url;
							}
						})
						.catch(function (error) {
							let errors = error.response.data;
							errors = errors.errors
							for (let err in errors) {
								let selector = document.querySelector("." + err);
								if (selector) {
									selector.innerText = `${errors[err]}`;
								}
							}
						});
				},
				onChange(event) {
					this.symbol = event.target.options[event.target.options.selectedIndex].getAttribute('currencySymbol');
					this.code = event.target.options[event.target.options.selectedIndex].getAttribute('currencyCode');

					let currency = {};
					currency.id = event.target.value;

					var _this = this;
					axios.post("{{ route('currency.check') }}", currency)
						.then(function (response) {
							if (response.data.status == 'success') {
								_this.maxLimit = response.data.value.max_limit;
								_this.minLimit = response.data.value.min_limit;
								_this.charges = response.data.value.fixed_charge + '+' + response.data.value.percentage_charge + '%';
							}
						})
						.catch(function (error) {
							let errors = error.response.data;
							errors = errors.errors
							for (let err in errors) {
								let selector = document.querySelector("." + err);
								if (selector) {
									selector.innerText = `${errors[err]}`;
								}
							}
						});

				},
				payment(payment) {
					this.invoice.payment = payment;
				},
				editItem(index) {
					this.itemId = index;

					this.item.title = this.invoice.items[index].title;
					this.item.description = this.invoice.items[index].description;
					this.item.price = this.invoice.items[index].price;
					this.item.quantity = this.invoice.items[index].quantity;

					this.calculateSubtotalEdit(this.item.price, 0, this.item.quantity)
				},
				addServices() {
					this.serviceError();
					if (this.item.title && this.item.price && !isNaN(this.item.price) && this.item.quantity && !isNaN(this.item.quantity)) {
						this.invoice.items.push({
							title: this.item.title,
							description: this.item.description,
							price: this.item.price,
							quantity: this.item.quantity
						});

						this.calculateSubtotal(this.item.price, this.item.quantity)
						this.taxVatall();
						$('#addService').modal('hide');
						this.makeEmptyItem();
						this.showModal = false;
					}
				},
				editService() {
					this.serviceError();
					if (this.item.title && this.item.description && this.item.price && !isNaN(this.item.price) && this.item.quantity && !isNaN(this.item.quantity)) {
						this.invoice.items.splice(this.itemId, 1, {
							title: this.item.title,
							description: this.item.description,
							price: this.item.price,
							quantity: this.item.quantity
						});

						this.calculateSubtotalEdit(this.oldPrice, this.item.price, this.oldQuantity, this.item.quantity);
						this.makeEmptyItem();
						$('#editModal').modal('hide');
					}
				},
				removeItem(index) {
					this.itemId = index;
					this.item.price = this.invoice.items[index].price;
					this.item.quantity = this.invoice.items[index].quantity;
					this.calculateSubtotalDelete(this.item.price, this.item.quantity)
					this.taxVatall();
					this.invoice.items.splice(index, 1);
				},
				calculateSubtotal(price, quantity) {
					var total = parseFloat(price) * parseInt(quantity);
					this.subtotal += total;
					this.grandTotal = this.subtotal;
				},
				calculateSubtotalEdit(oldPrice, newPrice, oldQuantity, newQuantity) {
					this.oldPrice = oldPrice;
					this.oldQuantity = oldQuantity;
					var newPrice = newPrice;
					var newQuantity = newQuantity;

					var oldTotal = parseFloat(this.oldPrice) * parseInt(this.oldQuantity);

					var newTotal = newPrice * newQuantity;

					var sub = parseFloat(this.subtotal) - oldTotal;

					var add = sub + newTotal;
					if (!isNaN(add)) {
						this.subtotal = add
						this.taxVatall();
					}
				},
				taxVatall() {
					this.tax = (parseFloat(this.taxRate) * parseFloat(this.subtotal) / 100);
					this.vat = (parseFloat(this.vatRate) * parseFloat(this.subtotal) / 100);
					this.totalCalculation(this.tax, this.vat);
				},
				calculateSubtotalDelete(price, quantity) {
					var oldTotal = parseFloat(price) * parseInt(quantity);
					this.subtotal -= oldTotal;
					this.tax = 0;
					this.vat = 0;
					this.totalCalculation(0, 0)
				},
				calculateTax(tax) {
					this.tax = tax.target.value
					this.tax = (parseFloat(this.tax) * parseFloat(this.subtotal) / 100);
					this.taxRate = tax.target.value;
					this.totalCalculation(this.tax, this.vat);
				},
				calculateVat(vat) {
					this.vat = vat.target.value
					this.vat = (parseFloat(this.vat) * parseFloat(this.subtotal) / 100);
					this.vatRate = vat.target.value;
					this.totalCalculation(this.tax, this.vat);
				},
				totalCalculation(tax, vat) {
					this.grandTotal = parseFloat(this.subtotal) + parseFloat(tax) + parseFloat(vat);
				},
				invoiceNumber(invoice) {
					this.item.invoiceNumber = invoice.target.value

				},
				customerEmail(invoice) {
					this.invoice.customer_email = invoice.target.value;
				},
				sendInvoice() {
					this.invoice.customer_email = this.item.customer.email_address;
				},
				serviceError() {
					if (!this.item.title) {
						this.title_error = 'Title is required';
					}

					if (!this.item.price) {
						this.price_error = 'Price is required';
					}

					if (isNaN(this.item.price)) {
						this.price_error = 'Invalid Price';
					}

					if (!this.item.quantity) {
						this.quantity_error = 'Quantity is required';
					}

					if (isNaN(this.item.quantity)) {
						this.quantity_error = 'Quantity Price';
					}
				},
				saveAndSendError() {
					if (!this.invoice.customer_email) {
						this.customer_email_error = 'Email is required';
					}
					if (this.maxLimit < this.grandTotal) {
						Notiflix.Notify.Failure("Gradtotal Must e less than Max Limit");
					}
					if (this.minLimit > this.grandTotal) {
						Notiflix.Notify.Failure("Gradtotal Must e large than Min Limit");
					}
				},
				makeEmptyItem() {
					this.item.title = '';
					this.item.description = '';
					this.item.price = '';
					this.item.quantity = '';
					this.makeEmptyError();
				},
				makeEmptyError() {
					this.title_error = '';
					this.description_error = '';
					this.price_error = '';
					this.quantity_error = '';
				}
			},
			filters: {
				decimalFiltered(value) {
					if (value) {
						return value.toFixed(2)
					}
				}
			},
		})
	</script>
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
@endsection
