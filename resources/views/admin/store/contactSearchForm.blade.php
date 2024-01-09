<div class="row">
	<div class="col-md-3">
		<div class="form-group">
			<input placeholder="@lang('Username')" name="username" value="{{@request()->username}}" type="text"
				   class="form-control form-control-sm">
		</div>
	</div>
	<div class="col-md-3">
		<div class="form-group search-currency-dropdown">
			<select name="store_id" class="form-control form-control-sm">
				<option value="">@lang('All Store')</option>
				@foreach($stores as $key => $store)
					<option
						value="{{ $store->id }}" {{ @request()->store_id == $store->id ? 'selected' : '' }}> {{$store->name}} </option>
				@endforeach
			</select>
		</div>
	</div>
	<div class="col-md-3">
		<div class="form-group">
			<input placeholder="@lang('Sender Name')" name="sender_name" value="{{@request()->sender_name}}" type="text"
				   class="form-control form-control-sm">
		</div>
	</div>
	<div class="col-md-3">
		<div class="form-group">
			<button type="submit" class="btn btn-primary btn-sm btn-block">@lang('Search')</button>
		</div>
	</div>
</div>
