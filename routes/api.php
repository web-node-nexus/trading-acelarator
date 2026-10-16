<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DepositController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\WithdrawalController;
use Illuminate\Support\Facades\Route;

Route::post('webhooks/razorpay', [DepositController::class, 'webhook']);

// Public auth routes (phone OTP via 2factor SMS — no password)
Route::prefix('auth')->group(function () {
    Route::post('send-otp', [AuthController::class, 'sendOtp']);
    Route::post('register', [AuthController::class, 'register']); // alias → send-otp (register)
    Route::post('login', [AuthController::class, 'login']);       // alias → send-otp (login)
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('resend-otp', [AuthController::class, 'resendOtp']);
});


// Protected user routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    Route::get('dashboard', [ProfileController::class, 'dashboard']);
    Route::put('profile', [ProfileController::class, 'update']);
    Route::put('profile/password', [ProfileController::class, 'changePassword']);

    Route::get('deposits/preview', [DepositController::class, 'preview']);
    Route::post('deposits/razorpay/order', [DepositController::class, 'createOrder']);
    Route::post('deposits/razorpay/verify', [DepositController::class, 'verifyPayment']);
    Route::post('deposits/razorpay/failed', [DepositController::class, 'paymentFailed']);
    Route::apiResource('deposits', DepositController::class)->only(['index', 'store', 'show']);

    Route::post('withdrawals/request-otp', [WithdrawalController::class, 'requestOtp']);
    Route::apiResource('withdrawals', WithdrawalController::class)->only(['index', 'store']);
});
