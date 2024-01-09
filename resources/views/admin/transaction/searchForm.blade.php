<div class="row">
    <div class="col-md-2">
        <div class="form-group">
            <input placeholder="@lang('E-mail')" name="email"
                   value="{{ isset($search['email']) ? $search['email'] : '' }}" type="text"
                   class="form-control form-control-sm">
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <input placeholder="@lang('Transaction ID')" name="utr"
                   value="{{ isset($search['utr']) ? $search['utr'] : '' }}" type="text"
                   class="form-control form-control-sm">
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <input placeholder="@lang('Min Amount')" name="min"
                   value="{{ isset($search['min']) ? $search['min'] : '' }}" type="text"
                   class="form-control form-control-sm">
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <input placeholder="@lang('Maximum Amount')" name="max"
                   value="{{ isset($search['max']) ? $search['max'] : '' }}" type="text"
                   class="form-control form-control-sm">
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <input placeholder="@lang('Transaction Date')" name="created_at" id="created_at"
                   value="{{ isset($search['created_at']) ? $search['created_at'] : '' }}" type="date"
                   class="form-control form-control-sm" autocomplete="off">
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group search-currency-dropdown">
            <select name="currency_id" class="form-control form-control-sm">
                <option value="">@lang('All Currency')</option>
                @foreach($currencies as $key => $currency)
                    <option value="{{ $currency->id }}" {{ isset($search['currency_id']) && $search['currency_id'] == $currency->id ? 'selected' : '' }}> {{ __($currency->code) }}
                        @lang('-') {{ __($currency->name) }} </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group search-currency-dropdown">
            <select name="type" class="form-control form-control-sm">
                <option value="">@lang('All Type')</option>
                <option value="Transfer" {{ isset($search['type']) && $search['type'] == 'Transfer' ? 'selected' : '' }}>@lang('Transfer')</option>
                <option value="RequestMoney" {{ isset($search['type']) && $search['type'] == 'RequestMoney' ? 'selected' : '' }}>@lang('RequestMoney')</option>
                <option value="RedeemCode" {{ isset($search['type']) && $search['type'] == 'RedeemCode' ? 'selected' : '' }}>@lang('RedeemCode')</option>
                <option value="Escrow" {{ isset($search['type']) && $search['type'] == 'Escrow' ? 'selected' : '' }}>@lang('Escrow')</option>
                <option value="Voucher" {{ isset($search['type']) && $search['type'] == 'Voucher' ? 'selected' : '' }}>@lang('Voucher')</option>
                <option value="Fund" {{ isset($search['type']) && $search['type'] == 'Fund' ? 'selected' : '' }}>@lang('Fund')</option>
                <option value="Exchange" {{ isset($search['type']) && $search['type'] == 'Exchange' ? 'selected' : '' }}>@lang('Exchange')</option>
                <option value="Invoice" {{ isset($search['type']) && $search['type'] == 'Invoice' ? 'selected' : '' }}>@lang('Invoice')</option>
                <option value="ProductOrder" {{ isset($search['type']) && $search['type'] == 'ProductOrder' ? 'selected' : '' }}>@lang('ProductOrder')</option>
                <option value="QRCode" {{ isset($search['type']) && $search['type'] == 'QRCode' ? 'selected' : '' }}>@lang('QRCode')</option>
				<option
					value="VirtualCardTransaction" {{ isset($search['type']) && $search['type'] == 'VirtualCardTransaction' ? 'selected' : '' }}>@lang('VirtualCardTransaction')</option>
            </select>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-sm btn-block">@lang('Search')</button>
        </div>
    </div>
</div>
