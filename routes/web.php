<?php

use App\Http\Controllers\Admin\InstallmentsController;
use App\Http\Controllers\Admin\AdminDepositController;
use App\Http\Controllers\Admin\AdminDisputeController;
use App\Http\Controllers\Admin\AdminEscrowController;
use App\Http\Controllers\Admin\AdminExchangeController;
use App\Http\Controllers\Admin\AdminFundController;
use App\Http\Controllers\Admin\AdminPayoutController;
use App\Http\Controllers\Admin\AdminRedeemCodeController;
use App\Http\Controllers\Admin\AdminRequestMoneyController;
use App\Http\Controllers\Admin\AdminTransactionController;
use App\Http\Controllers\Admin\AdminTransferController;
use App\Http\Controllers\Admin\PushNotifyController;
use App\Http\Controllers\Admin\AdminVoucherController;
use App\Http\Controllers\Admin\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\AdminTicketController;
use App\Http\Controllers\Admin\VirtualCardController;
use App\Http\Controllers\Admin\RolesPermissionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminProfileController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\BillPaymentController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\BasicControlController;
use App\Http\Controllers\ChargesLimitController;
use App\Http\Controllers\StoreShopController;
use App\Http\Controllers\CommissionEntryController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\DisputeController;
use App\Http\Controllers\FaSecurityController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\EscrowController;
use App\Http\Controllers\ExchangeController;
use App\Http\Controllers\FundController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\NotifyController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\PayoutMethodController;
use App\Http\Controllers\PayBillController;
use App\Http\Controllers\RedeemCodeController;
use App\Http\Controllers\ReferralBonusController;
use App\Http\Controllers\RequestMoneyController;
use App\Http\Controllers\SecurityQuestionController;
use App\Http\Controllers\SiteNotificationController;
use App\Http\Controllers\SmsControlController;
use App\Http\Controllers\SmsTemplateController;
use App\Http\Controllers\SubscribeController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\TwoFactorSettingController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\StoreCategoryController;
use App\Http\Controllers\StoreShippingController;
use App\Http\Controllers\StoreProductAttrController;
use App\Http\Controllers\StoreProductController;
use App\Http\Controllers\StoreProductStockController;
use App\Http\Controllers\UserSettingController;
use App\Http\Controllers\PublicCartController;
use App\Http\Controllers\PublicChekoutController;
use App\Http\Controllers\khaltiPaymentController;
use App\Http\Controllers\ProductOrderController;
use App\Http\Controllers\QrCodePaymentController;
use App\Http\Controllers\VirtualCardController as UserVirtualCardController;
use App\Http\Controllers\AdminInvoiceController;
use App\Http\Controllers\Admin\AdminKycController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('queue-work', function () {
	return Illuminate\Support\Facades\Artisan::call('queue:work', ['--stop-when-empty' => true]);
})->name('queue.work');


Route::get('schedule-run', function () {
	return Illuminate\Support\Facades\Artisan::call('schedule:run');
})->name('schedule:run');

Route::get('removeStatus', function () {
	session()->forget('status');
})->name('removeStatus');

Route::get('{link}/order/success/{orderNumber}', [PaymentController::class, 'orderSuccess'])->name('order.success');
Route::match(['get', 'post'], 'success', [PaymentController::class, 'success'])->name('success');
Route::match(['get', 'post'], 'failed', [PaymentController::class, 'failed'])->name('failed');
Route::match(['get', 'post'], 'payment/{code}/{trx?}/{type?}', [PaymentController::class, 'gatewayIpn'])->name('ipn');
Route::post('/khalti/payment/verify/{trx}', [khaltiPaymentController::class, 'verifyPayment'])->name('khalti.verifyPayment');
Route::post('/khalti/payment/store', [khaltiPaymentController::class, 'storePayment'])->name('khalti.storePayment');


Route::group(['prefix' => 'admin'], function () {
	/* Authentication Routes */
	Route::get('/', [LoginController::class, 'showLoginForm'])->name('admin.login');
	Route::post('login', [LoginController::class, 'login'])->name('admin.auth.login');

	/* Password Reset Routes */
	Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('admin.password.request');
	Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('admin.password.email');
	Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('admin.password.reset')->middleware('guest');
	Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('admin.password.reset.update');
});


Route::group(['prefix' => 'admin', 'middleware' => ['auth:admin', 'demo']], function () {

//Edit
	Route::get('installment/list', function(){
		return view('admin.financial.installments.lists.list');
	})->name('admin.installment.list');
	
	Route::get('installment/create', function(){
		return view('admin.financial.installments.create.create');
	})->name('admin.installment.create');
	
	Route::get('installment/purchases', function(){
		return view('admin.financial.installments.purchases.list');
	})->name('admin.installment.purchases');
	
	Route::get('installment/overdue', function(){
		return view('admin.financial.installments.overdue_installments');
	})->name('admin.installment.overdue');
	
	Route::get('installment/overdue_history', function(){
		return view('admin.financial.installments.overdue_history');
	})->name('admin.installment.overdue_history');
	
	Route::get('installment/verification_requests', function(){
		return view('admin.financial.installments.verification_requests');
	})->name('admin.installment.verification_requests');

	Route::get('installment/verified_users', function(){
		return view('admin.financial.installments.verified_users');
	})->name('admin.installment.verified_users');
	
	Route::get('installment/settings', function(){
		return view('admin.financial.installments.settings.index');
	})->name('admin.installment.settings');
	
	Route::get('/save-token', [AdminController::class, 'saveToken'])->name('admin.save.token');
	/* USER LIST */
	Route::middleware('module:13')->group(function () {
		Route::get('user-list', [UserController::class, 'index'])->name('user-list');
		Route::get('inactive-user-list', [UserController::class, 'inactiveUserList'])->name('inactive.user.list');

		Route::get('user-search', [UserController::class, 'search'])->name('user.search');
		Route::get('inactive-user-search', [UserController::class, 'inactiveUserSearch'])->name('inactive.user.search');

		Route::match(['get', 'post'], 'user-edit/{user}', [UserController::class, 'edit'])->name('user.edit');
		Route::match(['get', 'post'], 'user-asLogin/{user}', [UserController::class, 'asLogin'])->name('user.asLogin');
		Route::match(['get', 'post'], 'send-mail-user/{user?}', [UserController::class, 'sendMailUser'])->name('send.mail.user');
	});

	/* PROFILE SHOW UPDATE BY USER */
	Route::match(['get', 'post'], 'profile', [AdminProfileController::class, 'index'])->name('admin.profile');
	Route::match(['get', 'post'], 'change-password', [AdminController::class, 'changePassword'])->name('admin.change.password');

	/* KYC MANAGE BY ADMIN*/
	Route::middleware('module:14')->group(function () {
		Route::get('manage-kyc', [AdminKycController::class, 'create'])->name('kyc.create');
		Route::post('manage-kyc/update', [AdminKycController::class, 'update'])->name('kyc.update');
		Route::get('user/kyc/{status?}', [AdminKycController::class, 'list'])->name('kyc.list');
		Route::get('user/kyc/view/{id}', [AdminKycController::class, 'view'])->name('kyc.view');
		Route::post('user/kyc/action/{id}', [AdminKycController::class, 'action'])->name('kyc.action');
		Route::get('user/kyc/search', [AdminKycController::class, 'search'])->name('kyc.search');
	});

	/* PAYMENT METHOD MANAGE BY ADMIN*/
	Route::get('payment-methods', [PaymentMethodController::class, 'index'])->name('payment.methods');
	Route::get('edit-payment-methods/{id}', [PaymentMethodController::class, 'edit'])->name('edit.payment.methods');
	Route::put('update-payment-methods/{id}', [PaymentMethodController::class, 'update'])->name('update.payment.methods');
	Route::post('sort-payment-methods', [PaymentMethodController::class, 'sortPaymentMethods'])->name('sort.payment.methods');

	Route::get('push-notification-show', [SiteNotificationController::class, 'showByAdmin'])->name('admin.push.notification.show');
	Route::get('push.notification.readAll', [SiteNotificationController::class, 'readAllByAdmin'])->name('admin.push.notification.readAll');
	Route::get('push-notification-readAt/{id}', [SiteNotificationController::class, 'readAt'])->name('admin.push.notification.readAt');

	/* PAYOUT METHOD MANAGE BY ADMIN */
	Route::middleware('module:8')->group(function () {
		Route::get('payout-method-list', [PayoutMethodController::class, 'index'])->name('payout.method.list');
		Route::match(['get', 'put'], 'payout-method/{payoutMethod}', [PayoutMethodController::class, 'edit'])->name('payout.method.edit');
		Route::match(['get', 'post'], 'payout-method-add', [PayoutMethodController::class, 'addMethod'])->name('payout.method.add');
	});

	/* BILL PAYMENT METHOD MANAGE BY ADMIN */
	Route::middleware('module:11')->group(function () {
		Route::get('bill-method-list', [BillPaymentController::class, 'index'])->name('bill.method.list');
		Route::match(['get', 'put'], 'bill-method-edit/{id}', [BillPaymentController::class, 'edit'])->name('bill.method.edit');
		Route::put('bill-service-convert/{id}', [BillPaymentController::class, 'serviceRate'])->name('bill.service.rate');
		Route::get('api/bill-service', [BillPaymentController::class, 'fetchServices'])->name('bill.fetch.service');
		Route::post('api/add-service', [BillPaymentController::class, 'addServices'])->name('bill.add.service');
		Route::post('api/add-service/bulk', [BillPaymentController::class, 'addServicesBulk'])->name('bill.add.bulk.service');

		Route::get('bill-service-list', [BillPaymentController::class, 'serviceList'])->name('bill.service.list');
		Route::post('bill-charge-limit/add', [BillPaymentController::class, 'chargeLimitAdd'])->name('bill.chargeLimit.add');
		Route::post('bill-charge-limit/{id}', [BillPaymentController::class, 'chargeLimitEdit'])->name('bill.chargeLimit.edit');
		Route::post('bill-service-change/{id}', [BillPaymentController::class, 'statusChange'])->name('bill.status.change');

		Route::get('bill-pay-list', [BillPaymentController::class, 'billPayList'])->name('bill.pay.list');
		Route::get('bill-pay-list/view/{utr}', [BillPaymentController::class, 'billPayView'])->name('bill.pay.view');
	});

	/* ROLES AND PERMISSION BY ADMIN */
	Route::middleware('module:12')->group(function () {
		Route::get('role/list', [RolesPermissionController::class, 'index'])->name('admin.role');
		Route::post('role/create', [RolesPermissionController::class, 'roleCreate'])->name('admin.role.create');
		Route::post('role/update', [RolesPermissionController::class, 'roleUpdate'])->name('admin.role.update');
		Route::delete('role/delete/{id}', [RolesPermissionController::class, 'roleDelete'])->name('admin.role.delete');

		Route::get('manage/staffs', [RolesPermissionController::class, 'staffList'])->name('admin.role.staff');
		Route::post('manage/staffs/create', [RolesPermissionController::class, 'staffCreate'])->name('admin.role.usersCreate');
		Route::post('manage/staffs/status/change/{id}', [RolesPermissionController::class, 'statusChange'])->name('admin.role.statusChange');
		Route::post('manage/staffs/login/{id}', [RolesPermissionController::class, 'userLogin'])->name('admin.role.usersLogin');
	});

	/* ===== DEPOSIT VIEW MANAGE BY ADMIN ===== */
	Route::match(['get', 'post'], 'add-balance-user/{userId}', [AdminDepositController::class, 'addBalanceUser'])->name('admin.user.add.balance');

	/* ===== FUND ADD VIEW MANAGE BY ADMIN ===== */
	Route::middleware('module:1')->group(function () {
		Route::get('fund-add-list', [AdminFundController::class, 'index'])->name('admin.fund.add.index');
		Route::get('fund-add-search', [AdminFundController::class, 'search'])->name('admin.fund.add.search');
		Route::get('fund-add-list/{userId}', [AdminFundController::class, 'showByUser'])->name('admin.user.fund.add.show');
		Route::get('fund-add-search/{userId}', [AdminFundController::class, 'searchByUser'])->name('admin.user.fund.add.search');
	});

	/* ===== TRANSFER VIEW MANAGE BY ADMIN ===== */
	Route::middleware('module:2')->group(function () {
		Route::get('transfer-list', [AdminTransferController::class, 'index'])->name('admin.transfer.index');
		Route::get('transfer-search', [AdminTransferController::class, 'search'])->name('admin.transfer.search');
		Route::get('transfer-list/{userId}', [AdminTransferController::class, 'showByUser'])->name('admin.user.transfer.show');
		Route::get('transfer-search/{userId}', [AdminTransferController::class, 'searchByUser'])->name('admin.user.transfer.search');
	});

	/* ===== REQUEST MONEY VIEW MANAGE BY ADMIN ===== */
	Route::middleware('module:15')->group(function () {
		Route::get('request-money-list', [AdminRequestMoneyController::class, 'index'])->name('admin.requestMoney.index');
		Route::get('request-money-search', [AdminRequestMoneyController::class, 'search'])->name('admin.requestMoney.search');
		Route::get('request-money-list/{userId}', [AdminRequestMoneyController::class, 'showByUser'])->name('admin.user.requestMoney.show');
		Route::get('request-money-search/{userId}', [AdminRequestMoneyController::class, 'searchByUser'])->name('admin.user.requestMoney.search');
	});

	/* ===== EXCHANGE VIEW MANAGE BY ADMIN ===== */
	Route::middleware('module:3')->group(function () {
		Route::get('exchange-list', [AdminExchangeController::class, 'index'])->name('admin.exchange.index');
		Route::get('exchange-search', [AdminExchangeController::class, 'search'])->name('admin.exchange.search');
		Route::get('exchange-list/{userId}', [AdminExchangeController::class, 'showByUser'])->name('admin.user.exchange.show');
		Route::get('exchange-search/{userId}', [AdminExchangeController::class, 'searchByUser'])->name('admin.user.exchange.search');
	});

	/* ===== REDEEM CODE VIEW MANAGE BY ADMIN ===== */
	Route::middleware('module:4')->group(function () {
		Route::get('redeem-list', [AdminRedeemCodeController::class, 'index'])->name('admin.redeem.index');
		Route::get('redeem-search', [AdminRedeemCodeController::class, 'search'])->name('admin.redeem.search');
		Route::get('redeem-list/{userId}', [AdminRedeemCodeController::class, 'showByUser'])->name('admin.user.redeem.show');
		Route::get('redeem-search/{userId}', [AdminRedeemCodeController::class, 'searchByUser'])->name('admin.user.redeem.search');
	});

	/* ===== ESCROW VIEW MANAGE BY ADMIN ===== */
	Route::middleware('module:5')->group(function () {
		Route::get('escrow-list', [AdminEscrowController::class, 'index'])->name('admin.escrow.index');
		Route::get('escrow-search', [AdminEscrowController::class, 'search'])->name('admin.escrow.search');
		Route::get('escrow-list/{userId}', [AdminEscrowController::class, 'showByUser'])->name('admin.user.escrow.show');
		Route::get('escrow-search/{userId}', [AdminEscrowController::class, 'searchByUser'])->name('admin.user.escrow.search');
	});

	/* ===== VOUCHER VIEW MANAGE BY ADMIN ===== */
	Route::middleware('module:6')->group(function () {
		Route::get('voucher-list', [AdminVoucherController::class, 'index'])->name('admin.voucher.index');
		Route::get('voucher-search', [AdminVoucherController::class, 'search'])->name('admin.voucher.search');
		Route::get('voucher-list/{userId}', [AdminVoucherController::class, 'showByUser'])->name('admin.user.voucher.show');
		Route::get('voucher-search/{userId}', [AdminVoucherController::class, 'searchByUser'])->name('admin.user.voucher.search');
	});

	/* ===== INVOICE VIEW MANAGE BY ADMIN ===== */
	Route::middleware('module:9')->group(function () {
		Route::get('invoice-list', [AdminInvoiceController::class, 'index'])->name('admin.invoice.index');
		Route::get('invoice-search', [AdminInvoiceController::class, 'search'])->name('admin.invoice.search');
		Route::get('invoice-list/{userId}', [AdminInvoiceController::class, 'showByUser'])->name('admin.user.invoice.show');
		Route::get('invoice-search/{userId}', [AdminInvoiceController::class, 'searchByUser'])->name('admin.user.invoice.search');
	});

	/* ===== PAYOUT VIEW MANAGE BY ADMIN ===== */
	Route::middleware('module:8')->group(function () {
		Route::get('payout-list', [AdminPayoutController::class, 'index'])->name('admin.payout.index');
		Route::get('payout-search', [AdminPayoutController::class, 'search'])->name('admin.payout.search');
		Route::get('payout-list/{userId}', [AdminPayoutController::class, 'showByUser'])->name('admin.user.payout.show');
		Route::get('payout-search/{userId}', [AdminPayoutController::class, 'searchByUser'])->name('admin.user.payout.search');
		Route::get('details-payout/{utr}', [AdminPayoutController::class, 'show'])->name('payout.details');
		Route::post('confirm-payout/{utr}', [AdminPayoutController::class, 'confirmPayout'])->name('admin.user.payout.confirm');
		Route::post('cancel-payout/{utr}', [AdminPayoutController::class, 'cancelPayout'])->name('admin.user.payout.cancel');
	});

	/* ===== BASIC CONTROL MANAGE BY ADMIN ===== */
	Route::get('settings/{settings?}', [BasicControlController::class, 'index'])->name('settings');

	Route::match(['get', 'post'], 'basic-control', [BasicControlController::class, 'basic_control'])->name('basic.control')->middleware('module:21');
	Route::match(['get', 'post'], 'service-control', [BasicControlController::class, 'serviceControl'])->name('service.control');
	Route::match(['get', 'post'], 'voucher-settings', [BasicControlController::class, 'voucherSettings'])->name('voucher.settings')->middleware('module:6');
	Route::match(['get', 'post'], 'invoice-settings', [BasicControlController::class, 'invoiceSettings'])->name('invoice.settings')->middleware('module:9');
	Route::match(['get', 'post'], 'virtual-card.settings', [BasicControlController::class, 'virtualCardSettings'])->name('virtual-card.settings')->middleware('module:10');
	Route::match(['get', 'post'], 'pusher-config', [BasicControlController::class, 'pusherConfig'])->name('pusher.config');
	Route::match(['get', 'post'], 'firebase-config', [BasicControlController::class, 'firebaseConfig'])->name('firebase.config');
	Route::match(['get', 'post'], 'email-config', [BasicControlController::class, 'emailConfig'])->name('email.config');
	Route::match(['get', 'post'], 'sms-config', [SmsControlController::class, 'smsConfig'])->name('sms.config');

	Route::get('plugin-config', [BasicControlController::class, 'pluginConfig'])->name('plugin.config');
	Route::match(['get', 'post'], 'tawk-config', [BasicControlController::class, 'tawkConfig'])->name('tawk.control');
	Route::match(['get', 'post'], 'fb-messenger-config', [BasicControlController::class, 'fbMessengerConfig'])->name('fb.messenger.control');
	Route::match(['get', 'post'], 'google-recaptcha', [BasicControlController::class, 'googleRecaptchaConfig'])->name('google.recaptcha.control');
	Route::match(['get', 'post'], 'google-analytics', [BasicControlController::class, 'googleAnalyticsConfig'])->name('google.analytics.control');

	Route::match(['get', 'post'], 'currency-exchange-api-config', [BasicControlController::class, 'currencyExchangeApiConfig'])->name('currency.exchange.api.config')->middleware('module:22');
	Route::match(['get', 'post'], 'api-sandbox', [BasicControlController::class, 'sandBoxApi'])->name('api.sandbox.index');

	/* ===== ADMIN EMAIL-CONFIGURATION SETTINGS ===== */
	Route::get('email-templates', [EmailTemplateController::class, 'index'])->name('email.template.index');
	Route::match(['get', 'post'], 'default-template', [EmailTemplateController::class, 'defaultTemplate'])->name('email.template.default');
	Route::get('email-template/edit/{id}', [EmailTemplateController::class, 'edit'])->name('email.template.edit');
	Route::post('email-template/update/{id}', [EmailTemplateController::class, 'update'])->name('email.template.update');
	Route::post('testEmail', [EmailTemplateController::class, 'testEmail'])->name('testEmail');

	/* ===== ADMIN SMS-CONFIGURATION SETTINGS ===== */
	Route::get('sms-template', [SmsTemplateController::class, 'index'])->name('sms.template.index');
	Route::get('sms-template/edit/{id}', [SmsTemplateController::class, 'edit'])->name('sms.template.edit');
	Route::post('sms-template/update/{id}', [SmsTemplateController::class, 'update'])->name('sms.template.update');

	/* ===== ADMIN NOTIFICATION-CONFIGURATION SETTINGS ===== */
	Route::get('notify-template', [NotifyController::class, 'index'])->name('notify.template.index');
	Route::get('notify-template/edit/{id}', [NotifyController::class, 'edit'])->name('notify.template.edit');
	Route::post('notify-template/update/{id}', [NotifyController::class, 'update'])->name('notify.template.update');

	/* ===== ADMIN FIREBASE NOTIFICATION-CONFIGURATION SETTINGS ===== */
	Route::get('push/notify-template', [PushNotifyController::class, 'show'])->name('push.notify.template.index');
	Route::get('push/notify-template/edit/{id}', [PushNotifyController::class, 'edit'])->name('push.notify.template.edit');
	Route::post('push/notify-template/update/{id}', [PushNotifyController::class, 'update'])->name('push.notify.template.update');


	/* ===== ADMIN CURRENCY SETTINGS ===== */
	Route::middleware('module:23')->group(function () {
		Route::get('currencies', [CurrencyController::class, 'index'])->name('currency.index');
		Route::get('currency/create', [CurrencyController::class, 'create'])->name('currency.create');
		Route::post('currency/create', [CurrencyController::class, 'store'])->name('currency.store');
		Route::get('currency/{currency}', [CurrencyController::class, 'edit'])->name('currency.edit');
		Route::put('currency/{currency}', [CurrencyController::class, 'update'])->name('currency.update');
	});

	/* ===== ADMIN LANGUAGE SETTINGS ===== */
	Route::get('languages', [LanguageController::class, 'index'])->name('language.index');
	Route::get('language/create', [LanguageController::class, 'create'])->name('language.create');
	Route::post('language/create', [LanguageController::class, 'store'])->name('language.store');
	Route::get('language/{language}', [LanguageController::class, 'edit'])->name('language.edit');
	Route::put('language/{language}', [LanguageController::class, 'update'])->name('language.update');
	Route::delete('language-delete/{language}', [LanguageController::class, 'destroy'])->name('language.delete');

	Route::get('language-keyword/{language}', [LanguageController::class, 'keywordEdit'])->name('language.keyword.edit');
	Route::put('language-keyword/{language}', [LanguageController::class, 'keywordUpdate'])->name('language.keyword.update');
	Route::post('language-import-json', [LanguageController::class, 'importJson'])->name('language.import.json');
	Route::post('store-key/{language}', [LanguageController::class, 'storeKey'])->name('language.store.key');
	Route::put('update-key/{language}', [LanguageController::class, 'updateKey'])->name('language.update.key');
	Route::delete('delete-key/{language}', [LanguageController::class, 'deleteKey'])->name('language.delete.key');

	/* ===== ADMIN CHARGES & LIMITS SETTINGS ===== */
	Route::middleware('module:24')->group(function () {
		Route::get('charges', [ChargesLimitController::class, 'index'])->name('charge.index');
		Route::get('charge/{chargesLimit}', [ChargesLimitController::class, 'edit'])->name('charge.edit');
		Route::get('charge/{transactionTypeID}/{currencyID}', [ChargesLimitController::class, 'chargeEdit'])->name('charge.chargeEdit');
		Route::put('charge/{chargesLimit}', [ChargesLimitController::class, 'update'])->name('charge.update');

		Route::get('charge-payment-method/{currencyID}', [ChargesLimitController::class, 'chargePaymentMethod'])->name('charge.payment.method');
		Route::get('get-deposit-charge/{currency_id}/{payment_method_id}', [ChargesLimitController::class, 'getCharge'])->name('get.deposit.charge');
		Route::post('set-deposit-charge', [ChargesLimitController::class, 'setCharge'])->name('set.deposit.charge');
	});

	/* ===== ADMIN SECURITY QUESTION SETTINGS ===== */
	Route::get('security-questions', [SecurityQuestionController::class, 'index'])->name('securityQuestion.index');
	Route::get('security-question/create', [SecurityQuestionController::class, 'create'])->name('securityQuestion.create');
	Route::post('security-question/create', [SecurityQuestionController::class, 'store'])->name('securityQuestion.store');
	Route::get('security-question/{securityQuestion}', [SecurityQuestionController::class, 'edit'])->name('securityQuestion.edit');
	Route::put('security-question/{securityQuestion}', [SecurityQuestionController::class, 'update'])->name('securityQuestion.update');

	/* ===== ADMIN SUPPORT TICKET ===== */
	Route::get('tickets', [AdminTicketController::class, 'tickets'])->name('admin.ticket');
	Route::get('tickets-search', [AdminTicketController::class, 'ticketSearch'])->name('admin.ticket.search');
	Route::get('tickets-view/{id}', [AdminTicketController::class, 'ticketReply'])->name('admin.ticket.view');
	Route::put('ticket-reply/{id}', [AdminTicketController::class, 'ticketReplySend'])->name('admin.ticket.reply');
	Route::get('ticket-download/{ticket}', [AdminTicketController::class, 'ticketDownload'])->name('admin.ticket.download');
	Route::post('ticket-delete', [AdminTicketController::class, 'ticketDelete'])->name('admin.ticket.delete');

	/* ===== ADMIN DISPUTE DETAILS ===== */
	Route::middleware('module:16')->group(function () {
		Route::get('dispute-list', [AdminDisputeController::class, 'index'])->name('admin.dispute.index');
		Route::get('dispute-search', [AdminDisputeController::class, 'search'])->name('admin.dispute.search');
		Route::put('defender-mute-unmute/{utr}/{option}', [AdminDisputeController::class, 'defenderMuteUnmute'])->name('admin.defender.mute.unmute');
		Route::put('claimer-mute-unmute/{utr}/{option}', [AdminDisputeController::class, 'claimerMuteUnmute'])->name('admin.claimer.mute.unmute');
		Route::put('dispute-status-change/{utr}/{option}', [AdminDisputeController::class, 'disputeStatusChange'])->name('admin.dispute.status.change');

		Route::match(['get', 'put'], 'dispute-view/{utr}', [AdminDisputeController::class, 'adminDisputeView'])->name('admin.dispute.view');
		Route::get('dispute-file-download/{utr}/{file}', [AdminDisputeController::class, 'adminDisputeDownload'])->name('admin.dispute.file.download');
	});

	/* ===== ADMIN QR PAYMENT DETAILS ===== */
	Route::middleware('module:17')->group(function () {
		Route::get('qr-payment/list', [UserController::class, 'qrPayment'])->name('admin.qr.payment');
	});

	/* ===== ADMIN TEMPLATE SETTINGS ===== */
	Route::middleware('module:20')->group(function () {
		Route::get('template/{section}', [TemplateController::class, 'show'])->name('template.show');
		Route::put('template/{section}/{language}', [TemplateController::class, 'update'])->name('template.update');

		Route::get('contents/{content}', [ContentController::class, 'index'])->name('content.index');
		Route::get('content-create/{content}', [ContentController::class, 'create'])->name('content.create');
		Route::put('content-create/{content}/{language?}', [ContentController::class, 'store'])->name('content.store');
		Route::get('content-show/{content}', [ContentController::class, 'show'])->name('content.show');
		Route::put('content-update/{content}/{language?}', [ContentController::class, 'update'])->name('content.update');
		Route::delete('content-delete/{id}', [ContentController::class, 'destroy'])->name('content.delete');
	});

	Route::match(['get', 'post'], 'logo-settings', [HomeController::class, 'logoUpdate'])->name('logo.update');
	Route::match(['get', 'post'], 'breadcrumb-settings', [HomeController::class, 'breadcrumbUpdate'])->name('breadcrumb.update');
	Route::match(['get', 'post'], 'seo-settings', [HomeController::class, 'seoUpdate'])->name('seo.update');

	/* ===== SUBSCRIBER VIEW MANAGE BY ADMIN ===== */
	Route::get('subscriber-list', [SubscribeController::class, 'index'])->name('subscribe.index');
	Route::get('subscriber-search', [SubscribeController::class, 'search'])->name('subscribe.search');
	Route::match(['get', 'post'], 'send-mail-subscriber/{subscribe?}', [SubscribeController::class, 'sendMailSubscribe'])->name('send.mail.subscribe');

	/* Transaction List*/
	Route::middleware('module:18')->group(function () {
		Route::get('transaction-list', [AdminTransactionController::class, 'index'])->name('admin.transaction.index');
		Route::get('transaction-search', [AdminTransactionController::class, 'search'])->name('admin.transaction.search');
		Route::get('transaction-list/{userId}', [AdminTransactionController::class, 'showByUser'])->name('admin.user.transaction.show');
		Route::get('transaction-search/{userId}', [AdminTransactionController::class, 'searchByUser'])->name('admin.user.transaction.search');
	});

	Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.home');

	Route::middleware('module:19')->group(function () {
		Route::match(['get', 'post'], 'referral-bonus', [ReferralBonusController::class, 'show'])->name('admin.referral.bonus.index');
		Route::get('commission-list', [CommissionEntryController::class, 'indexAdmin'])->name('admin.commission.index');
		Route::get('commission-search', [CommissionEntryController::class, 'searchAdmin'])->name('admin.commission.search');
	});

	Route::post('logout', [LoginController::class, 'logout'])->name('admin.logout');

	/* Store Management */
	Route::middleware('module:7')->group(function () {
		Route::get('store/list', [AdminProductController::class, 'storeList'])->name('admin.store.list');
		Route::get('store/list/view/{id}', [AdminProductController::class, 'storeView'])->name('admin.store.view');
		Route::get('store/product/list', [AdminProductController::class, 'productList'])->name('admin.product.list');
		Route::get('store/product/view/{id}', [AdminProductController::class, 'productView'])->name('admin.product.view');
		Route::get('store/product/order/{stage?}', [AdminProductController::class, 'orderList'])->name('admin.order.list');
		Route::get('store/product/order/view/{orderNumber}', [AdminProductController::class, 'orderView'])->name('admin.order.view');
		Route::get('store/contact/list', [AdminProductController::class, 'contactList'])->name('admin.contact.list');
	});


	/* VIRTUAL CARD MANAGE BY ADMIN*/
	Route::middleware('module:10')->group(function () {
		Route::get('virtual-card', [VirtualCardController::class, 'index'])->name('admin.virtual.card');
		Route::get('virtual-card/edit/{id}', [VirtualCardController::class, 'edit'])->name('admin.virtual.cardEdit');
		Route::put('virtual-card/update/{id}', [VirtualCardController::class, 'update'])->name('admin.virtual.cardUpdate');
		Route::post('virtual-card/status-change/{id}', [VirtualCardController::class, 'statusChange'])->name('admin.virtual.cardStatusCng');

		Route::get('virtual-card/order', [VirtualCardController::class, 'cardOrder'])->name('admin.virtual.cardOrder');
		Route::get('virtual-card/order/detail/{id}', [VirtualCardController::class, 'cardOrderDetail'])->name('admin.virtual.cardOrderDetail');
		Route::post('virtual-card/order/rejected/{id}', [VirtualCardController::class, 'cardOrderRejected'])->name('admin.virtual.cardOrderRejected');
		Route::get('virtual-card/order/approve/{id}', [VirtualCardController::class, 'cardOrderApprove'])->name('admin.virtual.cardOrderApprove');

		Route::get('virtual-card/list/{type?}', [VirtualCardController::class, 'cardList'])->name('admin.virtual.cardList');
		Route::get('virtual-card/list/view/{id}', [VirtualCardController::class, 'cardView'])->name('admin.virtual.cardView');
		Route::get('virtual-card/transaction/{id}', [VirtualCardController::class, 'cardTransaction'])->name('admin.virtual.cardTransaction');
		Route::post('virtual-card/block/{id}', [VirtualCardController::class, 'cardBlock'])->name('admin.virtual.cardBlock');
		Route::post('virtual-card/un-block/{id}', [VirtualCardController::class, 'cardUnBlock'])->name('admin.virtual.cardUnBlock');
		Route::post('virtual-card/fund-approve/{id}', [VirtualCardController::class, 'cardFundApprove'])->name('admin.virtual.cardFundApprove');
		Route::post('virtual-card/fund-return/{id}', [VirtualCardController::class, 'cardFundReturn'])->name('admin.virtual.cardFundReturn');
	});
});


Route::group(['prefix' => 'user', 'middleware' => ['auth', 'verifyUser']], function () {

	Route::post('/save-token', [HomeController::class, 'saveToken'])->name('user.save.token');
	Route::any('setting', [UserSettingController::class, 'index'])->name('user.setting');
	Route::get('commission-list', [CommissionEntryController::class, 'index'])->name('user.commission.index');
	Route::get('commission-search', [CommissionEntryController::class, 'search'])->name('user.commission.search');

	Route::any('api-key', [UserSettingController::class, 'apiKey'])->name('user.api.key');
	Route::get('api-key/mode-change/{mode}', [UserSettingController::class, 'modeChange'])->name('user.mode.change');

	Route::any('api-documentation', [UserSettingController::class, 'apiDocx'])->name('user.api.docx');

	Route::get('/dashboard', [HomeController::class, 'index'])->name('user.dashboard');
	Route::get('get-transaction-chart', [HomeController::class, 'getTransactionChart'])->name('user.get.transaction.chart');

	/* Transaction List*/
	Route::get('transaction-list', [TransactionController::class, 'index'])->name('user.transaction');
	Route::get('transaction-search', [TransactionController::class, 'search'])->name('user.transaction.search');

	Route::get('push-notification-show', [SiteNotificationController::class, 'show'])->name('push.notification.show');
	Route::get('push.notification.readAll', [SiteNotificationController::class, 'readAll'])->name('push.notification.readAll');
	Route::get('push-notification-readAt/{id}', [SiteNotificationController::class, 'readAt'])->name('push.notification.readAt');

	/* PROFILE SHOW UPDATE BY USER */
	Route::match(['get', 'post'], 'profile', [UserProfileController::class, 'index'])->name('user.profile');
	Route::match(['get', 'post'], 'change-password', [UserProfileController::class, 'changePassword'])->name('user.change.password');

	/* PAY Bill BY USER */
	Route::get('pay-bill', [PayBillController::class, 'payBill'])->name('pay.bill')->middleware('Ensure:bill_payment');
	Route::post('pay-bill/submit', [PayBillController::class, 'payBillSubmit'])->name('pay.bill.submit')->middleware('Ensure:bill_payment');
	Route::any('pay-bill/confirm/{utr}', [PayBillController::class, 'payBillConfirm'])->name('pay.bill.confirm')->middleware('Ensure:bill_payment');
	Route::post('pay-bill/fetch-services', [PayBillController::class, 'fetchServices'])->name('fetch.services')->middleware('Ensure:bill_payment');

	Route::get('pay-bill/list', [PayBillController::class, 'payBillList'])->name('pay.bill.list')->middleware('Ensure:bill_payment');

	/* PAYMENT REQUEST BY USER */
	Route::get('payout-list', [PayoutController::class, 'index'])->name('payout.index')->middleware('Ensure:payout');
	Route::get('payout-search', [PayoutController::class, 'search'])->name('payout.search')->middleware('Ensure:payout');
	Route::match(['get', 'post'], 'request-payout', [PayoutController::class, 'payoutRequest'])->name('payout.request')->middleware('Ensure:payout');
	Route::match(['get', 'post'], 'confirm-payout/{utr}', [PayoutController::class, 'confirmPayout'])->name('payout.confirm')->middleware('Ensure:payout');
	Route::post('confirm-payout/flutterwave/{utr}', [PayoutController::class, 'flutterwavePayout'])->name('payout.flutterwave')->middleware('Ensure:payout');
	Route::post('confirm-payout/paystack/{utr}', [PayoutController::class, 'paystackPayout'])->name('payout.paystack')->middleware('Ensure:payout');
	Route::get('payout-check-limit', [PayoutController::class, 'checkLimit'])->name('payout.checkLimit')->middleware('Ensure:payout');
	Route::post('payout-bank-form', [PayoutController::class, 'getBankForm'])->name('payout.getBankForm')->middleware('Ensure:payout');
	Route::post('payout-bank-list', [PayoutController::class, 'getBankList'])->name('payout.getBankList')->middleware('Ensure:payout');

	/* EXCHANGE CURRENCY BY USER */
	Route::get('exchange-list', [ExchangeController::class, 'index'])->name('exchange.index')->middleware('Ensure:exchange');
	Route::get('exchange-search', [ExchangeController::class, 'search'])->name('exchange.search')->middleware('Ensure:exchange');
	Route::match(['get', 'post'], 'exchange', [ExchangeController::class, 'initialize'])->name('exchange.initialize')->middleware('Ensure:exchange');
	Route::match(['get', 'post'], 'confirm-exchange/{utr}', [ExchangeController::class, 'confirmExchange'])->name('exchange.confirm')->middleware('Ensure:exchange');
	Route::get('currencies-except-selected', [ExchangeController::class, 'currenciesExceptSelected'])->name('currencies.except.selected')->middleware('Ensure:exchange');
	Route::get('exchange-check-amount', [ExchangeController::class, 'checkAmount'])->name('exchange.checkAmount')->middleware('Ensure:exchange');

	/* REQUEST MONEY BY USER */
	Route::get('request-money-list', [RequestMoneyController::class, 'index'])->name('requestMoney.index')->middleware('Ensure:request');
	Route::get('request-money-search', [RequestMoneyController::class, 'search'])->name('requestMoney.search')->middleware('Ensure:request');
	Route::match(['get', 'post'], 'request-money', [RequestMoneyController::class, 'initialize'])->name('requestMoney.initialize')->middleware('Ensure:request');
	Route::get('requestMoney-check-recipient', [RequestMoneyController::class, 'checkRecipient'])->name('requestMoney.checkRecipient')->middleware('Ensure:request');
	Route::get('requestMoney-check-amount', [RequestMoneyController::class, 'checkInitiateAmount'])->name('requestMoney.checkAmount')->middleware('Ensure:request');
	Route::match(['get', 'post'], 'check-request-money/{utr}', [RequestMoneyController::class, 'checkRequestMoney'])->name('requestMoney.check')->middleware('Ensure:request');
	Route::match(['get', 'post'], 'confirm-request-money/{utr}', [RequestMoneyController::class, 'confirmRequestMoney'])->name('requestMoney.confirm')->middleware('Ensure:request');
	Route::get('cancel-request-money/{utr}', [RequestMoneyController::class, 'cancelRequestMoney'])->name('requestMoney.cancel')->middleware('Ensure:request');

	/* SEND MONEY BY USER */
	Route::get('transfer-list', [TransferController::class, 'index'])->name('transfer.index')->middleware('Ensure:transfer');
	Route::get('transfer-search', [TransferController::class, 'search'])->name('transfer.search')->middleware('Ensure:transfer');
	Route::match(['get', 'post'], 'transfer', [TransferController::class, 'initialize'])->name('transfer.initialize')->middleware('Ensure:transfer');
	Route::match(['get', 'post'], 'confirm-transfer/{utr}', [TransferController::class, 'confirmTransfer'])->name('transfer.confirm')->middleware('Ensure:transfer');
	Route::get('transfer-check-recipient', [TransferController::class, 'checkRecipient'])->name('transfer.checkRecipient')->middleware('Ensure:transfer');
	Route::get('transfer-check-amount', [TransferController::class, 'checkAmount'])->name('transfer.checkAmount')->middleware('Ensure:transfer');

	/* ADD MONEY BY USER */
	Route::match(['get', 'post'], 'add-fund/{from?}/{id?}', [FundController::class, 'initialize'])->name('fund.initialize')->middleware('Ensure:deposit');
	Route::get('fund-list', [FundController::class, 'index'])->name('fund.index')->middleware('Ensure:deposit');
	Route::get('fund-search', [FundController::class, 'search'])->name('fund.search')->middleware('Ensure:deposit');

	/* REDEEM CODE BY USER */
	Route::get('redeem-list', [RedeemCodeController::class, 'index'])->name('redeem.index')->middleware('Ensure:redeem');
	Route::get('redeem-search', [RedeemCodeController::class, 'search'])->name('redeem.search')->middleware('Ensure:redeem');
	Route::match(['get', 'post'], 'generate-redeem-code', [RedeemCodeController::class, 'initialize'])->name('redeem.initialize')->middleware('Ensure:redeem');
	Route::match(['get', 'post'], 'confirm-generate/{utr}', [RedeemCodeController::class, 'confirmGenerate'])->name('redeem.confirm')->middleware('Ensure:redeem');
	Route::match(['get', 'post'], 'insert-redeem-code', [RedeemCodeController::class, 'insertRedeemCode'])->name('redeem.insert')->middleware('Ensure:redeem');
	Route::get('redeem-check-amount', [RedeemCodeController::class, 'checkAmount'])->name('redeem.checkAmount')->middleware('Ensure:redeem');

	/* ESCROW BY USER */
	Route::get('escrow-list', [EscrowController::class, 'index'])->name('escrow.index')->middleware('Ensure:escrow');
	Route::get('escrow-search', [EscrowController::class, 'search'])->name('escrow.search')->middleware('Ensure:escrow');
	Route::match(['get', 'post'], 'escrow-create-request', [EscrowController::class, 'create'])->name('escrow.createRequest')->middleware('Ensure:escrow');
	Route::match(['get', 'post'], 'escrow-confirm-init/{utr}', [EscrowController::class, 'confirmInit'])->name('escrow.confirmInit')->middleware('Ensure:escrow');
	Route::get('escrow-check-recipient', [EscrowController::class, 'checkRecipient'])->name('escrow.checkRecipient')->middleware('Ensure:escrow');
	Route::get('escrow-check-init-amount', [EscrowController::class, 'checkInitiateAmount'])->name('escrow.checkInitiateAmount')->middleware('Ensure:escrow');

	/* payment and disbursed request action from here */
	Route::match(['get', 'post'], 'escrow-payment-view/{utr}', [EscrowController::class, 'escrowPaymentView'])->name('escrow.paymentView');

	/* USER DISPUTE */
	Route::get('dispute-list', [DisputeController::class, 'index'])->name('user.dispute.index');
	Route::get('dispute-search', [DisputeController::class, 'search'])->name('user.dispute.search');
	Route::match(['get', 'put'], 'dispute-view/{utr}', [DisputeController::class, 'userDisputeView'])->name('user.dispute.view');
	Route::get('dispute-file-download/{utr}/{file}', [DisputeController::class, 'userDisputeDownload'])->name('user.dispute.file.download');

	/* USER QR PAYMENT */
	Route::get('qr-payment-list', [HomeController::class, 'qrPaymentList'])->name('user.qr.payment')->middleware('Ensure:qr_payment');

	/* VOUCHER PAYMENT BY USER */
	Route::get('voucher-list', [VoucherController::class, 'index'])->name('voucher.index')->middleware('Ensure:voucher');
	Route::get('voucher-search', [VoucherController::class, 'search'])->name('voucher.search')->middleware('Ensure:voucher');
	Route::match(['get', 'post'], 'voucher-create-request', [VoucherController::class, 'create'])->name('voucher.createRequest')->middleware('Ensure:voucher');
	Route::match(['get', 'post'], 'voucher-confirm-init/{utr}', [VoucherController::class, 'confirmInit'])->name('voucher.confirmInit')->middleware('Ensure:voucher');
	Route::get('voucher-check-recipient', [VoucherController::class, 'checkRecipient'])->name('voucher.checkRecipient')->middleware('Ensure:voucher');
	Route::get('voucher-check-init-amount', [VoucherController::class, 'checkInitiateAmount'])->name('voucher.checkInitiateAmount')->middleware('Ensure:voucher');
	Route::match(['get', 'post'], 'voucher-payment-view/{utr}', [VoucherController::class, 'voucherPaymentView'])->name('voucher.paymentView')->middleware('Ensure:voucher');

	/* Invoice Payment */
	Route::get('invoice/list', [InvoiceController::class, 'index'])->name('invoice.index')->middleware('Ensure:invoice');
	Route::get('invoice/search', [InvoiceController::class, 'search'])->name('invoice.search')->middleware('Ensure:invoice');
	Route::get('/invoice/view/{id}', [InvoiceController::class, 'viewInvoice'])->name('invoice.view')->middleware('Ensure:invoice');
	Route::post('/invoice/reminder', [InvoiceController::class, 'invoiceReminder'])->name('invoiceReminder')->middleware('Ensure:invoice');
	Route::get('/invoice/generate-pdf', [InvoiceController::class, 'generatePdf'])->name('generatePdf')->middleware('Ensure:invoice');
	Route::get('/invoice', [InvoiceController::class, 'create'])->name('invoice.create');
	Route::post('/invoice/store', [InvoiceController::class, 'store'])->name('invoice.store')->middleware('Ensure:invoice');
	Route::post('/currency/check', [InvoiceController::class, 'currencyCheck'])->name('currency.check')->middleware('Ensure:invoice');


	/* MANAGE SECURITY PIN BY USER */
	Route::get('security-pin/create', [TwoFactorSettingController::class, 'create'])->name('securityPin.create');
	Route::post('security-pin/store', [TwoFactorSettingController::class, 'store'])->name('securityPin.store');
	Route::match(['get', 'post'], 'security-pin/reset', [TwoFactorSettingController::class, 'reset'])->name('securityPin.reset');
	Route::match(['get', 'post'], 'security-pin/manage', [TwoFactorSettingController::class, 'manage'])->name('securityPin.manage');

	/* USER SUPPORT TICKET */
	Route::get('tickets', [SupportController::class, 'index'])->name('user.ticket.list');
	Route::get('ticket-create', [SupportController::class, 'create'])->name('user.ticket.create');
	Route::post('ticket-create', [SupportController::class, 'store'])->name('user.ticket.store');
	Route::get('ticket-view/{ticket}', [SupportController::class, 'view'])->name('user.ticket.view');
	Route::put('ticket-reply/{ticket}', [SupportController::class, 'reply'])->name('user.ticket.reply');
	Route::get('ticket-download/{ticket}', [SupportController::class, 'download'])->name('user.ticket.download');

	/* Store Management */
	Route::get('store/list', [StoreController::class, 'storeList'])->name('store.list')->middleware('Ensure:store');
	Route::any('store/create', [StoreController::class, 'storeCreate'])->name('store.create')->middleware('Ensure:store');
	Route::any('store/edit/{id}', [StoreController::class, 'storeEdit'])->name('store.edit')->middleware('Ensure:store');
	Route::delete('store/delete/{id}', [StoreController::class, 'storeDelete'])->name('store.delete')->middleware('Ensure:store');
	Route::post('store/link/check', [StoreController::class, 'storeLinkCheck'])->name('store.link.check')->middleware('Ensure:store');

	/* Store Category */
	Route::get('store/category/list', [StoreCategoryController::class, 'categoryList'])->name('category.list')->middleware('Ensure:store');
	Route::post('store/category/save', [StoreCategoryController::class, 'categorySave'])->name('category.save')->middleware('Ensure:store');
	Route::post('store/category/update', [StoreCategoryController::class, 'categoryUpdate'])->name('category.update')->middleware('Ensure:store');
	Route::delete('store/category/delete/{id}', [StoreCategoryController::class, 'categoryDelete'])->name('category.delete')->middleware('Ensure:store');

	/* Store Shipping */
	Route::get('store/shipping/list', [StoreShippingController::class, 'shippingList'])->name('shipping.list')->middleware('Ensure:store');
	Route::post('store/shipping/save', [StoreShippingController::class, 'shippingSave'])->name('shipping.save')->middleware('Ensure:store');
	Route::post('store/shipping/update', [StoreShippingController::class, 'shippingUpdate'])->name('shipping.update')->middleware('Ensure:store');
	Route::delete('store/shipping/delete/{id}', [StoreShippingController::class, 'shippingDelete'])->name('shipping.delete')->middleware('Ensure:store');

	/* Store Product Attribute */
	Route::get('store/product/attribute', [StoreProductAttrController::class, 'attrList'])->name('attr.list')->middleware('Ensure:store');
	Route::any('store/product/attribute/create', [StoreProductAttrController::class, 'attrCreate'])->name('attr.create')->middleware('Ensure:store');
	Route::any('store/product/attribute/edit/{id}', [StoreProductAttrController::class, 'attrEdit'])->name('attr.edit')->middleware('Ensure:store');
	Route::delete('store/product/attribute/delete/{id}', [StoreProductAttrController::class, 'attrDelete'])->name('attr.delete')->middleware('Ensure:store');

	/* Store Product */
	Route::get('store/product/list', [StoreProductController::class, 'productList'])->name('product.list')->middleware('Ensure:store');
	Route::any('store/product/create', [StoreProductController::class, 'productCreate'])->name('product.create')->middleware('Ensure:store');
	Route::get('store/product/view/{id}', [StoreProductController::class, 'productView'])->name('product.view')->middleware('Ensure:store');
	Route::any('store/product/edit/{id}', [StoreProductController::class, 'productEdit'])->name('product.edit')->middleware('Ensure:store');
	Route::delete('store/product/image/delete/{id}', [StoreProductController::class, 'productImageDelete'])->name('product.image.delete')->middleware('Ensure:store');
	Route::delete('store/product/delete/{id}', [StoreProductController::class, 'productDelete'])->name('product.delete')->middleware('Ensure:store');

	/* Product Stock */
	Route::get('store/product/stock/list', [StoreProductStockController::class, 'stockList'])->name('stock.list')->middleware('Ensure:store');
	Route::any('store/product/stock/create', [StoreProductStockController::class, 'stockCreate'])->name('stock.create')->middleware('Ensure:store');
	Route::get('store/product/stock/view/{productId}', [StoreProductStockController::class, 'stockEdit'])->name('stock.view')->middleware('Ensure:store');
	Route::get('store/product/stock-edit/fetch', [StoreProductStockController::class, 'stockEditFetch'])->name('stock.edit.fetch')->middleware('Ensure:store');
	Route::get('store/product/stock-attr/fetch', [StoreProductStockController::class, 'stockAttrFetch'])->name('stock.attr.fetch')->middleware('Ensure:store');

	/* Product Order */
	Route::get('store/product/order/{stage?}', [ProductOrderController::class, 'orderList'])->name('order.list')->middleware('Ensure:store');
	Route::get('store/product/order/view/{orderNumber}', [ProductOrderController::class, 'orderView'])->name('order.view')->middleware('Ensure:store');
	Route::post('store/product/order/stage/change', [ProductOrderController::class, 'stageChange'])->name('order.stage.change')->middleware('Ensure:store');
	Route::post('store/product/order/single/stage/change/{orderId}', [ProductOrderController::class, 'singleStageChange'])->name('single.stage.change')->middleware('Ensure:store');

	/* QR Code */
	Route::get('qr-code', [HomeController::class, 'qrCode'])->name('user.qr.code')->middleware('Ensure:qr_payment');

	/* Virtual Card */
	Route::get('virtual-card', [UserVirtualCardController::class, 'index'])->name('user.virtual.card')->middleware('Ensure:virtual_card');
	Route::get('virtual-card/order', [UserVirtualCardController::class, 'order'])->name('user.virtual.card.order')->middleware('Ensure:virtual_card');
	Route::post('virtual-card/order/submit', [UserVirtualCardController::class, 'orderSubmit'])->name('user.virtual.card.orderSubmit')->middleware('Ensure:virtual_card');
	Route::match(['get', 'post'], 'virtual-card/confirm/{utr}', [UserVirtualCardController::class, 'confirmOrder'])->name('order.confirm')->middleware('Ensure:virtual_card');
	Route::post('virtual-card/order/payment/{orderId}', [UserVirtualCardController::class, 'cardOrderPayment'])->name('user.virtual.card.orderPayment')->middleware('Ensure:virtual_card');
	Route::any('virtual-card/order/re-submit', [UserVirtualCardController::class, 'orderReSubmit'])->name('user.virtual.card.orderReSubmit')->middleware('Ensure:virtual_card');

	Route::post('virtual-card/block/{id}', [UserVirtualCardController::class, 'cardBlock'])->name('user.virtual.cardBlock')->middleware('Ensure:virtual_card');
	Route::get('virtual-card/transaction/{id}', [UserVirtualCardController::class, 'cardTransaction'])->name('user.virtual.cardTransaction')->middleware('Ensure:virtual_card');

	// TWO-FACTOR SECURITY
	Route::get('/twostep-security', [FaSecurityController::class, 'twoStepSecurity'])->name('user.twostep.security');
	Route::post('twoStep-enable', [FaSecurityController::class, 'twoStepEnable'])->name('user.twoStepEnable');
	Route::post('twoStep-disable', [FaSecurityController::class, 'twoStepDisable'])->name('user.twoStepDisable');

	// PUSH NOTIFY
	Route::get('/push-notify', [UserSettingController::class, 'settingNotify'])->name('list.setting.notify');
	Route::put('/push-notify', [UserSettingController::class, 'settingNotifyUpdate'])->name('update.setting.notify');


});

Route::group(['prefix' => 'user'], function () {
	Auth::routes();
	// Payment confirm page
	Route::get('deposit-check-amount', [DepositController::class, 'checkAmount'])->name('deposit.checkAmount');
	Route::get('payment-process/{utr}', [PaymentController::class, 'depositConfirm'])->name('payment.process');
	Route::match(['get', 'post'], 'confirm-deposit/{utr}', [DepositController::class, 'confirmDeposit'])->name('deposit.confirm')->middleware('Ensure:deposit');

	Route::get('check', [VerificationController::class, 'check'])->name('user.check');
	Route::get('resend_code', [VerificationController::class, 'resendCode'])->name('user.resendCode');
	Route::post('mail-verify', [VerificationController::class, 'mailVerify'])->name('user.mailVerify');
	Route::post('sms-verify', [VerificationController::class, 'smsVerify'])->name('user.smsVerify');
	Route::post('twoFA-Verify', [VerificationController::class, 'twoFAverify'])->name('user.twoFA-Verify');

	Route::get('kyc/fill-up', [HomeController::class, 'kycShow'])->name('user.kycShow');
	Route::post('kyc/fill-up/store', [HomeController::class, 'kycStore'])->name('user.kycStore');
	Route::get('kyc/verification/center', [HomeController::class, 'kycList'])->name('user.kycList');
	Route::get('kyc/verification/view/{id}', [HomeController::class, 'kycView'])->name('user.kycView');

});

// Voucher payment public view
Route::match(['get', 'post'], 'voucher-payment-public-view/{utr}', [VoucherController::class, 'voucherPaymentPublicView'])->name('voucher.paymentPublicView');
Route::match(['get', 'post'], 'voucher-public-payment/{utr}', [VoucherController::class, 'voucherPublicPayment'])->name('voucher.public.payment');

//Invoice payment public view
Route::get('/invoice/{hash_slug}', [InvoiceController::class, 'showPublicInvoice'])->name('public.invoice.show');
Route::post('/invoice/payment-confirm/{hash_slug}', [InvoiceController::class, 'publicInvoicePaymentConfirm'])->name('public.invoice.payment.confirm');
Route::match(['get', 'post'], 'invoice/public/payment/{hash_slug}', [InvoiceController::class, 'invoicePublicPayment'])->name('invoice.public.payment');
Route::get('/reject-invoice-from-email/{hash_slug}', [InvoiceController::class, 'rejectInvoiceFromEmail'])->name('reject.invoice.from.email');
Route::get('/download-pdf/{invoiceId}', [InvoiceController::class, 'downloadPdf'])->name('downloadPdf');

Route::get('/', [HomeController::class, 'home'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/faq', [HomeController::class, 'faq'])->name('faq');
Route::get('/blog', [HomeController::class, 'blog'])->name('blog');
Route::get('/blog-details/{contentDetails}', [HomeController::class, 'blogDetails'])->name('blogDetails');
Route::match(['get', 'post'], '/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('subscribe', [HomeController::class, 'subscribe'])->name('subscribe');
Route::get('{content_id}/{getLink}', [HomeController::class, 'getLink'])->name('getLink');
Route::get('/{template}', [HomeController::class, 'getTemplate'])->name('getTemplate');

Route::get('/set/language/{code?}', [HomeController::class, 'setLanguage'])->name('set.language');

/* Public Store */
Route::get('store/product/{link?}', [StoreShopController::class, 'shopProduct'])->name('public.view');
Route::get('store/product/{link?}/details/{title}/{id}', [StoreShopController::class, 'shopProductDetails'])->name('public.product.details');

//Cart
Route::get('store/product/stock/check', [PublicCartController::class, 'stockCheck'])->name('public.stock.check');
Route::get('store/product/attr/check', [PublicCartController::class, 'stockAttrCheck'])->name('product.attr.check');
Route::post('store/product/stock/check', [PublicCartController::class, 'ProductStockCheck'])->name('product.check');
Route::get('store/product/attr/list', [PublicCartController::class, 'attrList'])->name('product.attributes.list');
Route::get('store/product/{link}/cart', [PublicCartController::class, 'productCart'])->name('public.cart');
Route::get('store/product/{link}/checkout', [PublicChekoutController ::class, 'productCheckout'])->name('public.checkout');
Route::post('store/product/checkout/store', [PublicChekoutController ::class, 'productCheckoutStore'])->name('public.checkout.store');

//Order Track
Route::get('store/product/{link}/track', [PublicCartController::class, 'productTrack'])->name('public.product.track');
Route::get('store/product/order/download/{orderId}', [PublicCartController::class, 'productOrderDownload'])->name('public.product.orderDownload');

Route::get('store/product/{link}/seller', [StoreShopController::class, 'sellerDetails'])->name('public.seller.details');
Route::post('store/product/{link}/seller/contact', [StoreShopController::class, 'sellerContact'])->name('public.seller.contact');

//OR Code Payment
Route::any('public/qr-payment/{link}', [QrCodePaymentController::class, 'qrPayment'])->name('public.qr.Payment');

//Api Payment
Route::get('make/payment/{mode}/{utr}', [ApiController::class, 'makePayment'])->name('make.payment');
Route::post('make/payment/confirm/{mode}/{utr}', [ApiController::class, 'makePaymentConfirm'])->name('make.payment.confirm');


// Route::get('installments', function(){
// 	return view('admin.financial.installments.test');
// });
