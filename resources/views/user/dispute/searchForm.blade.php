<div class="row">
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('Dispute ID')" name="utr" value="{{ $search['utr'] ?? '' }}" type="text"
				   class="form-control form-control-sm">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('Created Date')" name="created_at" id="created_at"
				   value="{{ $search['created_at'] ?? '' }}" type="date" class="form-control form-control-sm"
				   autocomplete="off">
		</div>
	</div>

	<div class="col-md-2">
		<div class="form-group search-currency-dropdown">
			<select name="status" class="form-control form-control-sm">
				<option value="">@lang('All Status')</option>
				<option value="0" {{ isset($search['status']) && $search['status'] == '0' ? 'selected' : '' }}> @lang('Open') </option>
				<option value="1" {{ isset($search['status']) && $search['status'] == '1' ? 'selected' : '' }}> @lang('Solved') </option>
				<option value="2" {{ isset($search['status']) && $search['status'] == '2' ? 'selected' : '' }}> @lang('Closed') </option>
			</select>
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<button type="submit" class="btn btn-primary btn-sm btn-block">@lang('Search')</button>
		</div>
	</div>
</div>
