<div class="row">
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('User')" name="user"
				   value="{{@request()->user}}" type="text"
				   class="form-control form-control-sm">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('Card Number')" name="card_number"
				   value="{{@request()->card_number}}" type="text"
				   class="form-control form-control-sm">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group search-currency-dropdown">
			<select name="method_id" class="form-control form-control-sm">
				<option value="">@lang('All Methods')</option>
				@foreach($virtualCardMethods as $key => $singleMethod)
					<option
						value="{{ $singleMethod->id }}" {{ @request()->method_id == $singleMethod->id ? 'selected' : '' }}> {{ __($singleMethod->name) }}</option>
				@endforeach
			</select>
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group search-currency-dropdown">
			<select name="status" class="form-control form-control-sm">
				<option value="">@lang('All Status')</option>
				<option
					value="approved" {{ @request()->status == 'approved' ? 'selected' : '' }}>@lang('Approved')</option>
				<option
					value="rejected" {{ @request()->status == 'rejected' ? 'selected' : '' }}>@lang('Rejected')</option>
				<option
					value="block-request" {{ @request()->status == 'block-request' ? 'selected' : '' }}>@lang('Block Request')</option>
				<option
					value="fund-rejected" {{  @request()->status == 'fund-rejected' ? 'selected' : '' }}>@lang('Fund Rejected')</option>
				<option
					value="block" {{  @request()->status == 'block' ? 'selected' : '' }}>@lang('Block')</option>
				<option
					value="add-fund-request" {{  @request()->status == 'add-fund-request' ? 'selected' : '' }}>@lang('Add Fund Request')</option>
				<option
					value="inactive" {{  @request()->status == 'inactive' ? 'selected' : '' }}>@lang('Inactive')</option>
			</select>
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('Expiry Date')" name="expiry_date" id="created_at"
				   value="{{ @request()->expiry_date }}" type="date" class="form-control form-control-sm"
				   autocomplete="off">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<button type="submit" class="btn btn-primary btn-sm btn-block">@lang('Search')</button>
		</div>
	</div>
</div>
