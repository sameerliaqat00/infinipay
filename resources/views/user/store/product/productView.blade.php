@extends('user.layouts.master')
@section('page_title',__('Product Details'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Product Details')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Product Details')</div>
				</div>
			</div>
			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row justify-content-md-center">
						<div class="col-md-12">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Product Details')</h6>
									<a href="{{ route('product.list') }}" class="btn btn-primary">@lang('Back')</a>
								</div>
								<div class="card-body">
									<form>
										<div class="row">
											<div class="col-md-4">
												<div class="form-group">
													<label for="Store">@lang('Store')</label>
													<select name="store[]"
															class="form-select form-control"
															id="selectStore"
															multiple="multiple">
														@forelse($product->productStores as $productStore)
															<option
																selected>{{optional($productStore->store)->name}}</option>
														@empty
														@endforelse
													</select>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label for="Category">@lang('Category')</label>
													<select name="category"
															class="form-control form-control-sm">
														<option value="">{{optional($product->category)->name}}</option>
													</select>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label for="Product Name">@lang('Product Name')</label>
													<input type="text" value="{{ $product->name }}"
														   name="name"
														   class="form-control"
														   autocomplete="off" readonly>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-4">
												<div class="form-group">
													<label for="Price">@lang('Price')</label>
													<div class="input-group">
														<input type="text" value="{{ $product->price }}"
															   name="price"
															   class="form-control"
															   autocomplete="off" readonly>
														<div class="input-group-prepend">
															<span
																class="form-control currencyCode">{{optional(auth()->user()->storeCurrency)->code}}</span>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label for="Attribute Name">@lang('Attribute Name')</label>
													<select
														class="form-select form-control @error('attributes') is-invalid @enderror"
														id="select"
														multiple="multiple">
														@foreach($product->productAttrs as $productAttr)
															<option
																selected>{{optional($productAttr->attribute)->name}}</option>
														@endforeach
													</select>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label for="SKU">@lang('SKU')</label>
													<input type="text" value="{{ $product->sku }}"
														   name="sku"
														   class="form-control"
														   autocomplete="off" readonly>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-4">
												<div class="form-group">
													<label for="Tag">@lang('Tag')</label>
													<input type="text" value="{{ $product->tag }}"
														   name="tag"
														   class="form-control"
														   autocomplete=" off" readonly>
												</div>
											</div>
											<div class="col-md-4">
												<label>@lang('Status')</label>
												<div class="selectgroup w-100">
													<label class="selectgroup-item">
														<input type="radio" name="status"
															   value="0"
															   class="selectgroup-input" {{$product->status == 0 ? 'checked':''}}>
														<span class="selectgroup-button">@lang('OFF')</span>
													</label>
													<label class="selectgroup-item">
														<input type="radio" name="status"
															   value="1"
															   class="selectgroup-input" {{$product->status == 1 ? 'checked':''}}>
														<span class="selectgroup-button">@lang('ON')</span>
													</label>
												</div>
											</div>
										</div>
										<div class="row addedField">
											<div class="col-sm-12 col-md-4 image-column mb-4">
												<label>@lang('Thumbnail Image')</label>
												<div class="form-group position-relative">
													<div class="image-input z0">
														<label for="image-upload" id="image-label"><i
																class="fas fa-upload"></i></label>
														<img id="image_preview_container" class="preview-image"
															 src="{{ getFile(config('location.product.path').$product->thumbnail) }}"
															 alt="@lang('preview image')">

													</div>
												</div>
											</div>
											@forelse($product->productImages as $image)
												<div class="col-sm-12 col-md-4 image-column mb-4">
													<label>@lang('Product Image')</label>
													<div class="form-group position-relative">
														<div class="image-input z0">
															<label for="image-upload" id="image-label"><i
																	class="fas fa-upload"></i></label>
															<input type="" id="image" name=""
																   placeholder="@lang('Choose image')"
																   class="image-preview-thumb">
															<img id="image_preview_container" class="preview-image"
																 src="{{ getFile(config('location.product.path').$image->image) }}"
																 alt="@lang('preview image')">
														</div>
													</div>
												</div>
											@empty
											@endforelse
										</div>
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label
														for="Description">@lang('Description')</label>
													<textarea
														class="form-control summernote"
														name="description"
														rows="5">{{$product->description}}</textarea>
													<div class="invalid-feedback">
														@error('description') @lang($message) @enderror
													</div>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label
														for="Description">@lang('Instruction')</label>
													<textarea
														class="form-control summernote"
														name="instruction"
														rows="5">{{$product->instruction}}</textarea>
													<div class="invalid-feedback">
														@error('description') @lang($message) @enderror
													</div>
												</div>
											</div>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
	@include('user.store.product.modal')
@endsection
@push('extra_styles')
	<link rel="stylesheet" href="{{ asset('assets/dashboard/modules/summernote/summernote-bs4.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/store/css/bootstrap-select.min.css') }}">
@endpush
@section('scripts')
	<script src="{{ asset('assets/dashboard/modules/summernote/summernote-bs4.js') }}"></script>
	<script src="{{ asset('assets/store/js/bootstrap-select.min.js') }}"></script>
	<script>
		'use strict';
		$(document).ready(function () {
			$(function () {
				$('#select').selectpicker();
			});
			$(function () {
				$('#selectStore').selectpicker();
			});
		});
	</script>
@endsection

