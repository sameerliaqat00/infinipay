<div class="row">
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('User')" name="user"
				   value="{{@request()->user}}" type="text"
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
					value="pending" {{ @request()->status == 'pending' ? 'selected' : '' }}>@lang('Pending')</option>
				<option
					value="re-submitted" {{ @request()->status == 're-submitted' ? 'selected' : '' }}>@lang('ReSubmitted')</option>
				<option
					value="rejected" {{  @request()->status == 'rejected' ? 'selected' : '' }}>@lang('Rejected')</option>
			</select>
		</div>
	</div>
	<div class="col-md-3">
		<div class="form-group">
			<input placeholder="@lang('Created At')" name="created_at" id="created_at"
				   value="{{ @request()->created_at }}" type="date" class="form-control form-control-sm"
				   autocomplete="off">
		</div>
	</div>
	<div class="col-md-3">
		<div class="form-group">
			<button type="submit" class="btn btn-primary btn-sm btn-block">@lang('Search')</button>
		</div>
	</div>
</div>
