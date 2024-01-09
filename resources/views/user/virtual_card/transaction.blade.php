@extends('user.layouts.master')
@section('page_title',__('Card Transactions'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Card Transactions')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Card Transactions')</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row">
						<div class="col-lg-12">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Card Transactions')
										- {{@$cardTransactions[0]->cardOrder->card_number}}</h6>
								</div>
								<div class="card-body">
									<div class="table-responsive">
										<table
											class="table table-striped table-hover align-items-center table-borderless">
											<thead class="thead-light">
											<tr>
												<th>@lang('SL.')</th>
												<th>@lang('Provider')</th>
												<th>@lang('Amount')</th>
												<th>@lang('Type')</th>
											</tr>
											</thead>
											<tbody>
											@forelse($cardTransactions as $key => $value)
												<tr>
													<td data-label="@lang('SL.')">{{ ++$key }}</td>
													<td data-label="@lang('Provider')">{{ optional($value->cardOrder->cardMethod)->name }}</td>
													<td data-label="@lang('Amount')">{{$value->amount}} {{$value->currency_code}}</td>
													<td data-label="@lang('Type')"><span
															class="badge badge-success">@lang('Completed')</span></td>
												</tr>
											@empty
												<tr>
													<th colspan="100%" class="text-center">@lang('No data found')</th>
												</tr>
											@endforelse
											</tbody>
										</table>
									</div>
									<div class="card-footer">
										{{ $cardTransactions->links() }}
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

