@extends('user.layouts.master')
@section('page_title',__('Product Create'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Product Create')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Product Create')</div>
				</div>
			</div>
			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row justify-content-md-center">
						<div class="col-md-12">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Product Create')</h6>
									<a href="{{ route('product.list') }}" class="btn btn-primary">@lang('Back')</a>
								</div>
								<div class="card-body">
									<form action="{{ route('product.create') }}" method="post"
										  enctype="multipart/form-data">
										@csrf
										<div class="row">
											<div class="col-md-4">
												<div class="form-group">
													<label for="Store">@lang('Store') <sup
															class="text-danger">*</sup></label>
													<select name="store[]"
															class="form-select form-control @error('store') is-invalid @enderror"
															id="selectStore"
															multiple="multiple" required>
														@forelse($stores as $store)
															<option
																value="{{$store->id}}" {{old('Store') == $store->id ? 'selected':''}}>{{$store->name}}</option>
														@empty
														@endforelse
													</select>
													<div class="invalid-feedback mt-3">
														@error('store') @lang($message) @enderror
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label for="Category">@lang('Category') <sup
															class="text-danger">*</sup></label>
													<select name="category"
															class="form-control form-control-sm @error('category') is-invalid @enderror"
															required>
														<option disabled selected
																value="">@lang('Select Category')</option>
														@forelse($categories as $category)
															<option
																value="{{$category->id}}" {{old('category') == $category->id ? 'selected':''}}>{{$category->name}}</option>
														@empty
														@endforelse
													</select>
													<div class="invalid-feedback">
														@error('category') @lang($message) @enderror
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label for="Attribute Name">@lang('Product Name') <sup
															class="text-danger">*</sup></label>
													<input type="text" value="{{ old('name') }}"
														   name="name"
														   class="form-control @error('name') is-invalid @enderror"
														   autocomplete="off" required>
													<div class="invalid-feedback">
														@error('name') @lang($message) @enderror
													</div>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-4">
												<div class="form-group">
													<label for="Price">@lang('Price') <sup
															class="text-danger">*</sup></label>
													<div class="input-group">
														<input type="text" value="{{ old('price') }}"
															   name="price"
															   class="form-control @error('price') is-invalid @enderror"
															   autocomplete="off" required>
														<div class="input-group-prepend">
															<span class="form-control currencyCode">{{optional(auth()->user()->storeCurrency)->code}}</span>
														</div>
														<div class="invalid-feedback">
															@error('price') @lang($message) @enderror
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
														multiple="multiple" name="attribute[]" required>
														@forelse($productsAttrs as $productsAttr)
															<option value="{{$productsAttr->id}}"
																{{old('attributes') == $productsAttr->id ? 'selected':''}}>{{$productsAttr->name}}</option>
														@empty
														@endforelse
													</select>
													<div class="invalid-feedback mt-3">
														@error('attributes') @lang($message) @enderror
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label for="SKU">@lang('SKU') <sup
															class="text-danger">*</sup></label>
													<input type="text" value="{{ old('sku') }}"
														   name="sku"
														   class="form-control @error('sku') is-invalid @enderror"
														   autocomplete="off" required>
													<div class="invalid-feedback">
														@error('sku') @lang($message) @enderror
													</div>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-4">
												<div class="form-group">
													<label for="Tag">@lang('Tag')</label>
													<input type="text" value="{{ old('tag') }}"
														   name="tag"
														   class="form-control @error('tag') is-invalid @enderror"
														   autocomplete="off" required>
													<div class="invalid-feedback">
														@error('tag') @lang($message) @enderror
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<label>@lang('Status')</label>
												<div class="selectgroup w-100">
													<label class="selectgroup-item">
														<input type="radio" name="status"
															   value="0"
															   class="selectgroup-input">
														<span class="selectgroup-button">@lang('OFF')</span>
													</label>
													<label class="selectgroup-item">
														<input type="radio" name="status"
															   value="1"
															   class="selectgroup-input" checked>
														<span class="selectgroup-button">@lang('ON')</span>
													</label>
												</div>
											</div>
											<div class="col-md-4 mt-4">
												<button type="button"
														class="btn btn-success btn-sm float-right" id="generate"><i
														class="fa fa-plus-circle"></i>
													@lang('Add Product Image')</button>
											</div>
										</div>
										<div class="row addedField">
											<div class="col-sm-12 col-md-4 image-column mb-4">
												<label>@lang('Thumbnail Image')</label>
												<div class="form-group position-relative">
													<div class="image-input z0">
														<label for="image-upload" id="image-label"><i
																class="fas fa-upload"></i></label>
														<input type="file" id="image" name="thumbnail"
															   placeholder="@lang('Choose image')"
															   class="image-preview-thumb"
															   required>
														<img id="image_preview_container" class="preview-image"
															 src="{{ getFile(config('location.default2')) }}"
															 alt="@lang('preview image')">

													</div>
												</div>
												@if(config("location.product.thumbnail"))
													<span
														class="text-warning">{{trans('Image size should be')}} {{config("location.product.thumbnail")}} {{trans('px')}}</span>
												@endif
											</div>
										</div>
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label
														for="Description">@lang('Description')</label>
													<textarea
														class="form-control summernote"
														name="description"
														rows="5"></textarea>
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
														rows="5"></textarea>
													<div class="invalid-feedback">
														@error('description') @lang($message) @enderror
													</div>
												</div>
											</div>
										</div>
										<button type="submit"
												class="btn btn-primary btn-block btn-sm">@lang('Create Product')</button>
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

		$("#generate").on('click', function () {
			var form = `<div class="col-sm-12 col-md-4 image-column mb-4">
			<label>@lang('Product Image')</label>
                                <div class="form-group position-relative">
                                        <div class="image-input z0">
                                            <label for="image-upload" id="image-label"><i class="fas fa-upload"></i></label>
                                            <input type="file" id="image" name="image[]" placeholder="@lang('Choose image')" class="image-preview" required>
                                            <img id="image_preview_container" class="preview-image"	src="{{ getFile(config('location.default2')) }}" alt="@lang('preview image')">

                                        </div>

                                         <button class="btn btn-danger delete_desc removeFile z9" type="button">
                                                <i class="fa fa-times"></i>
                                        </button>

                                </div>
                                       @if(config("location.product.size"))
			<span class="text-warning">{{trans('Image size should be')}} {{config("location.product.size")}} {{trans('px')}}</span>
                                        @endif
			</div> `;
			$('.addedField').append(form)

			$(document).on('click', '.delete_desc', function () {
				$(this).closest('.form-group').parents('.image-column').remove();
			});
		});
		$(document).on('change', '.image-preview', function () {
			let currentIndex = $('.image-preview').index(this);
			$(this).attr('name', `image[${currentIndex}]`);
			let reader = new FileReader();
			let _this = this;
			reader.onload = (e) => {
				$(_this).siblings('.preview-image').attr('src', e.target.result);
			}
			reader.readAsDataURL(this.files[0]);
		});

		$(document).on('change', '.image-preview-thumb', function () {
			let reader = new FileReader();
			reader.onload = (e) => {
				$('#image_preview_container').attr('src', e.target.result);
			}
			reader.readAsDataURL(this.files[0]);
		});
	</script>
@endsection

