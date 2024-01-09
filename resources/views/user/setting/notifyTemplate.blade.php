@extends('user.layouts.master')
@section('page_title',__('Notification List'))

@section('content')
	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>@lang('Notification List')</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">@lang('Notification List')</div>
				</div>
			</div>
			<div class="row mb-3">
				<div class="container-fluid" id="container-wrapper">
					<div class="row justify-content-md-center">
						<div class="col-md-6">
							<div class="card mb-4 card-primary shadow">
								<div
									class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">@lang('Notification List')</h6>
								</div>
								<div class="card-body identity-confirmation">
									<form role="form" method="POST" action="{{route('update.setting.notify')}}"
										  enctype="multipart/form-data">
										@csrf
										@method('put')
										<div class="row g-4">
											<div class="table-parent table-responsive">
												<table class="table table-striped" id="service-table">
													<tbody>
													@forelse($templates as $key => $item)
														<tr>
															<td data-label="@lang('Notification List')">
																{{$item->name}}
															</td>
															<td>
																<div>
																	<label class="cswitch">
																		<input class="cswitch--input"
																			   name="access[]"
																			   value="{{$item->template_key}}"
																			   type="checkbox"
																			   @if(in_array($item->template_key, auth()->user()->notify_active_template??[])) checked
																			@endif
																		/><span
																			class="cswitch--trigger wrapper"></span>
																	</label>
																</div>
															</td>
														</tr>
													@empty
													@endforelse
													</tbody>
												</table>
											</div>
											<button type="submit"
													class="btn btn-primary btn-sm btn-block">@lang('Save Changes')</button>
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

