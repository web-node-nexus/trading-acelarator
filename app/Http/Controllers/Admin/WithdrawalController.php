<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Services\WithdrawalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WithdrawalController extends Controller
{
    public function __construct(private WithdrawalService $withdrawalService) {}

    public function index(Request $request)
    {
        $query = Withdrawal::with('user');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('customer_id', 'like', "%{$request->search}%"))
                ->orWhere('txn_id', 'like', "%{$request->search}%");
        }

        $withdrawals = $query->latest()->paginate(20)->withQueryString();

        return view('admin.withdrawals.index', compact('withdrawals'));
    }

    public function show(Withdrawal $withdrawal)
    {
        $withdrawal->load(['user', 'deposit']);
        return view('admin.withdrawals.show', compact('withdrawal'));
    }

    public function approve(Request $request, Withdrawal $withdrawal)
    {
        $request->validate(['note' => 'nullable|string|max:500']);

        if (!in_array($withdrawal->status, ['pending'])) {
            return back()->with('error', 'Withdrawal already processed.');
        }

        $this->withdrawalService->approve($withdrawal, Auth::id(), $request->note);

        return back()->with('success', "Withdrawal {$withdrawal->txn_id} approved.");
    }

    public function markPaid(Request $request, Withdrawal $withdrawal)
    {
        $request->validate(['note' => 'nullable|string|max:500']);

        if ($withdrawal->status !== 'approved') {
            return back()->with('error', 'Withdrawal must be approved first.');
        }

        $this->withdrawalService->markPaid($withdrawal, Auth::id(), $request->note);

        return back()->with('success', "Withdrawal {$withdrawal->txn_id} marked as paid. ₹{$withdrawal->amount} deducted from wallet.");
    }

    public function reject(Request $request, Withdrawal $withdrawal)
    {
        $request->validate(['note' => 'required|string|max:500']);

        if (!in_array($withdrawal->status, ['pending', 'approved'])) {
            return back()->with('error', 'Cannot reject this withdrawal.');
        }

        $this->withdrawalService->reject($withdrawal, Auth::id(), $request->note);

        return back()->with('success', "Withdrawal {$withdrawal->txn_id} rejected.");
    }
}
