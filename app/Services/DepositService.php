<?php

namespace App\Services;

use App\Models\Deposit;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DepositService
{
    const DEDUCTION_PERCENT = 20.00;

    public function calculate(float $amount): array
    {
        $percent = (float) Setting::get('deduction_percent', self::DEDUCTION_PERCENT);
        $multiplier = (float) Setting::get('payout_multiplier', 2);
        $deductionAmount = ($amount * $percent) / 100;
        $netAmount = $amount - $deductionAmount;
        $withdrawalAmount = $netAmount * $multiplier;

        return [
            'deposit_amount' => $amount,
            'deduction_percent' => $percent,
            'deduction_amount' => $deductionAmount,
            'net_amount' => $netAmount,
            'withdrawal_amount' => $withdrawalAmount,
        ];
    }

    public function create(User $user, float $amount, ?string $paymentProof = null): Deposit
    {
        $calc = $this->calculate($amount);

        return Deposit::create([
            ...$calc,
            'user_id' => $user->id,
            'payment_proof' => $paymentProof,
            'status' => 'pending',
        ]);
    }

    public function approve(Deposit $deposit, int $adminId, ?string $note = null): Deposit
    {
        $deposit->update([
            'status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
            'admin_note' => $note,
        ]);

        // Credit wallet
        $deposit->user->increment('wallet_balance', $deposit->withdrawal_amount);

        return $deposit->fresh();
    }

    public function reject(Deposit $deposit, int $adminId, string $note): Deposit
    {
        $deposit->update([
            'status' => 'rejected',
            'approved_by' => $adminId,
            'approved_at' => now(),
            'admin_note' => $note,
        ]);

        return $deposit->fresh();
    }

    public function createRazorpayOrder(User $user, float $amount, RazorpayService $razorpay): array
    {
        $calc = $this->calculate($amount);
        $amountPaise = (int) round($amount * 100);

        $deposit = Deposit::create([
            ...$calc,
            'user_id' => $user->id,
            'payment_method' => 'razorpay',
            'status' => 'pending',
        ]);

        try {
            $order = $razorpay->createOrder($amountPaise, $deposit->txn_id, [
                'deposit_id' => (string) $deposit->id,
                'user_id' => (string) $user->id,
                'customer_id' => $user->customer_id,
            ]);
        } catch (\Throwable $e) {
            $deposit->delete();
            throw $e;
        }

        $deposit->update(['razorpay_order_id' => $order['id']]);

        return [
            'deposit' => $deposit->fresh(),
            'checkout' => [
                'key_id' => $razorpay->keyId(),
                'order_id' => $order['id'],
                'amount' => $amountPaise,
                'currency' => 'INR',
                'name' => 'Trading Accelerator',
                'description' => 'Trading Accelerator · Deposit ' . $deposit->txn_id,
                'prefill' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'contact' => $user->phone ? preg_replace('/\D+/', '', $user->phone) : '',
                ],
                'theme' => ['color' => '#0F766E'],
            ],
        ];
    }

    public function settleVerifiedPayment(Deposit $deposit, string $paymentId, ?string $signature, array $payment): Deposit
    {
        return DB::transaction(function () use ($deposit, $paymentId, $signature, $payment) {
            $locked = Deposit::whereKey($deposit->id)->lockForUpdate()->first();

            if (!$locked) {
                throw ValidationException::withMessages(['deposit' => 'Deposit not found.']);
            }

            if ($locked->status === 'approved') {
                return $locked;
            }

            if ($locked->status !== 'pending') {
                throw ValidationException::withMessages(['deposit' => 'This deposit can no longer be paid.']);
            }

            $expectedPaise = (int) round(((float) $locked->deposit_amount) * 100);
            $paidPaise = (int) ($payment['amount'] ?? 0);

            if ($paidPaise !== $expectedPaise) {
                throw ValidationException::withMessages(['amount' => 'Paid amount does not match this deposit.']);
            }

            if (($payment['order_id'] ?? '') !== $locked->razorpay_order_id) {
                throw ValidationException::withMessages(['order' => 'Razorpay order does not match this deposit.']);
            }

            if (($payment['status'] ?? '') !== 'captured') {
                throw ValidationException::withMessages(['payment' => 'Payment is not captured yet.']);
            }

            $locked->update([
                'status' => 'approved',
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature,
                'approved_at' => now(),
                'admin_note' => 'Paid via Razorpay ' . $paymentId,
            ]);

            $locked->user()->increment('wallet_balance', $locked->withdrawal_amount);

            return $locked->fresh();
        });
    }

    public function markPaymentFailed(Deposit $deposit, string $reason): Deposit
    {
        if ($deposit->status !== 'pending' || $deposit->razorpay_payment_id) {
            return $deposit;
        }

        $deposit->update([
            'status' => 'failed',
            'admin_note' => $reason,
        ]);

        return $deposit->fresh();
    }
}
