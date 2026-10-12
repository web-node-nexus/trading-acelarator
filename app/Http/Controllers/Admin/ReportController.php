<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now()->endOfDay();

        $deposits = Deposit::with('user')
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);

        $withdrawals = Withdrawal::with('user')
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);

        $summary = [
            'new_users' => User::where('role', 'user')->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])->count(),
            'deposit_count' => (clone $deposits)->count(),
            'approved_deposit_amount' => (clone $deposits)->where('status', 'approved')->sum('deposit_amount'),
            'payout_credited' => (clone $deposits)->where('status', 'approved')->sum('withdrawal_amount'),
            'pending_deposit_amount' => (clone $deposits)->where('status', 'pending')->sum('deposit_amount'),
            'withdrawal_requests' => (clone $withdrawals)->count(),
            'paid_out' => (clone $withdrawals)->where('status', 'paid')->sum('amount'),
            'withdrawal_pending' => (clone $withdrawals)->whereIn('status', ['pending', 'approved'])->sum('amount'),
        ];

        $rows = (clone $deposits)->latest()->paginate(20)->withQueryString();

        return view('admin.reports.index', compact('summary', 'rows', 'from', 'to'));
    }

    public function export(Request $request): StreamedResponse
    {
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now()->endOfDay();

        $deposits = Deposit::with('user')
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($deposits) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['TXN ID', 'Customer ID', 'Name', 'Deposit', 'Deduction', 'Payout', 'Status', 'Method', 'Razorpay Payment', 'Date']);
            foreach ($deposits as $deposit) {
                fputcsv($out, [
                    $deposit->txn_id,
                    $deposit->user->customer_id,
                    $deposit->user->name,
                    $deposit->deposit_amount,
                    $deposit->deduction_amount,
                    $deposit->withdrawal_amount,
                    $deposit->status,
                    $deposit->payment_method,
                    $deposit->razorpay_payment_id,
                    $deposit->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($out);
        }, 'cashflow-deposits-' . now()->format('Ymd') . '.csv');
    }
}
