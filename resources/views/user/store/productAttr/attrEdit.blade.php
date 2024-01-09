@extends('user.layouts.master')
@section('page_title',__('Product Attribute'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Store Update')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Attribute Update')</div>
				</div>
			</div>
			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row justify-content-md-center">
						<div class="col-md-6">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Attribute Update')</h6>
								</div>
								<div class="card-body">
									<form action="{{ route('attr.edit',$storeProductAttr->id) }}" method="post">
										@csrf
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="Store Name">@lang('Attribute Name') <sup
															class="text-danger">*</sup></label>
													<input type="text"
														   name="name"
														   value="{{$storeProductAttr->name}}"
														   class="form-control @error('name') is-invalid @enderror"
														   autocomplete="off" required>
													<div class="invalid-feedback">
														@error('name') @lang($message) @enderror
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<label>@lang('Status')</label>
												<div class="selectgroup w-100">
													<label class="selectgroup-item">
														<input type="radio" name="status"
															   value="0"
															   class="selectgroup-input" {{$storeProductAttr->status == 0 ? 'checked':''}}>
														<span class="selectgroup-button">@lang('OFF')</span>
													</label>
													<label class="selectgroup-item">
														<input type="radio" name="status"
															   value="1"
															   class="selectgroup-input" {{$storeProductAttr->status == 1 ? 'checked':''}}>
														<span class="selectgroup-button">@lang('ON')</span>
													</label>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12 mb-4">
												<button type="button"
														class="btn btn-success btn-sm float-right" id="generate"><i
														class="fa fa-plus-circle"></i>
													@lang('Add Field')</button>
											</div>
										</div>
										<div class="row addedField">
											@forelse($storeProductAttr->attrLists as $info)
												<div class="col-md-6">
													<div class="form-group">
														<div class="input-group">
															<input name="field_name[]" class="form-control " type="text"
																   value="{{$info->name}}" required
																   placeholder="{{trans('Field Name')}}">
															<span class="input-group-btn">
                                                              <button class="btn btn-danger delete_desc" type="button">
                                                               <i class="fa fa-times"></i></button>
                                                           </span>
														</div>
													</div>
												</div>
											@empty
											@endforelse
										</div>
										<button type="submit"
												class="btn btn-primary btn-sm mt-3">@lang('Update Attribute')</button>
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
@section('scripts')
	<script>
		'use strict'
		$(document).on('click', '#generate', function () {
			var form = `<div class="col-md-6">
                                <div class="form-group">
                                    <div class="input-group">
                                        <input name="field_name[]" class="form-control " type="text" value="" required placeholder="{{trans('Attribute Info')}}">
                                        <span class="input-group-btn">
                                            <button class="btn btn-danger delete_desc" type="button">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </span>
                                    </div>
                                </div>
                            </div> `;

			$('.addedField').append(form)
		});

		$(document).on('click', '.delete_desc', function () {
			$(this).closest('.input-group').parent().remove();
		});
	</script>
@endsection

