@extends('admin.layouts.master')
@section('page_title', __('Create Security Question'))
@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Create Security Question')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item"><a
							href="{{ route('securityQuestion.index') }}">@lang('Security Questions')</a></div>
					<div class="breadcrumb-item">@lang('Create Security Question')</div>
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
											<h6 class="m-0 font-weight-bold text-primary">@lang('Create Security Question')</h6>
										</div>
										<div class="card-body">
											<form method="post"
												  action="{{ route('securityQuestion.store') }}"
												  enctype="multipart/form-data">
												@csrf
												<div class="form-group">
													<label for="question">@lang('Question')</label>
													<input type="text" name="question"
														   placeholder="@lang('eg:- What is your pet name?')"
														   class="form-control @error('question') is-invalid @enderror"
														   value="{{ old('question') }}">
													<div class="invalid-feedback">
														@error('question') @lang($message) @enderror
													</div>
												</div>
												<button type="submit"
														class="btn btn-primary btn-sm btn-block">@lang('Create New Question')</button>
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
