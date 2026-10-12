<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Withdrawal;
use App\Services\OtpService;
use App\Services\WithdrawalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function __construct(
        private WithdrawalService $withdrawalService,
        private OtpService $otpService
    ) {}

    public function requestOtp(Request $request): JsonResponse
    {
        $this->otpService->generate($request->user()->email, 'withdrawal');
        return response()->json(['success' => true, 'message' => 'Withdrawal OTP sent to your email.']);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'deposit_id' => 'required|exists:deposits,id',
            'otp' => 'required|string|size:6',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:30',
            'ifsc_code' => 'nullable|string|max:20',
            'account_holder_name' => 'nullable|string|max:100',
            'upi_id' => 'nullable|string|max:100',
        ]);

        // Verify OTP
        $verified = $this->otpService->verify($request->user()->email, $request->otp, 'withdrawal');
        if (!$verified) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired OTP.'], 422);
        }

        $deposit = Deposit::findOrFail($request->deposit_id);

        if ($deposit->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $withdrawal = $this->withdrawalService->request($request->user(), $deposit, $request->only([
            'bank_name', 'account_number', 'ifsc_code', 'account_holder_name', 'upi_id',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Withdrawal request submitted. Admin will process it shortly.',
            'data' => $this->formatWithdrawal($withdrawal),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $withdrawals = $request->user()
            ->withdrawals()
            ->with('deposit')
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $withdrawals->through(fn($w) => $this->formatWithdrawal($w)),
        ]);
    }

    private function formatWithdrawal(Withdrawal $w): array
    {
        return [
            'id' => $w->id,
            'txn_id' => $w->txn_id,
            'amount' => (float) $w->amount,
            'bank_name' => $w->bank_name,
            'account_number' => $w->account_number ? '****' . substr($w->account_number, -4) : null,
            'ifsc_code' => $w->ifsc_code,
            'account_holder_name' => $w->account_holder_name,
            'upi_id' => $w->upi_id,
            'status' => $w->status,
            'admin_note' => $w->admin_note,
            'otp_verified' => $w->otp_verified,
            'created_at' => $w->created_at->toIso8601String(),
            'processed_at' => $w->processed_at?->toIso8601String(),
        ];
    }
}
