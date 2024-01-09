<!-- Sidebar -->
<div class="main-sidebar sidebar-style-2 shadow-sm">
	<aside id="sidebar-wrapper">
		<div class="sidebar-brand">
			<a href="{{ route('admin.home') }}">
				<img src="{{ getFile(config('location.logo.path').'logo.png') }}" class="dashboard-logo"
					 alt="@lang('Logo')">
			</a>
		</div>
		<div class="sidebar-brand sidebar-brand-sm">
			<a href="{{ route('admin.home') }}">
				<img src="{{ getFile(config('location.logo.path').'favicon.png') }}" class="dashboard-logo-sm"
					 alt="@lang('Logo')">
			</a>
		</div>

		<ul class="sidebar-menu">
			<li class="menu-header">@lang('Dashboard')</li>
			<li class="dropdown {{ activeMenu(['admin.home']) }}">
				<a href="{{ route('admin.home') }}" class="nav-link"><i
						class="fas fa-tachometer-alt text-primary"></i><span>@lang('Dashboard')</span></a>
			</li>

			@if(checkPermission(13) == true)
				<li class="menu-header">@lang('User Panel')</li>
				<li class="dropdown {{ activeMenu(['user-list','user.search','inactive.user.search','send.mail.user','inactive.user.list']) }}">
					<a href="javascript:void(0)" class="nav-link has-dropdown" data-toggle="dropdown">
						<i class="fas fa-users text-dark"></i> <span>@lang('User Management')</span>
					</a>
					<ul class="dropdown-menu">
						<li class="{{ activeMenu(['user-list','user.search']) }}">
							<a class="nav-link " href="{{ route('user-list') }}">
								@lang('All User')
							</a>
						</li>
						<li class="{{ activeMenu(['inactive.user.list','inactive.user.search']) }}">
							<a class="nav-link" href="{{ route('inactive.user.list') }}">
								@lang('Inactive User')
							</a>
						</li>
						<li class="{{ activeMenu(['send.mail.user']) }}">
							<a class="nav-link" href="{{ route('send.mail.user') }}">
								@lang('Send Mail All User')
							</a>
						</li>
					</ul>
				</li>
			@endif

			@if(checkPermission(14) == true)
				<li class="menu-header">@lang('Manage KYC')</li>
				<li class="dropdown {{ activeMenu(['kyc.create']) }}">
					<a href="{{ route('kyc.create') }}" class="nav-link"><i
							class="fas fa-sticky-note text-info"></i><span>@lang('Manage KYC Form')</span></a>
				</li>
				<li class="dropdown {{ activeMenu(['kyc.list']) }}">
					<a href="javascript:void(0)" class="nav-link has-dropdown" data-toggle="dropdown">
						<i class="fas fa-stream text-dark"></i> <span>@lang('KYC List')</span>
					</a>
					<ul class="dropdown-menu">
						<li class="">
							<a class="nav-link" href="{{ route('kyc.list','pending') }}">
								@lang('Pending')
							</a>
						</li>
						<li class="">
							<a class="nav-link" href="{{ route('kyc.list','approve') }}">
								@lang('Approved')
							</a>
						</li>
						<li class="">
							<a class="nav-link" href="{{ route('kyc.list','rejected') }}">
								@lang('Rejected')
							</a>
						</li>
					</ul>
				</li>
			@endif

			<li class="menu-header">@lang('Support Tickets')</li>
			<li class="dropdown {{ activeMenu(['admin.ticket','admin.ticket.view','admin.ticket.search']) }}">
				<a href="{{ route('admin.ticket') }}" class="nav-link"><i
						class="fas fa-headset text-info"></i><span>@lang('Tickets')</span></a>
			</li>

			<li class="menu-header">@lang('Transactions')</li>
			@if($basic->deposit)
				@if(checkPermission(1) == true)
					<li class="dropdown {{ activeMenu(['admin.fund.add.index','admin.fund.add.search']) }}">
						<a href="{{ route('admin.fund.add.index') }}" class="nav-link"><i
								class="fas fa-money-check-alt text-green"></i><span>@lang('Add Fund List')</span></a>
					</li>
				@endif
			@endif

			@if($basic->transfer)
				@if(checkPermission(2) == true)
					<li class="dropdown {{ activeMenu(['admin.transfer.index','admin.transfer.search']) }}">
						<a href="{{ route('admin.transfer.index') }}" class="nav-link"><i
								class="fas fa-user-friends text-primary"></i><span>@lang('Transfer List')</span></a>
					</li>
				@endif
			@endif

			@if($basic->request)
				@if(checkPermission(15) == true)
					<li class="dropdown {{ activeMenu(['admin.requestMoney.index','admin.requestMoney.search']) }}">
						<a href="{{ route('admin.requestMoney.index') }}" class="nav-link"><i
								class="fas fa-search-dollar text-info"></i><span>@lang('Request Money List')</span></a>
					</li>
				@endif
			@endif
			@if($basic->exchange)
				@if(checkPermission(3) == true)
					<li class="dropdown {{ activeMenu(['admin.exchange.index','admin.exchange.search']) }}">
						<a href="{{ route('admin.exchange.index') }}" class="nav-link"><i
								class="fas fa-exchange-alt text-teal"></i><span>@lang('Exchange List')</span></a>
					</li>
				@endif
			@endif
			@if($basic->redeem)
				@if(checkPermission(4) == true)
					<li class="dropdown {{ activeMenu(['admin.redeem.index','admin.redeem.search']) }}">
						<a href="{{ route('admin.redeem.index') }}" class="nav-link"><i
								class="fas fa-gift text-orange"></i><span>@lang('Redeem Code List')</span></a>
					</li>
				@endif
			@endif

			@if($basic->escrow)
				@if(checkPermission(5) == true)
					<li class="dropdown {{ activeMenu(['admin.escrow.index','admin.escrow.search']) }}">
						<a href="{{ route('admin.escrow.index') }}" class="nav-link"><i
								class="fas fa-handshake text-purple"></i><span>@lang('Escrow List')</span></a>
					</li>
				@endif
				@if(checkPermission(16) == true)
					<li class="dropdown {{ activeMenu(['admin.dispute.index','admin.dispute.search','admin.dispute.view']) }}">
						<a href="{{ route('admin.dispute.index') }}" class="nav-link"><i
								class="fa fa-gavel text-danger"></i><span>@lang('Dispute List')</span></a>
					</li>
				@endif
			@endif
			@if($basic->qr_payment)
				@if(checkPermission(17) == true)
					<li class="dropdown {{ activeMenu(['admin.qr.payment']) }}">
						<a href="{{ route('admin.qr.payment') }}" class="nav-link"><i
								class="fas fa-qrcode text-success"></i><span>@lang('QR Payment List')</span></a>
					</li>
				@endif
			@endif
			@if($basic->voucher)
				@if(checkPermission(6) == true)
					<li class="dropdown {{ activeMenu(['admin.voucher.index','admin.voucher.search']) }}">
						<a href="{{ route('admin.voucher.index') }}" class="nav-link"><i
								class="fas fa-file-invoice-dollar text-primary"></i><span>@lang('Voucher List')</span></a>
					</li>
				@endif
			@endif
			@if($basic->invoice)
				@if(checkPermission(9) == true)
					<li class="dropdown {{ activeMenu(['admin.invoice.index','admin.invoice.search']) }}">
						<a href="{{ route('admin.invoice.index') }}" class="nav-link"><i
								class="fas fa-file-invoice text-purple"></i><span>@lang('Invoice List')</span></a>
					</li>
				@endif
			@endif
			@if($basic->store)
				@if(checkPermission(7) == true)
					<li class="dropdown {{ activeMenu(['admin.store.list','admin.product.list','admin.store.view','admin.product.list',
                       'admin.order.list','admin.product.view','admin.order.view','admin.contact.list']) }}">
						<a href="javascript:void(0)" class="nav-link has-dropdown" data-toggle="dropdown">
							<i class="fas fa-store text-danger"></i> <span>@lang('Store management')</span>
						</a>
						<ul class="dropdown-menu">
							<li class="{{ activeMenu(['admin.store.list','admin.store.view']) }}">
								<a class="nav-link" href="{{ route('admin.store.list') }}">
									@lang('Stores List')
								</a>
							</li>
							<li class="{{ activeMenu(['admin.product.list','admin.product.view']) }}">
								<a class="nav-link" href="{{ route('admin.product.list') }}">
									@lang('Products List')
								</a>
							</li>
							<li class="{{ activeMenu(['admin.order.list','admin.order.view']) }}">
								<a class="nav-link" href="{{ route('admin.order.list') }}">
									@lang('Orders List')
								</a>
							</li>
							<li class="{{ activeMenu(['admin.contact.list']) }}">
								<a class="nav-link" href="{{ route('admin.contact.list') }}">
									@lang('Contact List')
								</a>
							</li>
						</ul>
					</li>
				@endif
			@endif

			@if($basic->store)
				@if(checkPermission(7) == true)
					<li class="dropdown {{ activeMenu(['admin.store.list','admin.product.list','admin.store.view','admin.product.list',
                       'admin.order.list','admin.product.view','admin.order.view','admin.contact.list']) }}">
						<a href="javascript:void(0)" class="nav-link has-dropdown" data-toggle="dropdown">
							<i class="fas fa-money-bill-alt text-success"></i> <span>@lang('Installments')</span>
						</a>
						<ul class="dropdown-menu">
						<li class="">
								<a class="nav-link" href="{{ route('admin.installment.create')}}">
									@lang('New Plan')
								</a>
							</li>
							<li class="">
								<a class="nav-link" href="{{ route('admin.installment.list')}}">
									@lang('Plans')
								</a>
							</li>
							<li class="">
								<a class="nav-link" href="{{ route('admin.installment.purchases')}}">
									@lang('Purchases')
								</a>
							</li>
							<li class="">
								<a class="nav-link" href="{{ route('admin.installment.overdue')}}">
									@lang('Overdue')
								</a>
							</li>
							<li class="">
								<a class="nav-link" href="{{ route('admin.installment.overdue_history')}}">
									@lang('Overdue History')
								</a>
							</li>
							<li class="">
								<a class="nav-link" href="{{ route('admin.installment.verification_requests')}}">
									@lang('Verification Requests')
								</a>
							</li>
							<li class="">
								<a class="nav-link" href="{{ route('admin.installment.verified_users')}}">
									@lang('Verified Users')
								</a>
							</li>
							<li class="">
								<a class="nav-link" href="{{ route('admin.installment.settings')}}">>
									@lang('Settings')
								</a>
							</li>
							
						</ul>
					</li>
				@endif
			@endif

			@if($basic->payout)
				@if(checkPermission(8) == true)
					<li class="dropdown {{ activeMenu(['admin.payout.index','admin.payout.search','payout.details']) }}">
						<a href="{{ route('admin.payout.index') }}" class="nav-link"><i
								class="far fa-money-bill-alt text-primary"></i><span>@lang('Payout List')</span></a>
					</li>
				@endif
			@endif

			@if(checkPermission(18) == true)
				<li class="dropdown {{ activeMenu(['admin.transaction.index','admin.transaction.search']) }}">
					<a href="{{ route('admin.transaction.index') }}" class="nav-link"><i
							class="fas fa-chart-line text-success"></i><span>@lang('Transaction List')</span></a>
				</li>
			@endif
			<li class="dropdown {{ activeMenu(['admin.commission.index','admin.commission.search']) }}">
				<a href="{{ route('admin.commission.index') }}" class="nav-link"><i class="fa fa-percent text-info"></i><span>@lang('Commission List')</span></a>
			</li>


			<li class="menu-header">@lang('Settings Panel')</li>

			<li class="dropdown {{ activeMenu(['settings','seo.update','plugin.config','tawk.control','google.analytics.control','google.recaptcha.control','fb.messenger.control','service.control','logo.update','breadcrumb.update','seo.update','currency.exchange.api.config','sms.config', 'sms.template.index','sms.template.edit','voucher.settings','basic.control','securityQuestion.index','securityQuestion.create','securityQuestion.edit','pusher.config','notify.template.index','notify.template.edit','language.index','language.create', 'language.edit','language.keyword.edit', 'email.config','email.template.index','email.template.default', 'email.template.edit', 'charge.index', 'charge.edit', 'currency.index', 'currency.create', 'currency.edit', 'charge.chargeEdit' ]) }}">
				<a href="{{ route('settings') }}" class="nav-link"><i
						class="fas fa-cog text-primary"></i><span>@lang('Control Panel')</span></a>
			</li>

			<li class="dropdown {{ activeMenu(['payment.methods','edit.payment.methods']) }}">
				<a href="{{ route('payment.methods') }}" class="nav-link"><i
						class="fas fa-credit-card text-success"></i><span>@lang('Payment Methods')</span></a>
			</li>

			@if($basic->virtual_card)
				@if(checkPermission(10) == true)
					<li class="dropdown {{ activeMenu(['admin.virtual.card','admin.virtual.cardOrder','admin.virtual.cardOrderDetail',
                       'admin.virtual.cardList','admin.virtual.cardTransaction']) }}">
						<a href="javascript:void(0)" class="nav-link has-dropdown" data-toggle="dropdown">
							<i class="fas fa-address-card text-primary"></i> <span>@lang('Virtual Card')</span>
						</a>
						<ul class="dropdown-menu">
							<li class="{{ activeMenu(['admin.virtual.card']) }}">
								<a class="nav-link" href="{{ route('admin.virtual.card') }}">
									@lang('Available Methods')
								</a>
							</li>
							<li class="{{ activeMenu(['admin.virtual.cardOrder','admin.virtual.cardOrderDetail']) }}">
								<a class="nav-link" href="{{ route('admin.virtual.cardOrder') }}">
									@lang('Request List')
								</a>
							</li>
							<li class="{{(collect(request()->segments())->last() == 'all' ? 'active':'')}}">
								<a class="nav-link" href="{{ route('admin.virtual.cardList','all') }}">
									@lang('Card Lists')
								</a>
							</li>
							<li class="{{(collect(request()->segments())->last() == 'add-fund' ? 'active':'')}}">
								<a class="nav-link" href="{{ route('admin.virtual.cardList','add-fund') }}">
									@lang('Add Fund Requests')
								</a>
							</li>
							<li class="{{(collect(request()->segments())->last() == 'block' ? 'active':'')}}">
								<a class="nav-link" href="{{ route('admin.virtual.cardList','block') }}">
									@lang('Block Requests')
								</a>
							</li>
						</ul>
					</li>
				@endif
			@endif
			<li class="dropdown {{ activeMenu(['payout.method.list','payout.method.add','payout.method.edit']) }}">
				<a href="javascript:void(0)" class="nav-link has-dropdown" data-toggle="dropdown">
					<i class="fas fa-users-cog text-danger"></i> <span>@lang('Payout Settings')</span>
				</a>
				<ul class="dropdown-menu">
					<li class="{{ activeMenu(['payout.method.list','payout.method.edit']) }}">
						<a class="nav-link" href="{{ route('payout.method.list') }}">
							@lang('Available Methods')
						</a>
					</li>
					<li class="{{ activeMenu(['payout.method.add']) }}">
						<a class="nav-link" href="{{ route('payout.method.add') }}">
							@lang('Add Method')
						</a>
					</li>
				</ul>
			</li>
			@if($basic->bill_payment)
				@if(checkPermission(11) == true)
					<li class="dropdown {{ activeMenu(['bill.method.list','bill.service.list','bill.pay.list','bill.pay.view']) }}">
						<a href="javascript:void(0)" class="nav-link has-dropdown" data-toggle="dropdown">
							<i class="fas fa-money-bill text-dark"></i> <span>@lang('Bill Settings')</span>
						</a>
						<ul class="dropdown-menu">
							<li class="{{ activeMenu(['bill.method.list']) }}">
								<a class="nav-link" href="{{ route('bill.method.list') }}">
									@lang('Available Methods')
								</a>
							</li>
							<li class="{{ activeMenu(['bill.service.list']) }}">
								<a class="nav-link" href="{{ route('bill.service.list') }}">
									@lang('Service List')
								</a>
							</li>
							<li class="{{ activeMenu(['bill.pay.list','bill.pay.view']) }}">
								<a class="nav-link" href="{{route('bill.pay.list')}}">
									@lang('Bill List')
								</a>
							</li>
						</ul>
					</li>
				@endif
			@endif
			@if(checkPermission(12) == true)
				<li class="dropdown {{ activeMenu(['admin.role','admin.role.staff']) }}">
					<a href="javascript:void(0)" class="nav-link has-dropdown" data-toggle="dropdown">
						<i class="fas fa-user-friends text-success"></i> <span>@lang('Roles and Permission')</span>
					</a>
					<ul class="dropdown-menu">
						<li class="{{ activeMenu(['admin.role']) }}">
							<a class="nav-link" href="{{ route('admin.role') }}">
								@lang('Available Roles')
							</a>
						</li>
						<li class="{{ activeMenu(['admin.role.staff']) }}">
							<a class="nav-link" href="{{ route('admin.role.staff') }}">
								@lang('Manage Staffs')
							</a>
						</li>
					</ul>
				</li>
			@endif

			@if(checkPermission(19) == true)
				<li class="dropdown {{ activeMenu(['admin.referral.bonus.index']) }}">
					<a href="{{ route('admin.referral.bonus.index') }}" class="nav-link"><i
							class="fas fa-retweet text-primary"></i><span>@lang('Referral Settings')</span></a>
				</li>
			@endif

			@if(checkPermission(20) == true)
				<li class="dropdown {{ activeMenu(['template.show']) }}">
					<a href="javascript:void(0)" class="nav-link has-dropdown" data-toggle="dropdown">
						<i class="fa fa-file-code text-info"></i> <span>@lang('UI Settings')</span>
					</a>
					<ul class="dropdown-menu">
						@foreach(array_diff(array_keys(config('templates')),['message','template_media']) as $name)
							<li class="{{ activeMenu(['template.show'],$name) }}">
								<a class="nav-link" href="{{ route('template.show',$name) }}">
									@lang(ucfirst(kebab2Title($name)))
								</a>
							</li>
						@endforeach
					</ul>
				</li>
			@endif
			@if(checkPermission(20) == true)
				<li class="dropdown {{ activeMenu(['content.index','content.create','content.show']) }}">
					<a href="javascript:void(0)" class="nav-link has-dropdown" data-toggle="dropdown">
						<i class="fas fa-cogs text-dark"></i> <span>@lang('Content Settings')</span>
					</a>
					<ul class="dropdown-menu">
						@foreach(array_diff(array_keys(config('contents')),['message','content_media']) as $name)
							<li class="{{ activeMenu(['content.index','content.create','content.show'],$name) }}">
								<a class="nav-link" href="{{ route('content.index',$name) }}">
									@lang(ucfirst(kebab2Title($name)))
								</a>
							</li>
						@endforeach
					</ul>
				</li>
			@endif
		</ul>

		<div class="mt-4 mb-4 p-3 hide-sidebar-mini">
		</div>
	</aside>
</div>
