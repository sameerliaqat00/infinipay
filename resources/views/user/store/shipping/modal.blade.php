<div id="addShipping" class="modal fade" data-backdrop="static" tabindex="-1" role="dialog"
	 aria-labelledby="primary-header-modalLabel"
	 aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="primary-header-modalLabel">@lang('New Shipping Charge')</h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			</div>
			<form>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							<label>@lang('Store')</label>
							<div class="form-group">
								<select class="form-control" v-model="item.store">
									<option value="" disabled>@lang('Select Store')</option>
									<option v-for="(item, index) in stores" :value="item.id">@{{ item.name }}
									</option>
								</select>
								<span class="text-danger">@{{ storeError }}</span>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<label>@lang('Address')</label>
							<div class="form-group">
								<input type="text" v-model="item.address" class="form-control">
								<span class="text-danger">@{{ addressError }}</span>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<label>@lang('Charge')</label>
							<div class="input-group">
								<input type="text" v-model="item.charge" class="form-control">
								<div class="input-group-prepend">
									<span class="form-control">{{ optional(auth()->user()->storeCurrency)->code }}</span>
								</div>
							</div>
							<span class="text-danger">@{{ chargeError }}</span>
						</div>
					</div>
					<div class="row mt-4">
						<div class="col-md-12">
							<label>@lang('Status')</label>
							<div class="selectgroup w-100">
								<label class="selectgroup-item">
									<input type="radio" name="status" v-model="item.status" value="0"
										   class="selectgroup-input">
									<span class="selectgroup-button">@lang('OFF')</span>
								</label>
								<label class="selectgroup-item">
									<input type="radio" name="status" v-model="item.status" value="1"
										   class="selectgroup-input" :checked="item.status">
									<span class="selectgroup-button">@lang('ON')</span>
								</label>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
					<button type="button" @click="save" class="btn btn-primary">@lang('Submit')</button>
				</div>
			</form>
		</div>
	</div>
</div>
<div id="editCategory" class="modal fade" data-backdrop="static" tabindex="-1" role="dialog"
	 aria-labelledby="primary-header-modalLabel"
	 aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="primary-header-modalLabel">@lang('Update Shipping Charge')</h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			</div>
			<form>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							<label>@lang('Store')</label>
							<div class="form-group">
								<select class="form-control" v-model="item.store">
									<option value="" disabled>@lang('Select Store')</option>
									<option v-for="(item, index) in stores" :value="item.id">@{{ item.name }}
									</option>
								</select>
								<span class="text-danger">@{{ storeError }}</span>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<label>@lang('Address')</label>
							<div class="form-group">
								<input type="text" v-model="item.address" class="form-control">
								<span class="text-danger">@{{ addressError }}</span>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<label>@lang('Charge')</label>
							<div class="input-group">
								<input type="text" v-model="item.charge" class="form-control">
								<div class="input-group-prepend">
									<span class="form-control">{{ optional(auth()->user()->storeCurrency)->code }}</span>
								</div>
							</div>
							<span class="text-danger">@{{ chargeError }}</span>
						</div>
					</div>
					<div class="row mt-3">
						<div class="col-md-12">
							<label>@lang('Status')</label>
							<div class="selectgroup w-100">
								<label class="selectgroup-item">
									<input type="radio" name="status" v-model="item.status" value="0"
										   class="selectgroup-input">
									<span class="selectgroup-button">@lang('OFF')</span>
								</label>
								<label class="selectgroup-item">
									<input type="radio" name="status" v-model="item.status" value="1"
										   class="selectgroup-input" :checked="item.status">
									<span class="selectgroup-button">@lang('ON')</span>
								</label>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
					<button type="button" @click="update" class="btn btn-primary">@lang('Update')</button>
				</div>
			</form>
		</div>
	</div>
</div>
<div id="categoryDelete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
	 aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title text-danger"
					id="primary-header-modalLabel">@lang('Shipping Delete Confirmation')</h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			</div>
			@csrf
			<div class="modal-body">
				<p>@lang('Are you want to delete this shipping?')</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
				<form action="" method="post" class="deleteShippingForm">
					@csrf
					@method('delete')
					<button type="submit" class="btn btn-primary">@lang('Submit')</button>
				</form>
			</div>
		</div>
	</div>
</div>
