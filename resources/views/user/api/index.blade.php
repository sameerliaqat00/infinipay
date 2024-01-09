@extends('user.layouts.master')
@section('page_title',__('Business Api key'))

@section('content')
	<div class="main-content" id="api-app">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Business Api key')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Business Api key')</div>
				</div>
			</div>
			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row justify-content-md-center">
						<div class="col-md-8">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Business Api key')</h6>
									<div class="selectgroup w-30">
										<label class="selectgroup-item">
											<input type="radio"
												   class="selectgroup-input" {{$user->mode == 0?'checked':''}}>
											<a href="{{route('user.mode.change','test')}}"
											   class="selectgroup-button"><span>@lang('Test Mode')</span></a>
										</label>
										<label class="selectgroup-item">
											<input type="radio"
												   class="selectgroup-input" {{$user->mode == 1?'checked':''}}>
											<a href="{{route('user.mode.change','live')}}"
											   class="selectgroup-button"><span>@lang('Live Mode')</span></a>
										</label>
									</div>
								</div>
								<div class="card-body">
									<form>
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="Public Key">@lang('Public Key:')</label>
													<div class="input-group">
														<input type="text" name="public_key"
															   class="form-control"
															   value="{{$user->public_key}}" readonly>
														<div class="input-group-prepend">
															<button type="button"
																	class="btn btn-success py-0"
																	@click="copyPublicCode()">@lang('Copy')</button>
														</div>
													</div>
													<div class="invalid-feedback">
														@error('public_key') @lang($message) @enderror
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="Public Key">@lang('Secret Key:')</label>
													<div class="input-group">
														<input type="text" name="secret_key" class="form-control"
															   value="{{$user->secret_key}}" readonly>
														<div class="input-group-prepend">
															<button type="button"
																	class="btn btn-success py-0"
																	@click="copySecretCode()">@lang('Copy')</button>
														</div>
													</div>
													<div class="invalid-feedback">
														@error('secret_key') @lang($message) @enderror
													</div>
												</div>
											</div>
										</div>
										<button type="button" data-target="#generate" data-toggle="modal"
												class="btn btn-primary btn-sm btn-block">@lang('Generate Api Key')</button>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
	<div id="generate" class="modal fade" tabindex="-1" role="dialog"
		 aria-labelledby="primary-header-modalLabel"
		 aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title text-dark font-weight-bold"
						id="primary-header-modalLabel">@lang('Re-Generate Confirmation ')</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				<form action="{{route('user.api.key')}}" method="post">
					@csrf
					<div class="modal-body">
						<h5>@lang('Are you sure want generate new api key?')</h5>
						<p>@lang('It may cause interrupt with your existing api request operations.')</p>
						<div class="form-group mt-3 security-block">
							<label for="security_pin">@lang('Security Pin')</label>
							<input type="password" name="security_pin"
								   placeholder="@lang('Please enter your security PIN')"
								   autocomplete="off"
								   value="{{ old('security_pin') }}"
								   class="form-control @error('security_pin') is-invalid @enderror">
							<div class="invalid-feedback">
								@error('security_pin') @lang($message) @enderror
							</div>
							<div class="valid-feedback"></div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
						<button type="submit" class="btn btn-primary">@lang('Confirm')</button>
					</div>
				</form>
			</div>
		</div>
	</div>

@endsection
@push('extra_scripts')
	<script>
		'use script'
		var newApp = new Vue({
			el: "#api-app",
			data: {
				item: {
					public_key: '{{$user->public_key}}',
					secret_key: '{{$user->secret_key}}',
				},
			},
			mounted() {
			},
			methods: {
				copyPublicCode() {
					let text = this.item.public_key;
					navigator.clipboard.writeText(text);
					Notiflix.Notify.Success(`Copied: ${text}`);
				},
				copySecretCode() {
					let text = this.item.secret_key;
					navigator.clipboard.writeText(text);
					Notiflix.Notify.Success(`Copied: ${text}`);
				},
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
@endpush
