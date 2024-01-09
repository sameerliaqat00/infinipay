<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Admin\VirtualCardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

Route::middleware('auth:api')->get('/user', function (Request $request) {
	return $request->user();
});
Route::post('virtual-card/ufitpay/callback', [VirtualCardController::class, 'ufitpayCallBack'])->name('ufitpay.Callback');
Route::post('virtual-card/flutterwave/callback', [VirtualCardController::class, 'flutterwavedCallBack'])->name('flutterwave.Callback');
Route::post('payout/{code}', [VirtualCardController::class, 'payout'])->name('payout');

Route::post('payment/initiate', [ApiController::class, 'store'])->name('payment.initiate');
Route::post('payment/verify', [ApiController::class, 'verifyPayment'])->name('payment.verify');

