@extends('admin.layouts.master')
@section('page_title', __('Bill Details'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Bill Details')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('admin.home') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Bill Details')</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					@if($billDetails->last_api_error)
						<div
							class="media align-items-center d-flex justify-content-between alert alert-warn mb-4">
							<div>
								<i class="fas fa-exclamation-triangle"></i> @lang('Last Api error message:-') {{$billDetails->last_api_error}}
							</div>
						</div>
					@endif
					<div class="row justify-content-md-center">
						<div class="col-lg-12">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Bill Details')</h6>
									<a href="{{ route('bill.pay.list')}}" class="btn btn-sm btn-outline-primary">
										<i
											class="fas fa-arrow-left"></i> @lang('Back')</a>
								</div>
								<div class="card-body">
									<ul class="list-group">
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('User')</span><a
												href="{{route('user.edit',$billDetails->user_id)}}"><span> {{ __(optional($billDetails->user)->name ?? __('N/A')) }} </span></a>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Payment method')</span><span> {{ __(optional($billDetails->method)->methodName) }} </span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Transaction Id')</span><span> {{ __($billDetails->utr) }} </span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Status')</span>
											<span>
												@if($billDetails->status == 0)
													<span class="text-warning">@lang('Generated')</span>
												@elseif($billDetails->status == 1)
													<span class="text-info">@lang('Pending')</span>
												@elseif($billDetails->status == 2)
													<span
														class="text-info">@lang('Payment Done')</span>
												@elseif($billDetails->status == 3)
													<span class="text-success">@lang('Completed')</span>
												@elseif($billDetails->status == 4)
													<span class="text-danger">@lang('Return')</span>
												@endif
											</span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Category')</span><span> {{ __(str_replace('_',' ',ucfirst($billDetails->category_name))) }} </span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Type')</span><span> {{ __($billDetails->type) }} </span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Country')</span><span> {{ __($billDetails->country_name) }} </span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Amount')</span><span
												class="text-info"> {{ getAmount($billDetails->amount,2) }} {{$billDetails->currency}} </span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Charge')</span><span
												class="text-danger"> {{ getAmount($billDetails->charge,2) }} {{$billDetails->currency}} </span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Payable Amount')</span><span
												class="text-success"> {{ getAmount($billDetails->payable_amount,2) }} {{$billDetails->currency}} </span>
										</li>
										@if($billDetails->status == 4)
											<li class="list-group-item list-group-item-action d-flex justify-content-between">
												<span>@lang('Return Amount')</span><span
													class="text-danger"> {{ getAmount($billDetails->pay_amount_in_base,2) }} {{optional($billDetails->baseCurrency)->code??config('basic.base_currency_code')}} </span>
											</li>
										@endif
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

		</section>
	</div>
@endsection

