<!-- Sidebar -->
<div class="main-sidebar sidebar-style-2 shadow-sm">
	<aside id="sidebar-wrapper">
		<div class="sidebar-brand">
			<a href="{{ route('home') }}">
				<img src="{{ getFile(config('location.logo.path').'logo.png') }}" class="dashboard-logo"
					 alt="@lang('Logo')">
			</a>
		</div>
		<div class="sidebar-brand sidebar-brand-sm">
			<a href="{{ route('home') }}">
				<img src="{{ getFile(config('location.logo.path').'favicon.png') }}" class="dashboard-logo-sm"
					 alt="@lang('Logo')">
			</a>
		</div>

		<ul class="sidebar-menu">
			<li class="menu-header">@lang('Dashboard')</li>
			<li class="dropdown  {{ activeMenu(['user.dashboard']) }}">
				<a href="{{ route('user.dashboard') }}" class="nav-link"><i
						class="fas fa-home text-primary"></i><span>@lang('Dashboard')</span></a>
			</li>

			<li class="menu-header">@lang('Manage Content')</li>

			@auth
				@if($basic->deposit)
					<li class="dropdown {{ activeMenu(['fund.initialize','deposit.confirm','fund.index','fund.search']) }}">
						<a href="javascript:void(0)" class="nav-link has-dropdown" data-toggle="dropdown">
							<i class="fas fa-money-check-alt text-green"></i> <span>@lang('Add Fund')</span>
						</a>
						<ul class="dropdown-menu">
							<li class="{{ activeMenu(['fund.initialize','deposit.confirm']) }}">
								<a class="nav-link" href="{{ route('fund.initialize') }}">
									@lang('Add Fund')
								</a>
							</li>
							<li class="{{ activeMenu(['fund.index','fund.search']) }}">
								<a class="nav-link" href="{{ route('fund.index') }}">
									@lang('Fund Added List')
								</a>
							</li>
						</ul>
					</li>
				@endif

				@if($basic->transfer)
					<li class="dropdown {{ activeMenu(['transfer.initialize','transfer.confirm','transfer.index','transfer.search']) }}">
						<a href="javascript:void(0)" class="nav-link has-dropdown" data-toggle="dropdown">
							<i class="fas fa-user-friends text-primary"></i> <span>@lang('Send Money')</span>
						</a>
						<ul class="dropdown-menu">
							<li class="{{ activeMenu(['transfer.initialize','transfer.confirm']) }}">
								<a class="nav-link" href="{{ route('transfer.initialize') }}">
									@lang('Send Money')
								</a>
							</li>
							<li class="{{ activeMenu(['transfer.index','transfer.search']) }}">
								<a class="nav-link" href="{{ route('transfer.index') }}">
									@lang('Transfer List')
								</a>
							</li>
						</ul>
					</li>
				@endif

				@if($basic->request)
					<li class="dropdown {{ activeMenu(['requestMoney.initialize','requestMoney.confirm','requestMoney.index','requestMoney.search']) }}">
						<a href="javascript:void(0)" class="nav-link has-dropdown" data-toggle="dropdown">
							<i class="fas fa-search-dollar text-info"></i> <span>@lang('Request Money')</span>
						</a>
						<ul class="dropdown-menu">
							<li class="{{ activeMenu(['requestMoney.initialize','requestMoney.confirm']) }}">
								<a class="nav-link" href="{{ route('requestMoney.initialize') }}">
									@lang('New Request')
								</a>
							</li>
							<li class="{{ activeMenu(['requestMoney.index','requestMoney.search']) }}">
								<a class="nav-link" href="{{ route('requestMoney.index') }}">
									@lang('All Request')
								</a>
							</li>
						</ul>
					</li>
				@endif

				@if($basic->exchange)
					<li class="dropdown {{ activeMenu(['exchange.initialize','exchange.confirm','exchange.index','exchange.search']) }}">
						<a href="javascript:void(0)" class="nav-link has-dropdown" data-toggle="dropdown">
							<i class="fas fa-exchange-alt text-teal"></i> <span>@lang('Exchange')</span>
						</a>
						<ul class="dropdown-menu">
							<li class="{{ activeMenu(['exchange.initialize','exchange.confirm']) }}">
								<a class="nav-link" href="{{ route('exchange.initialize') }}">
									@lang('Exchange')
								</a>
							</li>
							<li class="{{ activeMenu(['exchange.index','exchange.search']) }}">
								<a class="nav-link" href="{{ route('exchange.index') }}">
									@lang('All Exchange')
								</a>
							</li>
						</ul>
					</li>
				@endif

				@if($basic->redeem)
					<li class="dropdown {{ activeMenu(['redeem.initialize','redeem.confirm','redeem.index','redeem.search','redeem.insert']) }}">
						<a href="javascript:void(0)" class="nav-link has-dropdown" data-toggle="dropdown">
							<i class="fas fa-gift text-orange"></i> <span>@lang('Redeem')</span>
						</a>
						<ul class="dropdown-menu">
							<li class="{{ activeMenu(['redeem.initialize','redeem.confirm']) }}">
								<a class="nav-link" href="{{ route('redeem.initialize') }}">
									@lang('Generate New Code')
								</a>
							</li>
							<li class="{{ activeMenu(['redeem.index','redeem.search']) }}">
								<a class="nav-link" href="{{ route('redeem.index') }}">
									@lang('Generated List')
								</a>
							</li>
							<li class="{{ activeMenu(['redeem.insert']) }}">
								<a class="nav-link" href="{{ route('redeem.insert') }}">
									@lang('Insert Redeem Code')
								</a>
							</li>
						</ul>
					</li>
				@endif

				@if($basic->escrow)
					<li class="dropdown {{ activeMenu(['escrow.createRequest','escrow.confirmInit','escrow.index','escrow.search','escrow.paymentView']) }}">
						<a href="javascript:void(0)" class="nav-link has-dropdown" data-toggle="dropdown">
							<i class="fas fa-handshake text-purple"></i> <span>@lang('Escrow')</span>
						</a>
						<ul class="dropdown-menu">
							<li class="{{ activeMenu(['escrow.createRequest']) }}">
								<a class="nav-link" href="{{ route('escrow.createRequest') }}">
									@lang('Create a request')
								</a>
							</li>
							<li class="{{ activeMenu(['escrow.index','escrow.search','escrow.paymentView']) }}">
								<a class="nav-link" href="{{ route('escrow.index') }}">
									@lang('Escrow List')
								</a>
							</li>
						</ul>
					</li>


					<li class="{{ activeMenu(['user.dispute.index','user.dispute.view','user.dispute.search']) }}">
						<a class="nav-link" href="{{ route('user.dispute.index') }}"><i
								class="fa fa-gavel text-danger"></i>
							<span>@lang('Dispute')</span></a>
					</li>
				@endif
				@if($basic->qr_payment)
					<li class="{{ activeMenu(['user.qr.payment']) }}">
						<a class="nav-link" href="{{ route('user.qr.payment') }}"><i
								class="fas fa-qrcode text-success"></i>
							<span>@lang('QR Payment')</span></a>
					</li>
				@endif

				@if($basic->virtual_card)
					<li class="{{ activeMenu(['user.virtual.card']) }}">
						<a class="nav-link" href="{{ route('user.virtual.card') }}"><i
								class="fas fa-credit-card text-danger"></i>
							<span>@lang('Virtual Card')</span></a>
					</li>
				@endif

				@if($basic->voucher)
					<li class="dropdown {{ activeMenu(['voucher.createRequest','voucher.index','voucher.search','voucher.paymentView']) }}">
						<a href="javascript:void(0)" class="nav-link has-dropdown" data-toggle="dropdown">
							<i class="fas fa-file-invoice-dollar text-primary"></i> <span>@lang('Voucher')</span>
						</a>
						<ul class="dropdown-menu">
							<li class="{{ activeMenu(['voucher.createRequest']) }}">
								<a class="nav-link" href="{{ route('voucher.createRequest') }}">
									@lang('Create voucher')
								</a>
							</li>
							<li class="{{ activeMenu(['voucher.index','voucher.search','voucher.paymentView']) }}">
								<a class="nav-link" href="{{ route('voucher.index') }}">
									@lang('Voucher List')
								</a>
							</li>
						</ul>
					</li>
				@endif

				@if($basic->invoice)
					<li class="dropdown {{ activeMenu(['invoice.create','invoice.index']) }}">
						<a href="javascript:void(0)" class="nav-link has-dropdown" data-toggle="dropdown">
							<i class="fas fa-file-invoice text-primary"></i> <span>@lang('Invoice')</span>
						</a>
						<ul class="dropdown-menu">
							<li class="{{ activeMenu(['voucher.createRequest']) }}">
								<a class="nav-link" href="{{ route('invoice.create') }}">
									@lang('Create Invoice')
								</a>
							</li>
							<li class="{{ activeMenu(['invoice.index','invoice.search']) }}">
								<a class="nav-link" href="{{ route('invoice.index') }}">
									@lang('Invoice List')
								</a>
							</li>
						</ul>
					</li>
				@endif
				@if($basic->store)
					<li class="dropdown {{ activeMenu(['store.list','store.create','store.edit','category.list','shipping.list'
                           ,'attr.list','attr.create','attr.edit','product.list','product.create','product.view','product.edit',
                           'product.image.delete','product.delete','stock.list','stock.create','stock.view','order.list','order.view']) }}">
						<a href="javascript:void(0)" class="nav-link has-dropdown" data-toggle="dropdown">
							<i class="fas fa-store text-primary"></i> <span>@lang('Store')</span>
						</a>
						<ul class="dropdown-menu">
							<li class="{{ activeMenu(['store.list','store.create','store.edit']) }}">
								<a class="nav-link" href="{{ route('store.list') }}">
									@lang('Store List')
								</a>
							</li>
							<li class="{{ activeMenu(['category.list']) }}">
								<a class="nav-link" href="{{ route('category.list') }}">
									@lang('Category List')
								</a>
							</li>
							<li class="{{ activeMenu(['attr.list','attr.create','attr.edit']) }}">
								<a class="nav-link" href="{{ route('attr.list') }}">
									@lang('Products Attribute')
								</a>
							</li>
							<li class="{{ activeMenu(['product.list','product.create','product.view','product.edit',
                           'product.image.delete','product.delete']) }}">
								<a class="nav-link" href="{{ route('product.list') }}">
									@lang('Products List')
								</a>
							</li>
							<li class="{{ activeMenu(['stock.list','stock.create','stock.view']) }}">
								<a class="nav-link" href="{{ route('stock.list') }}">
									@lang('Products Stock')
								</a>
							</li>
							<li class="{{ activeMenu(['shipping.list']) }}">
								<a class="nav-link" href="{{ route('shipping.list') }}">
									@lang('Shipping Charge')
								</a>
							</li>
							<li class="{{ activeMenu(['order.list','order.view']) }}">
								<a class="nav-link" href="{{ route('order.list') }}">
									@lang('Orders')
								</a>
							</li>
						</ul>
					</li>
				@endif

				@if($basic->bill_payment)
					<li class="dropdown {{ activeMenu(['pay.bill','pay.bill.list']) }}">
						<a href="javascript:void(0)" class="nav-link has-dropdown" data-toggle="dropdown">
							<i class="far fa-lightbulb text-danger"></i> <span>@lang('Bill')</span>
						</a>
						<ul class="dropdown-menu">
							<li class="{{ activeMenu(['pay.bill']) }}">
								<a class="nav-link" href="{{ route('pay.bill') }}">
									@lang('Pay Bill')
								</a>
							</li>
							<li class="{{ activeMenu(['pay.bill.list']) }}">
								<a class="nav-link" href="{{ route('pay.bill.list') }}">
									@lang('Pay List')
								</a>
							</li>
						</ul>
					</li>
				@endif

				@if($basic->payout)
					<li class="dropdown {{ activeMenu(['payout.index','payout.search','payout.request','payout.confirm']) }}">
						<a href="javascript:void(0)" class="nav-link has-dropdown" data-toggle="dropdown">
							<i class="far fa-money-bill-alt text-indigo"></i> <span>@lang('Payout')</span>
						</a>
						<ul class="dropdown-menu">
							<li class="{{ activeMenu(['payout.request','payout.confirm']) }}">
								<a class="nav-link" href="{{ route('payout.request') }}">
									@lang('Request Payout')
								</a>
							</li>
							<li class="{{ activeMenu(['payout.index','payout.search']) }}">
								<a class="nav-link" href="{{ route('payout.index') }}">
									@lang('Payout List')
								</a>
							</li>
						</ul>
					</li>
				@endif

				<li class="dropdown {{ activeMenu(['user.transaction','user.transaction.search','user.ticket.list','user.ticket.view','user.ticket.create','user.commission.index','user.commission.search','securityPin.reset','securityPin.create','securityPin.manage','securityPin.create','user.kycList',
                           'user.setting','user.api.key','user.api.docx','user.twostep.security','user.twoStepEnable','user.twoStepDisable','list.setting.notify']) }}">
					<a href="javascript:void(0)" class="nav-link has-dropdown" data-toggle="dropdown">
						<i class="fas fa-list-ul text-info"></i> <span>@lang('More')</span>
					</a>
					<ul class="dropdown-menu">
						<li class="{{ activeMenu(['user.transaction','user.transaction.search']) }}">
							<a class="nav-link" href="{{ route('user.transaction') }}">
								@lang('Transactions')
							</a>
						</li>
						<li class="{{ activeMenu(['user.ticket.list','user.ticket.view','user.ticket.create']) }}">
							<a class="nav-link" href="{{ route('user.ticket.list') }}">
								@lang('Tickets')
							</a>
						</li>
						<li class="{{ activeMenu(['user.twostep.security','user.twoStepEnable','user.twoStepDisable']) }}">
							<a class="nav-link" href="{{ route('user.twostep.security') }}">
								@lang('2FA Security')
							</a>
						</li>
						<li class="{{ activeMenu(['securityPin.reset','securityPin.create']) }}">
							<a class="nav-link" href="{{ route('securityPin.reset') }}">
								@lang('Security PIN')
							</a>
						</li>
						<li class="{{ activeMenu(['securityPin.manage']) }}">
							<a class="nav-link" href="{{ route('securityPin.manage') }}">
								@lang('Manage PIN uses')
							</a>
						</li>
						<li class="{{ activeMenu(['user.commission.index','user.commission.search']) }}">
							<a class="nav-link" href="{{ route('user.commission.index') }}">
								@lang('Commission List')
							</a>
						</li>
						<li class="{{ activeMenu(['user.kycList']) }}">
							<a class="nav-link" href="{{ route('user.kycList') }}">
								@lang('Verification Center')
							</a>
						</li>
						<li class="{{ activeMenu(['list.setting.notify']) }}">
							<a class="nav-link" href="{{ route('list.setting.notify') }}">
								@lang('Push Notify Setting')
							</a>
						</li>
						<li class="{{ activeMenu(['user.api.key']) }}">
							<a class="nav-link" href="{{ route('user.api.key') }}">
								@lang('Api Key')
							</a>
						</li>
						<li class="{{ activeMenu(['user.api.docx']) }}">
							<a class="nav-link" href="{{ route('user.api.docx') }}">
								@lang('Api Documentation')
							</a>
						</li>
						<li class="{{ activeMenu(['user.setting']) }}">
							<a class="nav-link" href="{{ route('user.setting') }}">
								@lang('Setting')
							</a>
						</li>
					</ul>
				</li>
			@endauth
		</ul>

		<div class="mt-4 mb-4 p-3 hide-sidebar-mini">
		</div>
	</aside>
</div>
