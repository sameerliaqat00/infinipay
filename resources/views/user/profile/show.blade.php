@extends('user.layouts.master')
@section('page_title',__('Profile'))

@push('extra_styles')
	<link href="{{ asset('assets/dashboard/css/select2.min.css') }}" rel="stylesheet" type="text/css">
@endpush

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Profile')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Profile')</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row justify-content-md-center">
						<div class="col-lg-7">
							<div class="card mb-4 card-primary shadow">
								<div class="card-header py-3">
									<div class="media custom--media flex-wrap d-flex flex-row justify-content-center">
										<div class="mr-2">
											<img class="align-self-start mr-3 img-profile-view img-thumbnail"
												 src="{{ $user->profilePicture() }}" alt="@lang('image')">
										</div>
										<div class="media-body">
											<h5 class="mt-0 font-weight-bold text-primary">{{ __(strtoupper($user->name)) }}</h5>
											<p>
												<i class="fas fa-user"></i> {{ __($user->username) }} <br>
												<i class="fas fa-mobile-alt"></i> {{ __($userProfile->phone) }} <br>
												<i class="fas fa-envelope"></i> {{ __( $user->email) }} <br>
											</p>
										</div>
									</div>
								</div>
								<div class="card-body">
									<form method="post" action="{{ route('user.profile') }}"
										  enctype="multipart/form-data">
										@csrf
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="name">@lang('Name')</label>
													<input type="text" name="name" placeholder="@lang('Your full name')"
														   value="{{ old('name', $user->name) }}"
														   class="form-control @error('name') is-invalid @enderror">
													<div
														class="invalid-feedback">@error('name') @lang($message) @enderror</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="city">@lang('City')</label>
													<input type="text" name="city"
														   placeholder="@lang('Enter your city')"
														   value="{{ old('city', $userProfile->city ) }}"
														   class="form-control @error('city') is-invalid @enderror">
													<div
														class="invalid-feedback">@error('city') @lang($message) @enderror</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="form-group">
													<label for="phone">@lang('Phone')</label>
													<div class="input-group-sm media parent-form-control">
														<div class="input-group-prepend phone-code-picker">
															<select name="phone_code"
																	class="form-control-sm country_code">
																@foreach($countries as $value)
																	<option
																		value="{{$value['phone_code']}}" {{ $userProfile->phone_code == $value['phone_code'] ? 'selected' : '' }}>
																		{{ __($value['phone_code']) }}
																		<strong>({{ __($value['name']) }})</strong>
																	</option>
																@endforeach
															</select>
														</div>
														<input type="text" name="phone" class="form-control"
															   value="{{old('phone', $userProfile->phone)}}"
															   placeholder="@lang('Your Phone Number')">
													</div>
													<div
														class="invalid-feedback">@error('phone') @lang($message) @enderror</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="state">@lang('State')</label>
													<input type="text" name="state"
														   placeholder="@lang('Enter your state')"
														   value="{{ old('state', $userProfile->state) }}"
														   class="form-control @error('state') is-invalid @enderror">
													<div
														class="invalid-feedback">@error('state') @lang($message) @enderror</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group search-currency-dropdown">
													<label for="language">@lang('Language')
														<i class="fas fa-info-circle" data-toggle="tooltip"
														   data-placement="top"
														   title="@lang('Select language to get notification on preferred language')"></i>
													</label>
													<select name="language"
															class="form-control @error('language') is-invalid @enderror">
														@foreach($languages as $language)
															<option
																value="{{ $language->id }}" {{ old('language', $user->language_id) == $language->id ? 'selected' : '' }}>
																{{ __($language->name) }}
															</option>
														@endforeach
													</select>
													<div
														class="invalid-feedback">@error('language') @lang($message) @enderror</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="form-group">
													<label for="referral_link">@lang('Referral Link')</label>
													<div class="input-group input-group-sm">
														<input type="text" id="referral_link" name="referral_link"
															   value="{{ route('register').'?referral='.$user->username }}"
															   class="form-control" disabled>
														<div class="input-group-append">
										<span class="input-group-text btn btn-sm copy-btn">
										<i class="far fa-copy"></i>
										&nbsp @lang('Copy')
										</span>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="form-group">
													<label for="address">@lang('Address')</label>
													<textarea
														class="form-control @error('address') is-invalid @enderror"
														name="address"
														rows="5">{{ old('address', $userProfile->address) }}</textarea>
													<div
														class="invalid-feedback">@error('address') @lang($message) @enderror</div>
												</div>
												<div class="form-group">
													<label for="About Yourself">@lang('About Yourself')</label>
													<textarea
														class="form-control @error('about_me') is-invalid @enderror"
														name="about_me"
														rows="5">{{ old('about_me', $userProfile->about_me) }}</textarea>
													<div
														class="invalid-feedback">@error('about_me') @lang($message) @enderror</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="form-group">
													<div class="custom-file">
														<input type="file" class="custom-file-input file-upload-input"
															   id="profile_picture" name="profile_picture">
														<label class="custom-file-label form-control-sm"
															   for="profile_picture">@lang('Choose profile picture')</label>
													</div>
												</div>
											</div>
										</div>
										<button type="submit"
												class="btn btn-primary btn-sm btn-block">@lang('Update Profile')</button>
									</form>
								</div>
							</div>
						</div>

						<div class="col-lg-5">
							<div class="card mb-4 card-primary shadow">
								<div class="card-header">
								<span class="d-flex flex-row align-items-center justify-content-between">
									<h6 class="font-weight-bold text-primary">@lang('Current Balance')</h6>
								</span>
								</div>
								<div class="card-body">
									<ul class="list-group">
										@foreach($wallets as $key => $value)
											<li class="list-group-item d-flex flex-row justify-content-between align-items-center">
										<span>
											<img class="image-profile rounded-circle"
												 src="{{ getFile(config('location.currencyLogo.path').optional($value->currency)->logo) }}">
											{{ __(optional($value->currency)->name) }} &nbsp;
										</span>
												<span class="">
											{{ __(optional($value->currency)->symbol) . ' ' . getAmount($value->balance) }}
										</span>
											</li>
										@endforeach
									</ul>
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
	<script src="{{ asset('assets/dashboard/js/select2.min.js') }}"></script>
@endpush
@section('scripts')
	<script>
		'use strict'
		$(document).ready(function () {
			$('[data-toggle="tooltip"]').tooltip();
			$('.country_code').select2();

			$(document).on('click', '.copy-btn', function () {
				let _this = $(this)[0];
				let copyText = $('#referral_link');
				$(copyText).prop('disabled', false);
				copyText.select();
				document.execCommand("copy");
				$(copyText).prop('disabled', true);
				$(this).text('Coppied');
				setTimeout(function () {
					$(_this).text('');
					$(_this).html('<i class="far fa-copy"></i> &nbsp Copy');
				}, 500);
			});
			$(document).on('change', '.file-upload-input', function () {
				let _this = $(this);
				let reader = new FileReader();
				reader.readAsDataURL(this.files[0]);
				reader.onload = function (e) {
					$('.img-profile-view').attr('src', e.target.result);
				}
			});
		});
	</script>
@endsection
