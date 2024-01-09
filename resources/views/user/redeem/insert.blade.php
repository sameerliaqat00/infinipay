@extends('user.layouts.master')
@section('page_title',__('Insert Redeem Code'))

@section('content')
<div class="main-content">
	<section class="section">
		<div class="section-header">
			<h1>@lang('Insert Redeem Code')</h1>
			<div class="section-header-breadcrumb">
				<div class="breadcrumb-item active">
					<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
				</div>
				<div class="breadcrumb-item">@lang('Insert Redeem Code')</div>
			</div>
		</div>

		<div class="row mb-3">
			<div class="container-fluid" id="container-wrapper">
				<div class="row justify-content-md-center">
					<div class="col-md-12">
						<div class="card mb-4 card-primary shadow">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
								<h6 class="m-0 font-weight-bold text-primary">@lang('Insert Redeem Code')</h6>
							</div>
							<div class="card-body">
								<form action="{{ route('redeem.insert') }}" method="post">
									@csrf
									<div class="row">
										<div class="col-md-12">
											<div class="form-group">
												<label for="utr">@lang('Redeem Code')</label>
												<input type="text" id="redeemCode" value="{{ old('redeemCode') }}"
													   name="redeemCode" placeholder="@lang('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx')"
													   class="form-control @error('redeemCode') is-invalid @enderror"
													   autocomplete="off">
												<div class="invalid-feedback">
													@error('redeemCode') @lang($message) @enderror
												</div>
												<div class="valid-feedback"></div>
											</div>
										</div>
									</div>
									<button type="submit" id="submit" class="btn btn-primary btn-sm btn-block" disabled>@lang('Submit redeem code')</button>
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
        'use strict';
        $(document).ready(function () {
            let redeemCodeField = $('#redeemCode');
            let redeemCodeStatus = false;

            function clearMessage(fieldId) {
                $(fieldId).removeClass('is-valid');
                $(fieldId).removeClass('is-invalid');
                $(fieldId).closest('div').find(".invalid-feedback").html('');
                $(fieldId).closest('div').find(".is-valid").html('');
            }

            $(document).on('input', "#redeemCode", function (e) {
                let redeemCode = redeemCodeField.val();

                if (redeemCode.length === 36) {
                    clearMessage(redeemCodeField);
                    $(redeemCodeField).addClass('is-valid');
                    $(redeemCodeField).closest('div').find(".valid-feedback").html('okay');
                    redeemCodeStatus = true;
                    submitButton();
                } else {
                    redeemCodeStatus = false;
                    submitButton();
                    clearMessage(redeemCodeStatus);
                    $(redeemCodeField).addClass('is-invalid');
                    $(redeemCodeField).closest('div').find(".invalid-feedback").html('invalid redeem code length');
                }
            });

            function submitButton() {
                if (redeemCodeStatus) {
                    $("#submit").removeAttr("disabled");
                } else {
                    $("#submit").attr("disabled", true);
                }
            }
        })
    </script>
@endsection
