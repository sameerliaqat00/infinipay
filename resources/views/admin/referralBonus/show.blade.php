@extends('admin.layouts.master')
@section('page_title', __('Referral Bonus'))

@section('content')
<div class="main-content">
	<section class="section">
		<div class="section-header">
			<h1>@lang('Referral Bonus')</h1>
			<div class="section-header-breadcrumb">
				<div class="breadcrumb-item active">
					<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
				</div>
				<div class="breadcrumb-item">@lang('Referral Bonus')</div>
			</div>
		</div>

		<div class="row mb-3">
			<div class="container-fluid" id="container-wrapper">
				<div class="row justify-content-md-center">
					<div class="col-lg-12">
						<div class="card mb-4 card-primary shadow">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
								<h6 class="m-0 font-weight-bold text-primary">@lang('Bonus on Add Fund')</h6>
							</div>
							<div class="card-body parent">
								<div class="table-responsive--sm mt-2 mb-2">
									<table class="table table-striped table-hover table--light style--two">
										<thead>
										<tr>
											<th>@lang('Level')</th>
											<th>@lang('Commission Amount')</th>
											<th>@lang('Minimum Amount')</th>
											<th>@lang('Status')</th>
											<th>@lang('Type')</th>
										</tr>
										</thead>
										<tbody>
										@foreach($referralBonuses->where('referral_on','deposit') as $k => $v)
											<tr>
												<td data-label="@lang('Level')">@lang('Level')# {{ __($v->level) }}</td>
												<td data-label="@lang('Commission')">{{ (getAmount($v->amount)) }}</td>
												<td data-label="@lang('Minimum')">{{ (getAmount($v->minAmount)) }}</td>
												<td data-label="@lang('Status')">
													@if($v->status)
														@lang('Active')
													@else
														@lang('Deactive')
													@endif
												</td>
												<td data-label="Type">
													@if($v->Type)
														@lang('Fixed')
													@else
														@lang('Percent')
													@endif
												</td>
											</tr>
										@endforeach
										</tbody>
									</table>
								</div>
								<hr>
								<div class="row mt-3 mb-5">
									<div class="col-md-6 mt-2">
										<input type="number" name="level" value="1" placeholder="@lang('HOW MANY LEVEL')"
											   class="form-control levelGenerate">
									</div>
									<div class="col-md-6 mt-2">
										<button type="button" class="btn btn-sm btn-primary btn-block generate">
											@lang('GENERATE')
										</button>
									</div>
								</div>
								<form action="{{route('admin.referral.bonus.index')}}" method="post">
									@csrf
									<input type="hidden" name="referral_on" class="referral_on" value="deposit">
									<div class="d-none levelForm">
										<div class="form-group">
											<label class="text-success"> @lang('Level & Commission :')
												<small>@lang('(Old Levels will Remove After Generate)')</small>
											</label>
											<div class="row">
												<div class="col-md-12">
													<div class="description referral-desc">
														<div class="row">
															<div class="col-md-12 planDescriptionContainer">
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<button type="submit"
												class="btn btn-sm btn-primary btn-block my-3">@lang('Save Changes')</button>
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
@section('scripts')
    <script>
        "use strict";
        $(document).ready(function () {
            $(document).on('click', '.generate', function () {
                let levelGenerate = $(this).parents('.parent').find('.levelGenerate').val();
                let referral_on = $(this).parents('.parent').find('.referral_on').val();
                let a = 0;
                let val = 1;
                let viewHtml = '';
                if (levelGenerate !== '' && levelGenerate > 0) {
                    $(this).parents('.parent').find('.levelForm').removeClass('d-none');
                    $(this).parents('.parent').find('.levelForm').addClass('d-block');
                    for (a; a < parseInt(levelGenerate); a++) {
                        viewHtml += `<div class="input-group input-group-sm mt-4">
                            <div class="input-group-prepend">
                                <span class="input-group-text no-right-border">@lang('LEVEL')</span>
                            </div>
                            <input name="level[]" class="form-control margin-top-10 no-left-border width-120" type="number" readonly value="${val++}" required placeholder="@lang('Level')">
                            <input name="amount[]" class="form-control margin-top-10" type="text" required placeholder="@lang('Amount')">

                          	<select name="status[]" class="form-control form-control-sm">
                          	<option value="1">@lang('Active')</option>
                          	<option value="0">@lang('Deactive')</option>
							</select>
							${(referral_on !== 'login') ?
                            `<input name="minAmount[]" class="form-control margin-top-10" type="text" required placeholder="@lang('Min Amount')">
								<select name="calcType[]" class="form-control form-control-sm">
									<option value="1">@lang('Fixed')</option>
									<option value="0">@lang('Percent')</option>
								</select>` : `<input type="hidden" name="calcType[]" value="1"><input type="hidden" name="minAmount[]" value="0">`}
							<span class="input-group-btn">
							<button class="btn btn-sm btn-danger margin-top-10 delete_desc" type="button"><i class='fa fa-minus'></i></button></span>
							</div>`;
                    }
                    $(this).parents('.parent').find('.planDescriptionContainer').html(viewHtml);
                } else {
                    alert('Level Field Is Required');
                }
            });

            $(document).on('click', '.delete_desc', function () {
                $(this).closest('.input-group').remove();
            });
        });
    </script>
@endsection
