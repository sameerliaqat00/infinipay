@extends('user.layouts.master')
@section('page_title',__('Product Stock Create'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Product Stock Create')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Product Stock Create')</div>
				</div>
			</div>
			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row justify-content-md-center">
						<div class="col-md-12">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Product Stock Create')</h6>
								</div>
								<div class="card-body">
									<form action="{{ route('stock.create') }}" method="post">
										@csrf
										<div class="row">
											<div class="col-md-4">
												<div class="form-group">
													<label for="Product">@lang('Product') <sup
															class="text-danger">*</sup></label>
													<select name="product" id="selectProduct"
															class="form-control select2 form-control-sm productSelect @error('product') is-invalid @enderror"
															required>
														<option disabled selected
																value="">@lang('Select Product')</option>
														@forelse($products as $product)
															<option
																value="{{$product->id}}" {{old('product') == $product->id ? 'selected':''}}>{{$product->name}}</option>
														@empty
														@endforelse
													</select>
													<div class="invalid-feedback">
														@error('product') @lang($message) @enderror
													</div>
												</div>
											</div>
										</div>
										<div class="row addedField mt-3">
										</div>
										<button type="submit"
												class="btn btn-primary btn-lg">@lang('Create Stock')</button>
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
	<link rel="stylesheet" href="{{ asset('assets/store/css/select2.min.css') }}">
@endpush
@section('scripts')
	<script src="{{ asset('assets/store/js/select2.min.js') }}"></script>
	<script>
		'use strict';
		var productId;

		$(document).on('change', '.productSelect', function () {
			productId = $(this).val();
			attrCollect(productId);
		});

		$(document).on('click', '.delete_desc', function () {
			$(this).closest('.input-group').parent().remove();
		});

		function attrCollect(productId) {
			$('.addedField').html('');
			$.ajax({
				url: "{{ route('stock.attr.fetch') }}",
				method: "get",
				data: {
					productId: productId,
				},
				success: function (res) {
					if (res.status == 'success') {
						attrAppend(res.data);
					}
				}
			});
		}

		function attrAppend(data) {
			$('.addedField').append(data)
		}

		$(document).ready(function () {
			$('select').select2({
				selectOnClose: true
			});
		});


		$(document).on('click', '.removeContentDiv', function () {
			var sss = $(this).closest('.column-form').remove();
		});


		$(document).on('click', '.copyFormData', function () {
			var sss = $(this).closest('.column-form').clone();
			var $len = parseInt($('.column-form').length);

			$(sss).find('.attrId').attr('name', 'attrName[' + $len + '][]');
			$(".addedField").append(sss);
			sss.find('.removeContentDiv').css('display', 'initial')
			sss.find('.copyFormData').css('display', 'none')
		})

		$(document).on('click', '.removeFormData', function () {
			var sss = $(this).closest('.row').remove();
		});

	</script>
@endsection

