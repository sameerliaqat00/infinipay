<div class="table-responsive mt-5">
	<table class="table table-striped table-hover align-items-center table-borderless">
		<thead class="thead-light">
		<tr>
			<th class="col-5">@lang('Title')</th>
			<th class="col-3">@lang('Quantity')</th>
			<th class="col-3">@lang('Price')</th>
			<th class="col-1" class="text-end">
				@lang('Action')
			</th>
		</tr>
		</thead>
		<tbody>
		<tr v-for="(item, index) in invoice.items">
			<td> @{{ item.title }}</td>
			<td> @{{ item.quantity }}</td>
			<td>{{config('basic.currency_symbol')}}@{{ item.price }}</td>
			<td class="action">
				<div class="d-flex justify-content-end">
					<button type="button"
							@click.prevent="editItem(index)"
							data-toggle="modal"
							data-target="#editModal"
							class="btn-outline-primary">
						<i class="fas fa-edit"
						   aria-hidden="true"></i>
					</button>
					<button type="button"
							@click.prevent="removeItem(index)"
							data-toggle="modal"
							data-target="#describeModal"
							class="btn-outline-danger">
						<i class="fas fa-trash" aria-hidden="true"></i>
					</button>
				</div>
			</td>
		</tr>
		<tr class="estimation top">
			<td></td>
			<td><span>@lang('Subtotal')</span></td>
			<td>
				<div class="input-group">
					<input type="text" v-model="subtotal" class="form-control" placeholder="" readonly/>
					<div class="input-group-append">
						<label class="form-control">@{{code}}</label>
					</div>
				</div>
			</td>
			<td></td>
		</tr>
		<tr class="estimation">
			<td></td>
			<td><span>@lang('Tax')</span></td>
			<td>
				<div class="input-group">
					<input type="number" v-model="item.tax" step="0.001" v-on:keyup="calculateTax" class="form-control"
						   placeholder=""/>
					<div class="input-group-append">
						<label class="form-control">%</label>
					</div>
				</div>
			</td>
			<td></td>
		</tr>
		<tr class="estimation">
			<td></td>
			<td><span>@lang('Vat')</span></td>
			<td>
				<div class="input-group">
					<input type="number" v-model="item.vat" step="0.001" v-on:keyup="calculateVat" class="form-control"
						   placeholder=""/>
					<div class="input-group-append">
						<label class="form-control">%</label>
					</div>
				</div>
			</td>
			<td></td>
		</tr>
		<tr class="estimation">
			<td></td>
			<td><span>@lang('Grandtotal')</span></td>
			<td>
				<span>@{{ symbol }}@{{grandTotal | decimalFiltered}}</span>
			</td>
			<td></td>
		</tr>
		</tbody>
	</table>
</div>
<div class="row">
	<div class="col-md-12">
		<button type="button" class="btn btn-primary btn-sm btn-block"
				@click="saveInvoice('send')">
			@lang('Save and Send')
		</button>
	</div>
</div>
