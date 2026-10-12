<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepositController;
use App\Http\Controllers\Admin\OtpController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WithdrawalController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    // Public admin routes
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');

    // Protected admin routes
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('otps', [OtpController::class, 'index'])->name('otps.index');
        Route::post('otps/{otp}/invalidate', [OtpController::class, 'invalidate'])->name('otps.invalidate');

        // Users
        Route::resource('users', UserController::class)->except(['index']);
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('users/{user}/wallet', [UserController::class, 'adjustWallet'])->name('users.wallet');

        // Deposits
        Route::resource('deposits', DepositController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('deposits/{deposit}/approve', [DepositController::class, 'approve'])->name('deposits.approve');
        Route::post('deposits/{deposit}/reject', [DepositController::class, 'reject'])->name('deposits.reject');

        // Withdrawals
        Route::resource('withdrawals', WithdrawalController::class)->only(['index', 'show']);
        Route::post('withdrawals/{withdrawal}/approve', [WithdrawalController::class, 'approve'])->name('withdrawals.approve');
        Route::post('withdrawals/{withdrawal}/mark-paid', [WithdrawalController::class, 'markPaid'])->name('withdrawals.mark-paid');
        Route::post('withdrawals/{withdrawal}/reject', [WithdrawalController::class, 'reject'])->name('withdrawals.reject');
    });
});

Route::redirect('/', '/admin/dashboard');
