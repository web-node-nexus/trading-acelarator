<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'user');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
                  ->orWhere('customer_id', 'like', "%{$request->search}%");
            });
        }

        if ($request->status) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->verified === 'yes') {
            $query->where('is_email_verified', true);
        } elseif ($request->verified === 'no') {
            $query->where('is_email_verified', false);
        }

        $users = $query->withCount(['deposits', 'withdrawals'])->latest()->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
            'customer_id' => 'nullable|string|max:30|unique:users,customer_id',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'customer_id' => $data['customer_id'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => 'user',
            'is_email_verified' => $request->boolean('is_email_verified'),
            'is_active' => true,
        ]);

        return redirect()->route('admin.users.show', $user)->with('success', 'Customer created. ID ' . $user->customer_id);
    }

    public function show(User $user)
    {
        $user->load(['deposits', 'withdrawals']);
        $ledger = $user->walletTransactions()->latest()->take(10)->get();

        return view('admin.users.show', compact('user', 'ledger'));
    }

    public function edit(User $user)
    {
        $this->guardCustomer($user);

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->guardCustomer($user);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
            'customer_id' => ['required', 'string', 'max:30', Rule::unique('users', 'customer_id')->ignore($user->id)],
        ]);

        $user->update([
            ...$data,
            'is_active' => $request->boolean('is_active'),
            'is_email_verified' => $request->boolean('is_email_verified'),
        ]);

        return redirect()->route('admin.users.show', $user)->with('success', 'Profile updated.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $this->guardCustomer($user);

        $data = $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', 'Password reset for ' . $user->customer_id . '.');
    }

    public function adjustWallet(Request $request, User $user)
    {
        $this->guardCustomer($user);

        $data = $request->validate([
            'type' => 'required|in:credit,debit',
            'amount' => 'required|numeric|min:1',
            'reason' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($user, $data) {
            $locked = User::whereKey($user->id)->lockForUpdate()->first();
            $amount = round((float) $data['amount'], 2);

            if ($data['type'] === 'debit' && (float) $locked->wallet_balance < $amount) {
                throw ValidationException::withMessages(['amount' => 'Wallet does not have enough balance.']);
            }

            if ($data['type'] === 'credit') {
                $locked->increment('wallet_balance', $amount);
            } else {
                $locked->decrement('wallet_balance', $amount);
            }

            WalletTransaction::create([
                'user_id' => $locked->id,
                'admin_id' => Auth::id(),
                'type' => $data['type'],
                'amount' => $amount,
                'balance_after' => $locked->fresh()->wallet_balance,
                'reason' => $data['reason'],
            ]);
        });

        return back()->with('success', 'Wallet updated.');
    }

    public function toggleStatus(User $user)
    {
        $this->guardCustomer($user);
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activated' : 'suspended';

        return back()->with('success', "User account {$status} successfully.");
    }

    public function destroy(User $user)
    {
        $this->guardCustomer($user);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    private function guardCustomer(User $user): void
    {
        if ($user->role === 'admin') {
            abort(403, 'Admin accounts cannot be changed from customer tools.');
        }
    }
}
