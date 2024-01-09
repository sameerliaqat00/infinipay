@extends('admin.layouts.master')
@section('page_title',__('Store Details'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Store Details')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Store Details')</div>
				</div>
			</div>
			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row justify-content-md-center">
						<div class="col-md-12">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Store Details')</h6>
									<a href="{{route('admin.store.list')}}" class="btn btn-primary">@lang('Back')</a>
								</div>
								<div class="card-body">
									<form>
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="Store Name">@lang('Store Name')</label>
													<input type="text"
														   value="{{$store->name}}"
														   class="form-control"
														   autocomplete="off" readonly>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="Shipping Status">@lang('Shipping Charge')</label>
													<select name="shipping_charge"
															class="form-control form-control-sm">
														<option
															value="1" {{$store->shipping_charge == '1' ? 'selected':''}}>@lang('Active')</option>
														<option
															value="0" {{$store->shipping_charge == '0' ? 'selected':''}}>@lang('Inactive')</option>
													</select>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="Delivery Note">@lang('Delivery Note')</label>
													<select name="delivery_note"
															class="form-control form-control-sm">
														<option
															value="disabled" {{$store->delivery_note == 'disabled' ? 'selected':''}}>@lang('Disable')</option>
														<option
															value="enable" {{$store->delivery_note == 'enable' ? 'selected':''}}>@lang('Enable')</option>
													</select>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="Status">@lang('Status')</label>
													<select name="status"
															class="form-control form-control-sm">
														<option
															value="1" {{$store->status == '1' ? 'selected':''}}>@lang('Active')</option>
														<option
															value="0" {{$store->status == '0' ? 'selected':''}}>@lang('Inactive')</option>
													</select>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12">
												<label for="Image">@lang('Store Link')</label>
												<div class="input-group">
													<input type="text" value="{{route('public.view')}}"
														   class="form-control" readonly>
													<div class="input-group-prepend width-50">
														<input type="text"
															   value="{{$store->link}}"
															   class="form-control" readonly>
													</div>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-6 mt-4">
												<label for="Image">@lang('Store Image')</label>
												<div class="form-group mt-2">
													<div class="fileinput fileinput-new "
														 data-provides="fileinput">
														<div class="fileinput-new thumbnail withdraw-thumbnail"
															 data-trigger="fileinput">
															<img class="w-150px"
																 src="{{ getFile(config('location.store.path').$store->image) }}"
																 alt="...">
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label for="Store Description">@lang('Store Description')</label>
													<textarea name="short_description" rows="5"
															  class="form-control form-control-sm"
															  readonly>{{$store->short_description}}</textarea>
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
@endsection


