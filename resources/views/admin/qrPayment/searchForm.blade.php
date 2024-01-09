<div class="row">
	<div class="col-md-3">
		<div class="form-group">
			<input placeholder="@lang('Sender Email')" name="email" value="{{@request()->email}}" type="text"
				   class="form-control form-control-sm">
		</div>
	</div>
	<div class="col-md-3">
		<div class="form-group">
			<input placeholder="@lang('Created Date')" name="datetrx" id="created_at"
				   value="{{ $search['created_at'] ?? '' }}" type="date" class="form-control form-control-sm"
				   autocomplete="off">
		</div>
	</div>

	<div class="col-md-3">
		<div class="form-group search-currency-dropdown">
			<select name="gateway" class="form-control form-control-sm">
				<option value="">@lang('All Gateways')</option>
				@if($gateways)
					@foreach($gateways as $item)
						<option
							value="{{$item->id}}" {{ @request()->gateway == $item->id ? 'selected' : '' }}> {{$item->name}} </option>
					@endforeach
				@endif
			</select>
		</div>
	</div>
	<div class="col-md-3">
		<div class="form-group">
			<button type="submit" class="btn btn-primary btn-sm btn-block">@lang('Search')</button>
		</div>
	</div>
</div>
