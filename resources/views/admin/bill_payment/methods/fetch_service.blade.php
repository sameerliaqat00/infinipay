@extends('admin.layouts.master')
@section('page_title',__('Service List'))
@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Service List')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Service List')</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row justify-content-md-center">
						<div class="col-lg-12">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Service List')</h6>
									<button class="btn btn-primary bulk-yes">@lang('Bulk Add')</button>
								</div>

								<div class="card-body">
									<div class="table-responsive">
										<table class="table table-striped table-hover align-items-center table-flush">
											<thead class="thead-light">
											<tr>
												<th scope="col" class="text-center">
													<input type="checkbox" class="form-check-input tic-check"
														   name="check-all"
														   id="check-all">
													<label for="check-all"></label>
												</th>
												<th>@lang('Code')</th>
												<th>@lang('Name')</th>
												<th>@lang('Country')</th>
												<th>@lang('Action')</th>
											</tr>
											</thead>
											<tbody>
											@if($services)
												@foreach($services as $key => $value)
													<tr>
														<td class="text-center">
															<input type="checkbox" id="chk-{{ $value->id }}"
																   class="form-check-input row-tic tic-check"
																   name="check"
																   value="{{json_encode($value)}}"
																   data-id="{{json_encode($value)}}">
															<label for="chk-{{ $value->id }}"></label>
														</td>
														<td data-label="@lang('Code')">{{ __($value->biller_code) }}</td>
														<td data-label="@lang('Min limit')">{{ __($value->name) }}</td>
														<td data-label="@lang('Country')">{{ __($value->country) }}</td>
														<td data-label="@lang('Action')">
															<button
																class="btn btn-sm btn-outline-primary"
																id="singleAdd"
																data-resource="{{json_encode($value)}}"><i
																	class="fas fa-plus-circle"></i> @lang('Add')
															</button>
														</td>
													</tr>
												@endforeach
											@endif
											</tbody>
										</table>
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
@push('extra_scripts')
	<script>
		'use strict'
		var api_service = '{{$api_service}}'
		$(document).on('click', '.bulk-yes', function (e) {
			e.preventDefault();
			var allVals = [];
			$(".row-tic:checked").each(function () {
				allVals.push($(this).attr('data-id'));
			});

			var res = allVals;

			$.ajax({
				headers: {'X-CSRF-TOKEN': $('meta[name="csrf_token"]').attr('content')},
				url: "{{ route('bill.add.bulk.service') }}",
				data: {res: res, api_service: api_service},
				dataType: 'json',
				type: "post",
				success: function (data) {
					if (data.status == 'success') {
						window.location.href = data.route;
					}
					if (data.status == 'error') {
						Notiflix.Notify.Failure("You are not selected any value");
					}
				},
			});
		});

		$(document).on('click', '#singleAdd', function () {
			var res = $(this).data('resource');
			var _this = this;

			$.ajax({
				headers: {'X-CSRF-TOKEN': $('meta[name="csrf_token"]').attr('content')},
				url: "{{ route('bill.add.service') }}",
				data: {res: res, api_service: api_service},
				dataType: 'json',
				type: "post",
				success: function (data) {
					if (data.status == 'success') {
						$(_this).attr('class', 'btn btn-sm btn-success')
						$(_this).prop("disabled", true)
						$(_this).text('Added')
						Notiflix.Notify.Success("Successfully Added");
					}
				}
			});
		});

		$(document).on('click', '#check-all', function () {
			$('input:checkbox').not(this).prop('checked', this.checked);
		});

		$(document).on('change', ".row-tic", function () {
			let length = $(".row-tic").length;
			let checkedLength = $(".row-tic:checked").length;
			if (length == checkedLength) {
				$('#check-all').prop('checked', true);
			} else {
				$('#check-all').prop('checked', false);
			}
		});
	</script>
@endpush
