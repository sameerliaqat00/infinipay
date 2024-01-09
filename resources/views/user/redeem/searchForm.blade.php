<div class="row">
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('Sender')" name="sender" value="{{ $search['sender'] ?? '' }}" type="text" class="form-control form-control-sm">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('Receiver')" name="receiver" value="{{ $search['receiver'] ?? '' }}" type="text" class="form-control form-control-sm">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('E-mail')" name="email" value="{{ $search['email'] ?? '' }}" type="text" class="form-control form-control-sm">
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
		<div class="form-group search-currency-dropdown">
			<select name="currency_id" class="form-control form-control-sm">
				<option value="">@lang('All Currency')</option>
				@foreach($currencies as $key => $currency)
					<option value="{{ $currency->id }}" {{ isset($search['currency_id']) && $search['currency_id'] == $currency->id ? 'selected' : '' }}> {{ __($currency->code) }} - {{ __($currency->name) }} </option>
				@endforeach
			</select>
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group search-currency-dropdown">
			<select name="type" class="form-control form-control-sm">
				<option value="">@lang('All Type')</option>
				<option value="sent" {{ isset($search['type']) && $search['type'] == 'sent' ? 'selected' : '' }}>@lang('Sent')</option>
				<option value="received" {{ isset($search['type']) && $search['type'] == 'received' ? 'selected' : '' }}>@lang('Received')</option>
			</select>
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group search-currency-dropdown">
			<select name="status" class="form-control form-control-sm">
				<option value="">@lang('All Status')</option>
				<option value="0" {{ isset($search['status']) && $search['status'] == '0' ? 'selected' : '' }}>@lang('Pending')</option>
				<option value="1" {{ isset($search['status']) && $search['status'] == '1' ? 'selected' : '' }}>@lang('Unused')</option>
				<option value="2" {{ isset($search['status']) && $search['status'] == '2' ? 'selected' : '' }}>@lang('Used')</option>
			</select>
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<button type="submit" class="btn btn-primary btn-sm btn-block">@lang('Search')</button>
		</div>
	</div>
</div>
