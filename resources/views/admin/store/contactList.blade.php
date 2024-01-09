@extends('admin.layouts.master')
@section('page_title', __('Contact List'))
@section('content')
	<div class="main-content" id="store" v-cloak>
		<section class="section">
			<div class="section-header">
				<h1>@lang('Contact List')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Contact List')</div>
				</div>
			</div>
			<div class="section-body">
				<div class="container-fluid" id="container-wrapper">
					<div class="row">
						<div class="col-12 col-md-12 col-lg-12">
							<div class="card mb-4 card-primary shadow-sm">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Search')</h6>
								</div>
								<div class="card-body">
									<form action="{{ route('admin.contact.list') }}" method="get">
										@include('admin.store.contactSearchForm')
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row mt-sm-4">
					<div class="col-12 col-md-12 col-lg-12">
						<div class="container-fluid" id="container-wrapper">
							<div class="row justify-content-md-center">
								<div class="col-lg-12">
									<div class="card mb-4 card-primary shadow">
										<div class="card-body">
											<div class="table-responsive">
												<table
													class="table table-striped table-hover align-items-center table-flush"
													id="data-table">
													<thead class="thead-light">
													<tr>
														<th>@lang('SL.')</th>
														<th>@lang('User')</th>
														<th>@lang('Store')</th>
														<th>@lang('Sender Name')</th>
														<th>@lang('Action')</th>
													</tr>
													</thead>
													<tbody>
													@foreach($contactLists as $key => $item)
														<tr>
															<td data-label="@lang('SL.')">{{++$key}}</td>
															<td data-label="@lang('User')">
																<a href="{{ route('user.edit', $item->user_id)}}"
																   class="text-decoration-none">
																	<div class="d-lg-flex d-block align-items-center ">
																		<div class="mr-3"><img
																				src="{{ optional($item->user)->profilePicture()??asset('assets/upload/boy.png') }}"
																				alt="user"
																				class="rounded-circle" width="35"
																				data-toggle="tooltip" title=""
																				data-original-title="{{optional($item->user)->name?? __('N/A')}}">
																		</div>
																		<div
																			class="d-inline-flex d-lg-block align-items-center">
																			<p class="text-dark mb-0 font-16 font-weight-medium">{{Str::limit(optional($item->user)->name?? __('N/A'),20)}}</p>
																			<span
																				class="text-muted font-14 ml-1">{{ '@'.optional($item->user)->username?? __('N/A')}}</span>
																		</div>
																	</div>
																</a>
															</td>
															<td data-label="@lang('Store')">{{ optional($item->store)->name }}</td>
															<td data-label="@lang('Sender Name')">{{$item->sender_name}}</span>
															</td>
															<td data-label="@lang('Action')">
																<a href="javascript:void(0)" data-target="#messageView"
																   data-toggle="modal"
																   data-name="{{$item->sender_name}}"
																   data-message="{{$item->message}}"
																   class="btn btn-outline-primary btn-sm msgView"><i
																		class="fa fa-eye"></i></a>
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
	<div class="modal fade" id="messageView" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-md">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="editModalLabel">@lang('Message Details')</h4>
					<button
						type="button"
						class="close"
						data-dismiss="modal"
						aria-label="Close">
						<i class="fas fa-times"></i>
					</button>
				</div>
				<div class="modal-body">
					<form action="">
						<div class="row">
							<div class="input-box col-12 mb-2">
								<label>@lang('Sender Name')</label>
								<input class="form-control name" type="text" value="" readonly/>
							</div>
							<div class="input-box col-12">
								<label>@lang('Message')</label>
								<textarea
									class="form-control msg"
									cols="30"
									rows="10" readonly></textarea>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button
						type="button"
						class="btn btn-outline-primary"
						data-dismiss="modal">
						@lang('Close')
					</button>
				</div>
			</div>
		</div>
	</div>
@endsection
@section('scripts')
	<script>
		'use strict'
		$(document).on('click', '.msgView', function () {
			$('.name').val($(this).data('name'))
			$('.msg').text($(this).data('message'))
		})
	</script>
@endsection
