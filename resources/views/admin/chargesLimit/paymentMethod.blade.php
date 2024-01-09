@extends('admin.layouts.master')
@section('page_title', __('Add Gateway Charge'))
@section('content')
<div class="main-content">
	<section class="section">
		<div class="section-header">
			<h1>@lang('Edit Charges & Limit')</h1>
			<div class="section-header-breadcrumb">
				<div class="breadcrumb-item active">
					<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
				</div>
				<div class="breadcrumb-item"><a href="{{ route('currency.index') }}">@lang('Currency')</a></div>
				<div class="breadcrumb-item">@lang('Payment Methods Charge')</div>
			</div>
		</div>

		<div class="section-body">
			<div class="container-fluid" id="container-wrapper">
				<div class="row justify-content-md-center">
					<div class="col-lg-8">
						<div class="card mb-4">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
								<h6 class="m-0 font-weight-bold text-primary">@lang('Edit Charges & Limit For ') {{ __($currency->code) }}</h6>
								<a href="{{route('currency.index')}}" class="btn btn-sm btn-outline-primary"> <i
											class="fas fa-arrow-left"></i> @lang('Back')</a>
							</div>
							<div class="card-body" id="depositMethodArea">
								<div class="row">
									<div class="col-xl-3 col-lg-4">
										<div class="tabScroll">
											<div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist"
												 aria-orientation="vertical">
												@foreach($methods as $key => $method)
													<a class="nav-link media {{ $key == 0 ? 'active' : '' }}" data-toggle="pill"
													   href=".payment-method-form" role="tab"
													   @click.prevent="getCharge({{$currency->id}}, {{$method->id}})"
													   aria-selected="{{ $key == 0 ? 'true' : '' }}">
														<span class="tabImage">
															<img src="{{ asset('assets/upload/gateway/'.$method->image) }}" alt="@lang('Image')">
														</span>{{ __($method->name) }}
													</a>
												@endforeach
											</div>
										</div>
									</div>
									<div class="col-xl-9 col-lg-8">
										<div class="tab-content" id="v-pills-tabContent">
											<div class="tab-pane fade show active payment-method-form" role="tabpanel">
												<form action="#" method="post" @submit.prevent="updateCharge()">
													@csrf
													<div class="row">
														<div class="col-md-6">
															<div class="form-group">
																<label for="convention_rate">@lang('Convention Rate')</label>
																<div class="input-group input-group-sm">
																	<div class="input-group-prepend">
																		<span class="input-group-text">@lang('1 ') {{ __($currency->code) }} @lang(' =')</span>
																	</div>
																	<input type="text" placeholder="@lang('eg:- 0.00')"
																		   v-model="items.convention_rate"
																		   class="form-control"
																		   :class="{'is-invalid' : hasErrors.convention_rate.length}">
																	<div class="input-group-append" v-cloak>
																		<span class="input-group-text">@{{ items.method_currency }}</span>
																	</div>
																	<div :class="{'invalid-feedback' : hasErrors.convention_rate.length}">
																		@{{ hasErrors.convention_rate }}
																	</div>
																</div>
															</div>
														</div>
														<div class="col-md-6">
															<div class="form-group">
																<label for="min_limit">@lang('Minimum Deposit Limit')</label>
																<div class="input-group input-group-sm">
																	<input type="text" placeholder="@lang('eg:- 0.00')"
																		   v-model="items.min_limit"
																		   class="form-control"
																		   :class="{'is-invalid' : hasErrors.min_limit.length}">
																	<div class="input-group-append">
																		<span class="input-group-text">{{ __($currency->code) }}</span>
																	</div>
																	<div :class="{'invalid-feedback' : hasErrors.min_limit.length}">
																		@{{ hasErrors.min_limit }}
																	</div>
																</div>
															</div>
														</div>
														<div class="col-md-6">
															<div class="form-group">
																<label for="max_limit">@lang('Maximum Deposit Limit')</label>
																<div class="input-group input-group-sm">
																	<input type="text" placeholder="@lang('eg:- 0.00')"
																		   v-model="items.max_limit"
																		   class="form-control"
																		   :class="{'is-invalid' : hasErrors.max_limit.length}">
																	<div class="input-group-append">
																		<span class="input-group-text">{{ __($currency->code) }}</span>
																	</div>
																	<div :class="{'invalid-feedback' : hasErrors.max_limit.length}">
																		@{{ hasErrors.max_limit }}
																	</div>
																</div>
															</div>
														</div>
														<div class="col-md-6">
															<div class="form-group">
																<label for="percentage_charge">@lang('Percentage Charge')</label>
																<div class="input-group input-group-sm">
																	<input type="text" placeholder="@lang('eg:- 0.00')"
																		   v-model="items.percentage_charge"
																		   class="form-control"
																		   :class="{'is-invalid' : hasErrors.percentage_charge.length}">
																	<div class="input-group-append">
																		<span class="input-group-text">@lang('%')</span></div>
																	<div :class="{'invalid-feedback' : hasErrors.percentage_charge.length}">
																		@{{ hasErrors.percentage_charge }}
																	</div>
																</div>
															</div>
														</div>
														<div class="col-md-6">
															<div class="form-group">
																<label for="fixed_charge">@lang('Fixed Charge')</label>
																<div class="input-group input-group-sm">
																	<input type="text" placeholder="@lang('eg:- 0.00')"
																		   v-model="items.fixed_charge"
																		   class="form-control"
																		   :class="{'is-invalid' : hasErrors.fixed_charge.length}">
																	<div class="input-group-append">
																		<span class="input-group-text">{{ __($currency->code) }}</span>
																	</div>
																	<div :class="{'invalid-feedback' : hasErrors.fixed_charge.length}">
																		@{{ hasErrors.fixed_charge }}
																	</div>
																</div>
															</div>
														</div>
														<div class="col-md-6">
															<div class="form-group">
																<label for="status">@lang('Status')</label>
																<div class="input-group input-group-sm ml-4">
																	<input type="checkbox" class="custom-control-input"
																		   :class="{'is-invalid' : hasErrors.is_active.length}"
																		   id="is_active"
																		   v-model="items.is_active"
																		   :checked="items.is_active == 1">
																	<label class="custom-control-label"
																		   for="is_active">@lang('Enable Charge')</label>
																	<div :class="{'invalid-feedback' : hasErrors.is_active.length}">
																		@{{ hasErrors.is_active }}
																	</div>
																</div>
															</div>
														</div>
														<div class="col-md-12">
															<div class="form-group">
																<button type="submit"
																		class="btn btn-primary btn-sm btn-block">@lang('Save Changes')</button>
															</div>
														</div>
													</div>
												</form>
											</div>
										</div>
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
@section('scripts')
    <script>
        'use strict'
        let depositMethodArea = new Vue({
            el: "#depositMethodArea",
            data: {
                items: [],
                hasErrors: {
                    'convention_rate': '',
                    'max_limit': '',
                    'min_limit': '',
                    'percentage_charge': '',
                    'fixed_charge': '',
                    'is_active': ''
                },
            },
            beforeMount() {
                this.getCharge("{{@$currency->id}}", "{{@$methods[0]->id}}");
            },
            methods: {
                getCharge(currency_id, payment_method_id) {
                    let app = this;
                    let url = "{{ route('get.deposit.charge',['currency_id','payment_method_id']) }}";
                    url = url.replace('currency_id', currency_id);
                    url = url.replace('payment_method_id', payment_method_id);
                    this.setErrorBlank()
                    axios.get(url)
                        .then(function (res) {
                            app.items = res.data;
                        })
                },
                updateCharge() {
                    let app = this;
                    let input = this.items;
                    this.setErrorBlank()
                    axios.post("{{ route('set.deposit.charge') }}", input)
                        .then(function (response) {
                            Notiflix.Notify.Success(response.data.message);
                        })
                        .catch(function (errors) {
                            let getError = errors.response.data[0];
                            for (var error in getError) {
                                app.hasErrors[error] = getError[error][0];
                            }
                        });

                },
                setErrorBlank() {
                    this.hasErrors.convention_rate = '';
                    this.hasErrors.max_limit = '';
                    this.hasErrors.min_limit = '';
                    this.hasErrors.percentage_charge = '';
                    this.hasErrors.fixed_charge = '';
                    this.hasErrors.is_active = '';
                }
            }
        });
    </script>
@endsection
