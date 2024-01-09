@extends('user.layouts.master')
@section('page_title', __('Product Stock'))

@section('content')
	<div class="main-content" id="store" v-cloak>
		<section class="section">
			<div class="section-header">
				<h1>@lang('Product Stock')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Product Stock')</div>
				</div>
			</div>
			<div class="section-body">
				<div class="row mt-sm-4">
					<div class="col-12 col-md-12 col-lg-12">
						<div class="container-fluid" id="container-wrapper">
							<div class="row justify-content-md-center">
								<div class="col-lg-12">
									<div class="card mb-4 card-primary shadow">
										<div
											class="card-header py-3 d-flex flex-wrap flex-row align-items-center justify-content-between">
											<h6 class="m-0 font-weight-bold text-primary">@lang('Product Stock')</h6>
											<a href="{{route('stock.create')}}"
											   class="btn btn-primary">@lang('Create Stock')</a>
										</div>
										<div class="card-body">
											<div class="table-responsive">
												<table
													class="table table-striped table-hover align-items-center table-flush"
													id="data-table">
													<thead class="thead-light">
													<tr>
														<th>@lang('SL.')</th>
														<th>@lang('Name')</th>
														<th>@lang('Quantity')</th>
														<th>@lang('Action')</th>
													</tr>
													</thead>
													<tbody>
													@foreach($productStocks as $key => $item)
														<tr>
															<td data-label="@lang('SL.')">{{++$key}}</td>
															<td data-label="Name">
																<a href="{{route('product.edit',$item->product_id)}}"
																   class="text-decoration-none">
																	<div class="d-lg-flex d-block align-items-center ">
																		<div class="mr-3"><img
																				src="{{getFile(config('location.product.path').optional($item->product)->thumbnail)}}"
																				alt="user" class="rounded-circle"
																				width="40" data-toggle="tooltip"
																				title=""
																				data-original-title="{{optional($item->product)->name}}">
																		</div>
																		<div
																			class="d-inline-flex d-lg-block align-items-center">
																			<p class="text-dark mb-0 font-16 font-weight-medium">
																				{{optional($item->product)->name}}</p>
																			<span
																				class="text-muted font-14 ml-1">{{optional($item->product)->sku}}</span>
																		</div>
																	</div>
																</a>
															</td>
															<td data-label="@lang('Quantity')">{{ $item->sumQuantity }}</td>
															<td data-label="@lang('Action')">
																<a href="{{route('stock.view',$item->product_id)}}"
																   class="btn btn-outline-primary btn-sm mr-2"><i
																		class="fas fa-eye"></i></a>
																<a href="javascript:void(0)"
																   data-target="#attrDelete"
																   data-toggle="modal"
																   data-route="{{route('attr.delete',$item->id)}}"
																   class="btn btn-outline-danger btn-sm deleteAttr"><i
																		class="fas fa-trash"></i></a>
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
	<div id="attrDelete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
		 aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title text-danger" id="primary-header-modalLabel">@lang('Attribute Delete')</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				@csrf
				<div class="modal-body">
					<p>@lang('Are you want to delete this Attribute?')</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
					<form action="" method="post" class="deleteAttrForm">
						@csrf
						@method('delete')
						<button type="submit" class="btn btn-primary">@lang('Submit')</button>
					</form>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('extra_scripts')

@endpush
@section('scripts')
	<script>
		'use strict'

		$(document).on('click', '.deleteAttr', function () {
			var route = $(this).data('route');
			$('.deleteAttrForm').attr('action', route);
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
			Notiflix.Notify.Failure("{{trans($error)}}");
			@endforeach
		</script>
	@endif
@endsection
