@extends('admin.layouts.master')
@section('page_title', __('Virtual-Card Settings'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Virtual-Card Settings')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Virtual-Card Settings')</div>
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
											<h6 class="m-0 font-weight-bold text-primary">@lang('Virtual-Card Settings')</h6>
										</div>
										<div class="card-body">
											<form action="{{ route('virtual-card.settings') }}" method="post">
												@csrf
												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<label>
																@lang('Allow existing users on create multiple card')
															</label>
															<div class="selectgroup w-100">
																<label class="selectgroup-item">
																	<input type="radio" name="v_card_multiple" value="0"
																		   class="selectgroup-input" {{ old('v_card_multiple', $basicControl->v_card_multiple) == 0 ? 'checked' : ''}}>
																	<span class="selectgroup-button">@lang('No')</span>
																</label>
																<label class="selectgroup-item">
																	<input type="radio" name="v_card_multiple" value="1"
																		   class="selectgroup-input" {{ old('v_card_multiple', $basicControl->v_card_multiple) == 1 ? 'checked' : ''}}>
																	<span class="selectgroup-button">@lang('Yes')</span>
																</label>
															</div>
															@error('v_card_multiple')
															<span class="text-danger" role="alert">
																<strong>{{ __($message) }}</strong>
															</span>
															@enderror
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label>
																@lang('Charges exiting users per card request')
															</label>
															<div class="input-group">
																<input type="number" class="form-control" step="0.001"
																	   name="v_card_charge"
																	   value="{{$basicControl->v_card_charge}}">
																<div class="input-group-prepend">
																	<span
																		class="form-control">{{config('basic.base_currency_code')}}</span>
																</div>
															</div>
															@error('v_card_charge')
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
