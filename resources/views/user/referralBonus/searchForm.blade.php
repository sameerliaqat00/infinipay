<div class="row">
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('From')" name="sender" value="{{ $search['sender'] ?? '' }}" type="text" class="form-control form-control-sm">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('To')" name="receiver" value="{{ $search['receiver'] ?? '' }}" type="text" class="form-control form-control-sm">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('Transaction ID')" name="utr" value="{{ $search['utr'] ?? '' }}" type="text" class="form-control form-control-sm">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('Min Amount')" name="min" value="{{ $search['min'] ?? '' }}" type="text" class="form-control form-control-sm">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('Maximum Amount')" name="max" value="{{ $search['max'] ?? '' }}" type="text" class="form-control form-control-sm">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('Transaction Date')" name="created_at" id="created_at" value="{{ $search['created_at'] ?? '' }}" type="date" class="form-control form-control-sm" autocomplete="off">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<button type="submit" class="btn btn-primary btn-sm btn-block">@lang('Search')</button>
		</div>
	</div>
</div>
