@extends('admin.layouts.master')
@section('page_title', __('Dispute'))
@push('extra_styles')
	<link href="{{ asset('assets/dashboard/css/file.css') }}" rel="stylesheet" type="text/css">
	<link href="{{ asset('assets/dashboard/css/chat.css') }}" rel="stylesheet" type="text/css">
@endpush
@section('content')
<div class="main-content">
	<section class="section">
		<div class="section-header">
			<h1>@lang('Dispute')</h1>
			<div class="section-header-breadcrumb">
				<div class="breadcrumb-item active">
					<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
				</div>
				<div class="breadcrumb-item">@lang('Dispute')</div>
			</div>
		</div>

		<div class="row mb-3">
			<div class="container-fluid" id="container-wrapper">
				<div class="row row-broken justify-content-md-center">
					<div class="col-sm-12">
						<div class="card mb-4 card-primary shadow">
							<div class="card-body">
								<form action="{{ route('admin.dispute.view', $dispute->utr) }}" method="post" enctype="multipart/form-data">
									@csrf
									@method('put')
									<div class="row">
										<div class="col-sm-8 col-12">
											<div class="form-group mt-0 mb-0">
												<textarea name="message" class="form-control  ticket-box" id="textarea1" placeholder="@lang('Type Here')" rows="5">{{ old('message') }}</textarea>
											</div>
											@error('message')<span class="text-danger">@lang($message)</span>@enderror
										</div>
										<div class="col-sm-4 col-12">
											<div class="row">
												<div class="col-sm-6 col-12">
													<button type="button" class="btn btn-block btn-sm btn-info mb-3 confirmModal" data-toggle="modal" data-target="#confirmModal"
															data-msg="@lang('solved this dispute?')" data-route="{{ route('admin.dispute.status.change', [$dispute->utr,1]) }}" {{ $dispute->status == 1 ? 'disabled' : ''}}>
														<i class="far fa-check-circle"></i> @lang('Solved')
													</button>
												</div>
												<div class="col-sm-6 col-12">
													<button type="button" class="btn btn-block btn-sm btn-dark mb-3 confirmModal" data-toggle="modal" data-target="#confirmModal"
															data-msg="@lang('closed this dispute?')" data-route="{{ route('admin.dispute.status.change', [$dispute->utr,2]) }}" {{ $dispute->status == 2 ? 'disabled' : ''}}>
														<i class="far fa-times-circle"></i> @lang('Closed')
													</button>
												</div>
												<div class="col-12">
													<div class="form-group search-currency-dropdown">
														<select name="user_id" class="form-control form-control-sm">
															<option value="{{ optional($dispute->disputable)->receiver_id ?? '' }}" selected>
																@lang("Reply to Claimer ") ( {{ __(optional(optional($dispute->disputable)->receiver)->name) ?? __('N/A') }} )
															</option>
															<option value="{{ optional($dispute->disputable)->sender_id ?? '' }}">
																@lang("Reply to Defender ") ( {{ __(optional(optional($dispute->disputable)->sender)->name) ?? __('N/A') }} )
															</option>
														</select>
													</div>
												</div>
												<div class="col-12 d-flex align-items-center justify-content-center mt-n1 mb-2">
													<div class="upload-btn">
														<div class="btn btn-primary new-file-upload mr-3" title="{{ trans('Image Upload') }}">
															<a href="javascript:void(0)">
																<i class="fa fa-image"></i>
															</a>
															<input type="file" name="attachments[]" id="upload" class="upload-box" multiple placeholder="@lang('Upload File')">
														</div>
														<p class="text-danger select-files-count"></p>
													</div>
													<button type="submit" title="{{trans('Reply')}}" class="btn btn-sm btn-success button-round float-right text-white">
														<i class="fas fa-paper-plane"></i>
													</button>
													@error('attachments')
														<span class="text-danger">{{ trans($message) }}</span>
													@enderror
												</div>
											</div>
										</div>
									</div>
								</form>
								<div class="row">
									<div class="col-sm-6 col-12">
										@if($dispute->defender_reply_yn == 1)
											<button type="button" class="btn btn-sm btn-block btn-outline-success mt-3 confirmModal" data-toggle="modal" data-target="#confirmModal"
													data-msg="@lang('mute this defender?')" data-route="{{ route('admin.defender.mute.unmute',[$dispute->utr,1]) }}">
												<i class="fas fa-microphone"></i>
												@lang('Mute Defender') ( {{ __(optional(optional($dispute->disputable)->sender)->name) ?? __('N/A') }} )
											</button>
										@else
											<button type="button" class="btn btn-sm btn-block btn-outline-danger mt-3 confirmModal" data-toggle="modal" data-target="#confirmModal"
													data-msg="@lang('unmute this defender?')" data-route="{{ route('admin.defender.mute.unmute',[$dispute->utr,0]) }}">
												<i class="fas fa-microphone-slash"></i>
												@lang('Unmute Defender') ( {{ __(optional(optional($dispute->disputable)->sender)->name) ?? __('N/A') }} )
											</button>
										@endif
									</div>
									<div class="col-sm-6 col-12">
										@if($dispute->claimer_reply_yn == 1)
											<button type="button" class="btn btn-sm btn-block btn-outline-success mt-3 confirmModal" data-toggle="modal" data-target="#confirmModal"
													data-msg="@lang('mute this claimer?')" data-route="{{ route('admin.claimer.mute.unmute',[$dispute->utr,1]) }}">
												<i class="fas fa-microphone"></i>
												@lang('Mute Claimer') ( {{ __(optional(optional($dispute->disputable)->receiver)->name) ?? __('N/A') }} )
											</button>
										@else
											<button type="button" class="btn btn-sm btn-block btn-outline-danger mt-3 confirmModal" data-toggle="modal" data-target="#confirmModal"
													data-msg="@lang('unmute this claimer?')" data-route="{{ route('admin.claimer.mute.unmute',[$dispute->utr,0]) }}">
												<i class="fas fa-microphone-slash"></i>
												@lang('Unmute Claimer') ( {{ __(optional(optional($dispute->disputable)->receiver)->name) ?? __('N/A') }} )
											</button>
										@endif
									</div>
								</div>
								@if(isset($dispute) && $dispute->disputeDetails->count() > 0)
									<div class="col-inside-lg decor-default chat mt-1">
										<div class="chat-body">
											@foreach($dispute->disputeDetails as $dispute_details)
												@if(isset($dispute_details->message))
													@if($dispute_details->admin_id == null)
														<div class="answer {{ Auth::getDefaultDriver() == 'web' ? 'right' : 'left' }}">
															<div class="avatar">
																<img src="{{getFile(config('location.user.path').optional(($dispute_details->user)->profile)->profile_picture)}}"
																	 alt="{{ __(optional($dispute_details->user)->name) }}">
															</div>
															<div class="name">{{ __(optional($dispute_details->user)->username) }}</div>
															<div class="text">{{ __($dispute_details->message) }}</div>

															@if(isset($dispute_details->files) && count($dispute_details->files) > 0)
																<div class="my-1 d-flex ">
																	@foreach($dispute_details->files as $k => $image)
																		<a href="{{route('admin.dispute.file.download',[$dispute_details->utr, encrypt($image)])}}" class="mr-3 ">
																			<i class="fa fa-file"></i> @lang('File') {{ __(++$k) }}
																		</a>
																	@endforeach
																</div>
															@endif
															<div class="time mt-1">{{ __($dispute_details->created_at->format('d M, Y h:i A')) }}</div>
														</div>
													@else
														<div class="answer {{ Auth::getDefaultDriver() == 'web' ? 'left' : 'right' }}">
															<div class="avatar">
																<img src="{{getFile(config('location.admin.path').optional($dispute_details->admin)->image)}}"
																	 alt="{{ __(optional($dispute_details->admin)->name) }}">
															</div>
															<div class="name">{{ __(optional($dispute_details->admin)->name) }}</div>
															<div class="text">{{ __($dispute_details->message) }}</div>
															@if(isset($dispute_details->files) && count($dispute_details->files) > 0)
																<div class="my-1 d-flex justify-content-end">
																	@foreach($dispute_details->files as $k => $image)
																		<a href="{{route('admin.dispute.file.download', [$dispute_details->utr, encrypt($image)])}}" class="mr-3">
																			<i class="fa fa-file"></i> @lang('File') {{ __(++$k) }}
																		</a>
																	@endforeach
																</div>
															@endif
															<div class="time mt-1">{{ __($dispute_details->created_at->format('d M, Y h:i A')) }}</div>
														</div>
													@endif
												@else
													<div class="answer text-center chat-action">
														<p class="p-0 m-0">
															@if($dispute_details->action == 0)
																@lang("Issue has been solved by") {{ __(optional($dispute_details->admin)->name) ?? __('Admin') }}
															@elseif($dispute_details->action == 1)
																@lang("Issue has been closed by") {{ __(optional($dispute_details->admin)->name) ?? __('Admin') }}
															@elseif($dispute_details->action == 2)
																{{ __(optional($dispute_details->user)->name) ?? __('Admin') }} @lang(" has been muted by") {{ __(optional($dispute_details->admin)->name) ?? __('N/A') }}
															@elseif($dispute_details->action == 3)
																{{ __(optional($dispute_details->user)->name) ?? __('Admin') }} @lang(" has been unmuted by") {{ __(optional($dispute_details->admin)->name) ?? __('N/A') }}
															@endif
														</p>
														<span class="text-black-50">{{ __($dispute_details->created_at->format('d M, Y h:i A')) }}</span>
													</div>
												@endif
											@endforeach
										</div>
									</div>
								@endif
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

	</section>
</div>

	<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title text-danger" id="exampleModalLabel">@lang('Confirmation!')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">@lang('&times;')</span>
					</button>
				</div>
				<div class="modal-body text-center">
					<p id="meg">@lang('Are you sure you want to cancel?')</p>
				</div>
				<form action="" method="post" id="confirmModalForm">
					@csrf
					@method('put')
					<div class="modal-footer">
						<button type="button" class="btn btn-outline-primary" data-dismiss="modal">@lang('Close')</button>
						<button type="submit" class="btn btn-primary">@lang('Confirmed')</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
@push('extra_scripts')
	<script src="{{ asset('assets/dashboard/js/jquery.nicescroll.min.js') }}"></script>
@endpush
@section('scripts')
	<script>
		'use strict';
		$(document).ready(function () {
			$(".chat").niceScroll();
			$(document).on('change', '#upload', function () {
				let fileCount = $(this)[0].files.length;
				$('.select-files-count').text(fileCount + ' file(s) selected')
			});
			$(document).on('click', '.confirmModal', function (e) {
				let formUrl = $(this).data('route');
				let msg = $(this).data('msg');
				$('#confirmModalForm').attr('action', formUrl)
				$('#meg').text('Do you want to ' + msg)
			});
		});
	</script>
@endsection
