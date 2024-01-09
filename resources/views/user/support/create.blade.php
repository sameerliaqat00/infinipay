@extends('user.layouts.master')
@section('page_title',__('New Ticket'))

@section('content')
<div class="main-content">
	<section class="section">
		<div class="section-header">
			<h1>@lang('New Ticket')</h1>
			<div class="section-header-breadcrumb">
				<div class="breadcrumb-item active">
					<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
				</div>
				<div class="breadcrumb-item">@lang('New Ticket')</div>
			</div>
		</div>

		<div class="row mb-3">
			<div class="container-fluid" id="container-wrapper">
				<div class="row justify-content-md-center">
					<div class="col-sm-12">
						<div class="card mb-4 card-primary shadow">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
								<h6 class="m-0 font-weight-bold text-primary">@lang('New Ticket')</h6>
							</div>
							<div class="card-body">
								<form action="{{route('user.ticket.store')}}" method="post" enctype="multipart/form-data">
									@csrf
									<div class="form-group">
										<label for="subject">@lang('Subject')</label>
										<input type="text" name="subject" placeholder="@lang('Subject')"
											   value="{{ old('subject') }}"
											   class="form-control @error('subject') is-invalid @enderror">
										<div class="invalid-feedback">
											@error('subject') @lang($message) @enderror
										</div>
										<div class="valid-feedback"></div>
									</div>
									<div class="form-group">
										<label for="message">@lang('Message')</label>
										<textarea name="message" rows="5"
												  class="form-control @error('note') is-invalid @enderror">{{ old('message') }}</textarea>
										<div class="invalid-feedback">
											@error('message') @lang($message) @enderror
										</div>
									</div>
									<div class="form-group">
										<div class="custom-file">
											<input type="file" class="custom-file-input" id="upload" name="attachments[]"
												   multiple>
											<label class="custom-file-label form-control-sm"
												   for="upload">@lang('Choose files')</label>
										</div>
										<p class="text-danger select-files-count"></p>
										@error('attachments')
											<div class="error text-danger"> @lang($message) </div>
										@enderror
									</div>
									<button type="submit" class="btn btn-primary btn-sm btn-block">
										@lang('Submit Ticket')
									</button>
								</form>
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
            $(document).on('change', '#upload', function () {
                var fileCount = $(this)[0].files.length;
                $('.select-files-count').text(fileCount + ' file(s) selected');
            });
        });
    </script>
@endsection
