@extends('admin.layouts.master')
@section('page_title', __('Invoice Settings'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Invoice Settings')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Invoice Settings')</div>
				</div>
			</div>

			<div class="section-body">
				<div class="row mt-sm-4">
					<div class="col-12 col-md-4 col-lg-3">
						@include('admin.control_panel.components.sidebar', ['settings' => config('generalsettings.settings'), 'suffix' => 'Settings'])
					</div>
					<div class="col-12 col-md-8 col-lg-9">
						<div class="container-fluid" id="container-wrapper">
							<div class="row justify-content-md-center">
								<div class="col-lg-12">
									<div class="card mb-4 card-primary shadow">
										<div
											class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
											<h6 class="m-0 font-weight-bold text-primary">@lang('Invoice Settings')</h6>
										</div>
										<div class="card-body">
											<form action="{{ route('invoice.settings') }}" method="post">
												@csrf
												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<label>
																@lang('Who pay invoice charges?')<i
																	class="fas fa-info-circle ml-2"
																	data-toggle="tooltip" data-placement="top"
																	title="@lang('If enable sender then ')">
																</i>
															</label>
															<div class="selectgroup w-100">
																<label class="selectgroup-item">
																	<input type="radio" name="invoiceCharge" value="0"
																		   class="selectgroup-input" {{ old('invoiceCharge', $basicControl->invoice_charge) == 0 ? 'checked' : ''}}>
																	<span
																		class="selectgroup-button">@lang('Sender')</span>
																</label>
																<label class="selectgroup-item">
																	<input type="radio" name="invoiceCharge" value="1"
																		   class="selectgroup-input" {{ old('invoiceCharge', $basicControl->invoice_charge) == 1 ? 'checked' : ''}}>
																	<span
																		class="selectgroup-button">@lang('Receiver')</span>
																</label>
															</div>
															@error('invoiceAllow')
															<span class="text-danger" role="alert">
																<strong>{{ __($message) }}</strong>
															</span>
															@enderror
														</div>
													</div>
												</div>
												<input type="submit" id="submit"
													   class="btn btn-primary btn-sm btn-block"
													   value="@lang('Save Changes')">
											</form>
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
		'use strict';
		$(document).ready(function () {
			$('[data-toggle="tooltip"]').tooltip();
		});
	</script>
@endsection
