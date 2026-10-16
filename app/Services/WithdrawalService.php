<?php

namespace App\Services;

use App\Models\Deposit;
use App\Models\Setting;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Validation\ValidationException;

class WithdrawalService
{
    public function request(User $user, Deposit $deposit, array $bankDetails): Withdrawal
    {
        // Check if deposit is approved
        if ($deposit->status !== 'approved') {
            throw ValidationException::withMessages(['deposit' => 'Deposit is not yet approved.']);
        }

        // Check if withdrawal already exists for this deposit
        if ($deposit->withdrawal()->exists()) {
            throw ValidationException::withMessages(['deposit' => 'Withdrawal already requested for this deposit.']);
        }

        $minWithdrawal = Setting::minWithdrawal();
        if ((float) $deposit->withdrawal_amount < $minWithdrawal) {
            throw ValidationException::withMessages([
                'amount' => "Minimum withdrawal is ₹{$minWithdrawal}.",
            ]);
        }

        // Verify user has sufficient wallet balance
        if ($user->wallet_balance < $deposit->withdrawal_amount) {
            throw ValidationException::withMessages(['amount' => 'Insufficient wallet balance.']);
        }

        return Withdrawal::create([
            'user_id' => $user->id,
            'deposit_id' => $deposit->id,
            'amount' => $deposit->withdrawal_amount,
            'bank_name' => $bankDetails['bank_name'] ?? null,
            'account_number' => $bankDetails['account_number'] ?? null,
            'ifsc_code' => $bankDetails['ifsc_code'] ?? null,
            'account_holder_name' => $bankDetails['account_holder_name'] ?? null,
            'upi_id' => $bankDetails['upi_id'] ?? null,
            'otp_verified' => 'yes',
            'status' => 'pending',
        ]);
    }

    public function approve(Withdrawal $withdrawal, int $adminId, ?string $note = null): Withdrawal
    {
        $withdrawal->update([
            'status' => 'approved',
            'processed_by' => $adminId,
            'processed_at' => now(),
            'admin_note' => $note,
        ]);

        return $withdrawal->fresh();
    }

    public function markPaid(Withdrawal $withdrawal, int $adminId, ?string $note = null): Withdrawal
    {
        // Deduct from wallet
        $withdrawal->user->decrement('wallet_balance', $withdrawal->amount);

        $withdrawal->update([
            'status' => 'paid',
            'processed_by' => $adminId,
            'processed_at' => now(),
            'admin_note' => $note,
        ]);

        return $withdrawal->fresh();
    }

    public function reject(Withdrawal $withdrawal, int $adminId, string $note): Withdrawal
    {
        $withdrawal->update([
            'status' => 'rejected',
            'processed_by' => $adminId,
            'processed_at' => now(),
            'admin_note' => $note,
        ]);

        return $withdrawal->fresh();
    }
}
