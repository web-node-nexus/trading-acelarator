<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Setting;
use App\Models\User;
use App\Models\Withdrawal;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::where('role', 'user')->count(),
            'pending_deposits' => Deposit::where('status', 'pending')->count(),
            'pending_withdrawals' => Withdrawal::where('status', 'pending')->count(),
            'total_deposited' => Deposit::where('status', 'approved')->sum('deposit_amount'),
            'total_withdrawal_due' => Withdrawal::where('status', 'pending')->sum('amount'),
            'total_paid_out' => Withdrawal::where('status', 'paid')->sum('amount'),
            'active_users' => User::where('role', 'user')->where('is_active', true)->count(),
            'unverified_users' => User::where('role', 'user')->where('is_email_verified', false)->count(),
            'ready_to_pay' => Withdrawal::where('status', 'approved')->count(),
            'wallet_liability' => User::where('role', 'user')->sum('wallet_balance'),
            'failed_deposits' => Deposit::where('status', 'failed')->count(),
            'deduction_percent' => Setting::get('deduction_percent', 20),
            'payout_multiplier' => Setting::get('payout_multiplier', 2),
        ];

        $recentDeposits = Deposit::with('user')->latest()->take(8)->get();
        $recentWithdrawals = Withdrawal::with('user')->latest()->take(8)->get();

        return view('admin.dashboard', compact('stats', 'recentDeposits', 'recentWithdrawals'));
    }
}
