@extends('user.layouts.master')
@section('page_title',__('Tickets Log'))

@section('content')
<div class="main-content">
	<section class="section">
		<div class="section-header">
			<h1>@lang('Tickets Log')</h1>
			<div class="section-header-breadcrumb">
				<div class="breadcrumb-item active">
					<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
				</div>
				<div class="breadcrumb-item">@lang('Tickets Log')</div>
			</div>
		</div>

		<div class="row mb-3">
			<div class="container-fluid" id="container-wrapper">
				<div class="row">
					<div class="col-sm-12">
						<div class="card mb-4 card-primary shadow">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
								<h6 class="m-0 font-weight-bold text-primary">@lang('Tickets Log')</h6>
								<a href="{{ route('user.ticket.create') }}" class="btn btn-sm btn-outline-primary"><i
											class="fas fa-plus"></i> @lang('Create new ticket')</a>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table class="table table-striped table-hover align-items-center table-borderless">
										<thead class="thead-light">
										<tr>
											<th>@lang('Subject')</th>
											<th>@lang('Status')</th>
											<th>@lang('Last Reply')</th>
											<th>@lang('Action')</th>
										</tr>
										</thead>
										<tbody>
										@forelse($tickets as $key => $ticket)
											<tr>
												<td data-label="@lang('Subject')">
													[{{ trans('Ticket# ').__($ticket->ticket) }}] {{ __($ticket->subject) }}
												</td>
												<td data-label="@lang('Status')">
													@if($ticket->status == 0)
														<span class="badge badge-pill badge-success">@lang('Open')</span>
													@elseif($ticket->status == 1)
														<span class="badge badge-pill badge-primary">@lang('Answered')</span>
													@elseif($ticket->status == 2)
														<span class="badge badge-pill badge-warning">@lang('Replied')</span>
													@elseif($ticket->status == 3)
														<span class="badge badge-pill badge-dark">@lang('Closed')</span>
													@endif
												</td>
												<td data-label="@lang('Last Reply')">
													{{ __($ticket->last_reply->diffForHumans()) }}
												</td>
												<td data-label="@lang('Action')">
													<a href="{{ route('user.ticket.view', $ticket->ticket) }}" class="btn btn-sm btn-info">
														@lang('View')
													</a>
												</td>
											</tr>
										@empty
											<tr>
												<th colspan="100%" class="text-center">@lang('No data found')</th>
											</tr>
										@endforelse
										</tbody>
									</table>
									{{ $tickets->appends($_GET)->links() }}
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
