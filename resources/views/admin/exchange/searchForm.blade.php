<div class="row">
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('Transaction ID')" name="utr" value="{{ isset($search['utr']) ? $search['utr'] : '' }}" type="text" class="form-control form-control-sm">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('Min Amount')" name="min" value="{{ isset($search['min']) ? $search['min'] : '' }}" type="text" class="form-control form-control-sm">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('Maximum Amount')" name="max" value="{{ isset($search['max']) ? $search['max'] : '' }}" type="text" class="form-control form-control-sm">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('Transaction Date')" name="created_at" id="created_at" value="{{ isset($search['created_at']) ? $search['created_at'] : '' }}" type="date" class="form-control form-control-sm" autocomplete="off">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group search-currency-dropdown">
			<select name="status" class="form-control form-control-sm">
				<option value="">@lang('All Status')</option>
				<option value="1" {{ isset($search['status']) && $search['status'] == 1 ? 'selected' : '' }}>@lang('Compleate')</option>
				<option value="0" {{ isset($search['status']) && $search['status'] == 0 ? 'selected' : '' }}>@lang('Pending')</option>
			</select>
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group search-currency-dropdown">
			<select name="from_wallet" class="form-control form-control-sm">
				<option value="">@lang('Select From Currency')</option>
				@foreach($wallets as $key => $value)
					<option value="{{ $value->id }}" {{ isset($search['from_wallet']) && $search['from_wallet'] == $value->id ? 'selected' : '' }}> {{ __(optional($value->currency)->code) }} @lang('-') {{ __(optional($value->currency)->name) }} </option>
				@endforeach
			</select>
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group search-currency-dropdown">
			<select name="to_wallet" class="form-control form-control-sm">
				<option value="">@lang('Select To Currency')</option>
				@foreach($wallets as $key => $value)
					<option value="{{ $value->id }}" {{ isset($search['to_wallet']) && $search['to_wallet'] == $value->id ? 'selected' : '' }}> {{ __(optional($value->currency)->code) }} @lang('-') {{ __(optional($value->currency)->name) }} </option>
				@endforeach
			</select>
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<button type="submit" class="btn btn-primary btn-sm btn-block">@lang('Search')</button>
		</div>
	</div>
</div>
