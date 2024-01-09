

@extends('admin.layouts.master')
@section('page_title', __('Security Questions'))
@section('content')
<div class="main-content">
	<section class="section">
		<div class="section-header">
			<h1>@lang('Security Questions')</h1>
			<div class="section-header-breadcrumb">
				<div class="breadcrumb-item active">
					<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
				</div>
				<div class="breadcrumb-item">@lang('Security Questions')</div>
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
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<h6 class="m-0 font-weight-bold text-primary">@lang('Security Questions')</h6>
										<a href="{{ route('securityQuestion.create') }}" class="btn btn-sm btn-outline-primary">@lang('Add New')</a>
									</div>
									<div class="card-body">
										<div class="table-responsive">
											<table class="table table-hover align-items-center table-flush">
												<thead class="thead-light">
												<tr>
													<th>@lang('Question')</th>
													<th>@lang('Action')</th>
												</tr>
												</thead>
												<tbody>
												@foreach($securityQuestions as $key => $securityQuestion)
													<tr>
														<td data-label="@lang('Question')">{{ __($securityQuestion->question) }}</td>
														<td data-label="@lang('Action')">
															<a href="{{ route('securityQuestion.edit', $securityQuestion) }}" class="btn btn-sm btn-outline-primary">
																<i class="fas fa-edit"></i>
																@lang('Edit')
															</a>
														</td>
													</tr>
												@endforeach
												</tbody>
											</table>
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
