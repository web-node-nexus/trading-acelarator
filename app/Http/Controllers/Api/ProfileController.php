<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'phone' => 'sometimes|nullable|string|max:15',
            'address' => 'sometimes|nullable|string|max:255',
            'profile_photo' => 'sometimes|image|max:2048',
        ]);

        if ($request->hasFile('profile_photo')) {
            $validated['profile_photo'] = $request->file('profile_photo')->store('avatars', 'public');
        }

        $request->user()->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated.',
            'data' => $request->user()->fresh(),
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        if (!Hash::check($request->current_password, $request->user()->password)) {
            throw ValidationException::withMessages(['current_password' => 'Current password is incorrect.']);
        }

        $request->user()->update(['password' => Hash::make($request->password)]);

        return response()->json(['success' => true, 'message' => 'Password updated successfully.']);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        $recentDeposits = $user->deposits()->latest()->take(5)->get();
        $recentWithdrawals = $user->withdrawals()->latest()->take(5)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'customer_id' => $user->customer_id,
                'name' => $user->name,
                'wallet_balance' => (float) $user->wallet_balance,
                'total_deposited' => (float) $user->total_deposited,
                'total_withdrawn' => (float) $user->total_withdrawn,
                'available_withdrawal' => (float) $user->available_withdrawal,
                'recent_deposits' => $recentDeposits->map(fn($d) => [
                    'txn_id' => $d->txn_id,
                    'amount' => (float) $d->deposit_amount,
                    'withdrawal_amount' => (float) $d->withdrawal_amount,
                    'status' => $d->status,
                    'date' => $d->created_at->format('d M Y'),
                ]),
                'recent_withdrawals' => $recentWithdrawals->map(fn($w) => [
                    'txn_id' => $w->txn_id,
                    'amount' => (float) $w->amount,
                    'status' => $w->status,
                    'date' => $w->created_at->format('d M Y'),
                ]),
            ],
        ]);
    }
}
