@extends('admin.layouts.master')
@section('page_title', __('Products List'))
@section('content')
	<div class="main-content" id="store" v-cloak>
		<section class="section">
			<div class="section-header">
				<h1>@lang('Products List')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Products List')</div>
				</div>
			</div>
			<div class="section-body">
				<div class="row mt-sm-4">
					<div class="col-12 col-md-12 col-lg-12">
						<div class="container-fluid" id="container-wrapper">
							<div class="row">
								<div class="col-lg-12">
									<div class="card mb-4 card-primary shadow-sm">
										<div
											class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
											<h6 class="m-0 font-weight-bold text-primary">@lang('Search')</h6>
										</div>
										<div class="card-body">
											<form action="{{ route('admin.product.list') }}" method="get">
												@include('admin.store.product.searchForm')
											</form>
										</div>
									</div>
								</div>
							</div>
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
														<th>@lang('Product')</th>
														<th>@lang('Category')</th>
														<th>@lang('Currency')</th>
														<th>@lang('Price')</th>
														<th>@lang('Status')</th>
														<th>@lang('Action')</th>
													</tr>
													</thead>
													<tbody>
													@foreach($products as $key => $item)
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
															<td data-label="Product">
																<a href="{{route('admin.product.view',$item->id)}}"
																   class="text-decoration-none">
																	<div class="d-lg-flex d-block align-items-center ">
																		<div class="mr-3"><img
																				src="{{getFile(config('location.product.path').$item->thumbnail)}}"
																				alt="user" class="rounded-circle"
																				width="40" data-toggle="tooltip"
																				title=""
																				data-original-title="{{$item->name}}">
																		</div>
																		<div
																			class="d-inline-flex d-lg-block align-items-center">
																			<p class="text-dark mb-0 font-16 font-weight-medium">
																				{{$item->name}}</p>
																			<span
																				class="text-muted font-14 ml-1">{{$item->sku}}</span>
																		</div>
																	</div>
																</a>
															</td>
															<td data-label="@lang('Category')">{{optional($item->category)->name}}</td>
															<td data-label="@lang('Currency')">{{optional($item->user->storeCurrency)->name}}</td>
															<td data-label="@lang('Price')">{{optional($item->user->storeCurrency)->symbol}}{{$item->price}}</td>
															<td data-label="@lang('Status')">
																@if($item->status == 1)
																	<span
																		class="badge badge-info">@lang('Active')</span>
																@else
																	<span
																		class="badge badge-warning">@lang('Inactive')</span>
																@endif
															</td>
															<td data-label="@lang('Action')">
																<a href="{{route('admin.product.view',$item->id)}}"
																   class="btn btn-outline-primary btn-sm mr-2"
																   title="@lang('view')"><i
																		class="fas fa-eye"></i></a>
															</td>
														</tr>
													@endforeach
													</tbody>
												</table>
											</div>
											<div class="card-footer">
												{{ $products->links() }}
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

