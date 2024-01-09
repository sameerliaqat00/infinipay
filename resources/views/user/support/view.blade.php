@extends('user.layouts.master')
@section('page_title', __("Ticket# "). __($ticket->ticket))
@push('extra_styles')
    <link href="{{ asset('assets/dashboard/css/file.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/dashboard/css/chat.css') }}" rel="stylesheet" type="text/css">
@endpush

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>{{__("Ticket# "). __($ticket->ticket)}}</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">{{__("Ticket# "). __($ticket->ticket)}}</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row">
						<div class="col-sm-12">
							<div class="card mb-4 card-primary shadow">
								<div class="card-body">
									<h5 class="card-title m-2 ">
										<div class="row justify-content-between align-items-center">
											<div class="col-sm-10">
												@if($ticket->status == 0)
													<span class="badge badge-pill badge-success">@lang('Open')</span>
												@elseif($ticket->status == 1)
													<span class="badge badge-pill badge-primary">@lang('Answered')</span>
												@elseif($ticket->status == 2)
													<span class="badge badge-pill badge-warning">@lang('Customer Replied')</span>
												@elseif($ticket->status == 3)
													<span class="badge badge-pill badge-dark">@lang('Closed')</span>
												@endif
												[{{trans('Ticket# ') . __($ticket->ticket) }}] {{ __($ticket->subject) }}
											</div>
											<div class="col-sm-2 text-sm-right mt-sm-0 mt-3">
												<button type="button" class="btn btn-outline-danger btn-sm" data-toggle="modal"
														data-target="#closeTicketModal">
													<i class="fas fa-times-circle"></i> {{ trans('Close') }}
												</button>
											</div>
										</div>
									</h5>
									<div class="card-body border mx-2">
										<form class="form-row" action="{{ route('user.ticket.reply', $ticket->id)}}" method="post"
											  enctype="multipart/form-data">
											@csrf
											@method('PUT')
											<div class="col-sm-10 col-12">
												<div class="form-group mt-0 mb-0">
													<textarea name="message" class="form-control  ticket-box" id="textarea1"
															  placeholder="@lang('Type Here')"
															  rows="3">{{ old('message') }}</textarea>
												</div>
												@error('message')
												<span class="text-danger">@lang($message)</span>
												@enderror
											</div>
											<div class="col-sm-2 col-12">
												<div class="justify-content-sm-end justify-content-start mt-sm-0 mt-2 align-items-center d-flex h-100">
													<div class="upload-btn">
														<div class="btn btn-primary new-file-upload mr-3"
															 title="{{ trans('Image Upload') }}">
															<a href="javascript:void(0)">
																<i class="fa fa-image"></i>
															</a>
															<input type="file" name="attachments[]" id="upload" class="upload-box"
																   multiple placeholder="@lang('Upload File')">
														</div>
														<p class="text-danger select-files-count"></p>
													</div>
													<button type="submit" title="{{ trans('Reply') }}" name="replayTicket" value="1"
															class="btn btn-sm btn-success button-round float-right text-white">
														<i class="fas fa-paper-plane"></i>
													</button>
												</div>
												@error('attachments')
													<span class="text-danger">@lang($message)</span>
												@enderror
											</div>
										</form>
									</div>
									@if(count($ticket->messages) > 0)
										<div class="row justify-content-center">
											<div class="col-md-12 chat" tabindex="5001">
												<div class="col-inside-lg decor-default">
													<div class="chat-body">
														@foreach($ticket->messages as $item)
															@if($item->admin_id == null)
																<div class="answer {{ Auth::getDefaultDriver() == 'web' ? 'right' : 'left' }}">
																	<div class="avatar">
																		<img src="{{getFile(config('location.user.path').optional(optional($ticket->user)->profile)->profile_picture)}}"
																			 alt="{{ __(optional($ticket->user)->name) }}">
																	</div>
																	<div class="name">{{ __(optional($ticket->user)->username) }}</div>
																	<div class="text">{{ __($item->message) }}</div>
																	@if(0 < count($item->attachments))
																		<div class="my-3 d-flex ">
																			@foreach($item->attachments as $k => $image)
																				<a href="{{ route('user.ticket.download',encrypt($image->id)) }}"
																				   class="mr-3 ">
																					<i class="fa fa-file"></i> @lang('File(s)') {{ __(++$k) }}
																				</a>
																			@endforeach
																		</div>
																	@endif
																	<div class="time mt-1">{{ __($item->created_at->format('d M, Y h:i A')) }}</div>
																</div>
															@else
																<div class="answer {{ Auth::getDefaultDriver() == 'web' ? 'left' : 'right' }}">
																	<div class="avatar">
																		<img src="{{ getFile(config('location.admin.path').optional($item->admin)->image) }}"
																			 alt="{{ __(optional($item->admin)->name) }}">
																	</div>
																	<div class="name">{{ __(optional($item->admin)->name) }}</div>
																	<div class="text">{{ __($item->message) }}</div>
																	@if(0 < count($item->attachments))
																		<div class="my-3 d-flex justify-content-end">
																			@foreach($item->attachments as $k => $image)
																				<a href="{{ route('user.ticket.download',encrypt($image->id)) }}"
																				   class="mr-3">
																					<i class="fa fa-file"></i> @lang('File(s)') {{ __(++$k) }}
																				</a>
																			@endforeach
																		</div>
																	@endif
																	<div class="time mt-1">{{ __($item->created_at->format('d M, Y h:i A')) }}</div>
																</div>
															@endif
														@endforeach
													</div>
												</div>
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

    <div class="modal fade" id="closeTicketModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="{{ route('user.ticket.reply', $ticket->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title text-danger"> @lang('Confirmation !')</h5>
                        <button type="button" class="close close-button" data-dismiss="modal">@lang('&times;')</button>
                    </div>
                    <div class="modal-body text-center">
                        <p>@lang('Are you want to close ticket')?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary"
                                data-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn-primary" name="replayTicket"
                                value="2">@lang("Confirm")</button>
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
            $(document).on('change', '#upload', function () {
                let fileCount = $(this)[0].files.length;
                $('.select-files-count').text(fileCount + ' file(s) selected')
            });
            $(".chat").niceScroll();
        });
    </script>
@endsection


