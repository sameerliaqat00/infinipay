@extends('user.layouts.master')
@section('page_title',__('Dispute'))

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
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Dispute')</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="container">
					<div class="content container-fluid bootstrap snippets bootdey">
						<div class="row row-broken">
							<div class="col-sm-12" tabindex="5001">
								<div class="card-body border bg-light mt-4">
									<form class="form-row" action="{{ route('user.dispute.view', $escrow->utr) }}" method="post"
										  enctype="multipart/form-data">
										@csrf
										@method('put')
										@if(isset($dispute) && (($escrow->sender_id == Auth::id() && $dispute->defender_reply_yn == 0) || ($escrow->receiver_id == Auth::id() && $dispute->claimer_reply_yn == 0)))
											<div class="col-sm-12">
												<h1 class="text-danger text-center">
													<i class="fas fa-exclamation-triangle muted-icon-size"></i> @lang('You are muted')
												</h1>
											</div>
										@else
											<div class="col-sm-10 col-12">
												<div class="form-group mt-0 mb-0">
													<textarea name="message" class="form-control  ticket-box" id="textarea1"
															  placeholder="@lang('Type Here')"
															  rows="3">{{old('message')}}</textarea>
												</div>
												@error('message')
													<span class="text-danger">@lang($message)</span>
												@enderror
											</div>
											<div class="col-sm-2 col-12">
												<div class="justify-content-sm-end justify-content-start mt-sm-0 mt-2 align-items-center d-flex h-100">
													<div class="upload-btn">
														<div class="btn btn-primary new-file-upload mr-3"
															 title="{{trans('Image Upload')}}">
															<a href="javascript:void(0)">
																<i class="fa fa-image"></i>
															</a>
															<input type="file" name="attachments[]" id="upload" class="upload-box"
																   multiple placeholder="@lang('Upload File')">
														</div>
														<p class="text-danger select-files-count"></p>
													</div>
													<button type="submit" title="{{ trans('Reply') }}"
															class="btn btn-sm btn-success button-round float-right text-white">
														<i class="fas fa-paper-plane"></i>
													</button>
												</div>
												@error('attachments')
													<span class="text-danger">{{ trans($message) }}</span>
												@enderror
											</div>
										@endif
									</form>
								</div>
								@if(isset($dispute) && $dispute->disputeDetails->count() > 0)
									<div class="col-inside-lg decor-default chat shadow-sm">
										<div class="chat-body">
											@foreach($dispute->disputeDetails as $dispute_details)
												@if(isset($dispute_details->message))
													@if($dispute_details->admin_id == null)
														<div class="answer {{ Auth::getDefaultDriver() == 'web' ? 'right' : 'left' }}">
															<div class="avatar">
																<img src="{{ getFile(config('location.user.path').optional(($dispute_details->user)->profile)->profile_picture) }}"
																	 alt="{{ __(optional($dispute_details->user)->name) }}">
															</div>
															<div class="name">{{ __(optional($dispute_details->user)->username) }}</div>
															<div class="text">{{ __($dispute_details->message) }}</div>

															@if(isset($dispute_details->files) && count($dispute_details->files) > 0)
																<div class="my-1 d-flex ">
																	@foreach($dispute_details->files as $k => $image)
																		<a href="{{ route('user.dispute.file.download',[$dispute_details->utr, encrypt($image)]) }}"
																		   class="mr-3 ">
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
																<img src="{{ getFile(config('location.admin.path').optional($dispute_details->admin)->image)}}"
																	 alt="{{ __(optional($dispute_details->admin)->name) }}">
															</div>
															<div class="name">{{ __(optional($dispute_details->admin)->name) }}</div>
															<div class="text">{{ __($dispute_details->message) }}</div>
															@if(isset($dispute_details->files) && count($dispute_details->files) > 0)
																<div class="my-1 d-flex justify-content-end">
																	@foreach($dispute_details->files as $k => $image)
																		<a href="{{ route('user.dispute.file.download', [$dispute_details->utr, encrypt($image)]) }}"
																		   class="mr-3">
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
																@lang("Issue has been solved by")
																<span class="text-success">
																	{{ __(optional($dispute_details->admin)->name) ?? __('Admin') }}
																</span>
															@elseif($dispute_details->action == 1)
																@lang("Issue has been closed by")
																<span class="text-success">
																	{{ __(optional($dispute_details->admin)->name) ?? __('Admin') }}
																</span>
															@elseif($dispute_details->action == 2)
																<span class="text-success">
																	@lang('You')
																</span>
																@lang(" have been muted by")
																<span class="text-success">
																	{{ __(optional($dispute_details->admin)->name) ?? __('N/A') }}
																</span>
															@elseif($dispute_details->action == 3)
																<span class="text-success">
																	@lang('You')
																</span>
																@lang(" have been unmuted by")
																<span class="text-success">
																	{{ __(optional($dispute_details->admin)->name) ?? __('N/A') }}
																</span>
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

		</section>
	</div>
@endsection

@push('extra_scripts')
    <script src="{{ asset('assets/dashboard/js/jquery.nicescroll.min.js') }}"></script>
@endpush
@section('scripts')
    <script>
        'use strict'
        $(document).ready(function () {
            $(document).on('change', '#upload', function () {
                let fileCount = $(this)[0].files.length;
                $('.select-files-count').text(fileCount + ' file(s) selected')
            })
            $(".chat").niceScroll();
        });
    </script>
@endsection
