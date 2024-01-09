<div class="row">
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('Sender')" name="sender"
				   value="{{ isset($search['sender']) ? $search['sender'] : '' }}" type="text"
				   class="form-control form-control-sm">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('E-mail')" name="email" value="{{ $search['email'] ?? '' }}" type="text"
				   class="form-control form-control-sm">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('Transaction ID')" name="hash_slug" value="{{ $search['utr'] ?? '' }}" type="text"
				   class="form-control form-control-sm">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('Min Amount')" name="min" value="{{ $search['min'] ?? '' }}" type="text"
				   class="form-control form-control-sm">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('Maximum Amount')" name="max" value="{{ $search['max'] ?? '' }}" type="text"
				   class="form-control form-control-sm">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('Transaction Date')" name="created_at" id="created_at"
				   value="{{ $search['created_at'] ?? '' }}" type="date" class="form-control form-control-sm"
				   autocomplete="off">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group search-currency-dropdown">
			<select name="currency_id" class="form-control form-control-sm">
				<option value="">@lang('All Currency')</option>
				@foreach($currencies as $key => $currency)
					<option
						value="{{ $currency->id }}" {{ isset($search['currency_id']) && $search['currency_id'] == $currency->id ? 'selected' : '' }}> {{ __($currency->code) }} @lang('-') {{ __($currency->name) }} </option>
				@endforeach
			</select>
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group search-currency-dropdown">
			<select name="status" class="form-control form-control-sm">
				<option value="">@lang('All Status')</option>
				<option
					value="paid" {{ isset($search['status']) && $search['status'] == 'paid' ? 'selected' : '' }}>@lang('Paid')</option>
				<option
					value="unpaid" {{ isset($search['status']) && $search['status'] == 'unpaid' ? 'selected' : '' }}>@lang('UnPaid')</option>
				<option
					value="rejected" {{ isset($search['status']) && $search['status'] == 'rejected' ? 'selected' : '' }}>@lang('Rejected')</option>
			</select>
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<button type="submit" class="btn btn-primary btn-sm btn-block">@lang('Search')</button>
		</div>
	</div>
</div>
