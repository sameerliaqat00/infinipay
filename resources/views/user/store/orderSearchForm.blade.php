<div class="row">
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('Order Number')" name="orderNumber" value="{{@request()->orderNumber}}"
				   type="text"
				   class="form-control form-control-sm">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('Email')" name="email" value="{{@request()->email}}" type="text"
				   class="form-control form-control-sm">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('Amount')" name="amount" value="{{@request()->amount}}" type="text"
				   class="form-control form-control-sm">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group search-currency-dropdown">
			<select name="stage" class="form-control form-control-sm">
				<option value="">@lang('All Status')</option>
				<option
					value="0" {{ @request()->status == '0' ? 'selected' : '' }}>@lang('New Arrival')</option>
				<option
					value="1" {{ @request()->status == '1' ? 'selected' : '' }}>@lang('Processing')</option>
				<option
					value="2" {{ @request()->status == '2' ? 'selected' : '' }}>@lang('On Shipping')</option>
				<option
					value="3" {{ @request()->status == '3' ? 'selected' : '' }}>@lang('Out For Delivery')</option>
				<option
					value="4" {{ @request()->status == '4' ? 'selected' : '' }}>@lang('Delivered')</option>
				<option
					value="5" {{ @request()->status == '5' ? 'selected' : '' }}>@lang('Cancel')</option>
			</select>
		</div>
	</div>
	<div class="col-md-2">
		<div class="input-box form-group mb-2">
			<input type="date" class="form-control" name="datetrx" id="datepicker"/>
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<button type="submit" class="btn btn-primary btn-sm btn-block">@lang('Search')</button>
		</div>
	</div>
</div>
