@extends('admin.layouts.master')
@section('page_title', __('Card Transactions'))
@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Card Transactions')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Card Lists')</div>
				</div>
			</div>
			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row">
						<div class="col-lg-12">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Card Transactions')</h6>
									<a href="{{route('admin.virtual.cardList')}}"
									   class="btn btn-primary">@lang('Back')</a>
								</div>
								<div class="card-body">
									<div class="table-responsive">
										<table class="table align-items-center table-bordered"
											   id="payment-method-table">
											<thead class="thead-light">
											<tr>
												<th col="scope">@lang('SL.')</th>
												<th col="scope">@lang('User')</th>
												<th col="scope">@lang('Provider')</th>
												<th col="scope">@lang('Amount')</th>
												<th scope="col">@lang('More')</th>
											</tr>
											</thead>
											<tbody id="sortable">
											@if(count($transactions) > 0)
												@foreach($transactions as $key => $item)
													<tr>
														<td data-label="@lang('SL.')">{{ ++$key }} </td>
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
														<td data-label="@lang('Provider')">{{optional($item->cardOrder->cardMethod)->name}}</td>
														<td data-label="@lang('Amount')">{{$item->amount}} {{$item->currency_code}}</td>
														<td data-label="@lang('More')">
															<a href="javascript:void(0)"
															   class="btn btn-outline-primary btn-sm details"
															   title="view" data-target="#viewDetails"
															   data-toggle="modal"
															   data-resource="{{json_encode($item->data)}}"><i
																	class="fas fa-eye"></i></a>
														</td>
													</tr>
												@endforeach
											@else
												<tr>
													<td class="text-center text-danger" colspan="5">
														@lang('No Data Found')
													</td>
												</tr>
											@endif
											</tbody>
										</table>
									</div>
									<div class="card-footer">
										{{ $transactions->links() }}
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

		</section>
	</div>
	<div id="viewDetails" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
		 aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title text-dark font-weight-bold"
						id="primary-header-modalLabel">@lang('Transaction Information')</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				<div class="modal-body">
					<div class="tranShow">

					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('scripts')
	<script>
		'use strict'
		$(document).on('click', '.details', function () {
			$('.tranShow').html('');
			var res = $(this).data('resource');
			for (const key in res) {
				let newKey = key.replace('_', ' ');
				let finalKey = newKey.toUpperCase();
				var list = `<li class="list-group-item d-flex justify-content-between text-dark font-weight-bold">
													<span>${finalKey}</span>
													<span
														class="text-dark font-weight-bold">${res[key]}</span>
												</li>`

				$('.tranShow').append(list);
			}

		})
	</script>
@endsection
