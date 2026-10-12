<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Setting;
use App\Models\User;
use App\Services\DepositService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepositController extends Controller
{
    public function __construct(private DepositService $depositService) {}

    public function index(Request $request)
    {
        $query = Deposit::with('user');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('txn_id', 'like', "%{$search}%")
                    ->orWhere('razorpay_payment_id', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%")
                        ->orWhere('customer_id', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->date('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->date('to'));
        }

        $deposits = $query->latest()->paginate(20)->withQueryString();

        return view('admin.deposits.index', compact('deposits'));
    }

    public function create()
    {
        $bounds = Setting::bounds();
        $previewAmount = 1000;
        $preview = $this->depositService->calculate($previewAmount);

        return view('admin.deposits.create', compact('bounds', 'preview', 'previewAmount'));
    }

    public function store(Request $request)
    {
        $bounds = Setting::bounds();
        $data = $request->validate([
            'customer_id' => 'required|string|exists:users,customer_id',
            'amount' => 'required|numeric|min:' . $bounds['min'] . '|max:' . $bounds['max'],
            'note' => 'nullable|string|max:500',
            'credit_now' => 'nullable|boolean',
        ]);

        $user = User::where('customer_id', $data['customer_id'])->where('role', 'user')->first();

        if (!$user) {
            return back()->withErrors(['customer_id' => 'No customer found with that ID.'])->withInput();
        }

        $deposit = $this->depositService->create($user, (float) $data['amount']);
        $deposit->update([
            'payment_method' => 'manual',
            'admin_note' => $data['note'] ?? 'Entered by admin',
        ]);

        if ($request->boolean('credit_now')) {
            $this->depositService->approve($deposit, Auth::id(), $data['note'] ?? 'Manual deposit credited by admin');
            return redirect()->route('admin.deposits.show', $deposit)->with('success', 'Deposit created and wallet credited.');
        }

        return redirect()->route('admin.deposits.show', $deposit)->with('success', 'Manual deposit saved as pending.');
    }

    public function show(Deposit $deposit)
    {
        $deposit->load('user');
        return view('admin.deposits.show', compact('deposit'));
    }

    public function approve(Request $request, Deposit $deposit)
    {
        $request->validate(['note' => 'nullable|string|max:500']);

        if ($deposit->status !== 'pending') {
            return back()->with('error', 'Deposit already processed.');
        }

        if ($deposit->payment_method === 'razorpay' && empty($deposit->razorpay_payment_id)) {
            return back()->with('error', 'Razorpay payment is not captured yet. Wallet is credited only after a verified payment.');
        }

        $this->depositService->approve($deposit, Auth::id(), $request->note);

        return back()->with('success', "Deposit {$deposit->txn_id} approved. ₹{$deposit->withdrawal_amount} added to user wallet.");
    }

    public function reject(Request $request, Deposit $deposit)
    {
        $request->validate(['note' => 'required|string|max:500']);

        if ($deposit->status !== 'pending') {
            return back()->with('error', 'Deposit already processed.');
        }

        $this->depositService->reject($deposit, Auth::id(), $request->note);

        return back()->with('success', "Deposit {$deposit->txn_id} rejected.");
    }
}
