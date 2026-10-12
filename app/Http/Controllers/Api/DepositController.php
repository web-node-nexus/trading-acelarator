<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Setting;
use App\Services\DepositService;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class DepositController extends Controller
{
    public function __construct(
        private DepositService $depositService,
        private RazorpayService $razorpay
    ) {}

    public function preview(Request $request): JsonResponse
    {
        $bounds = Setting::bounds();
        $request->validate(['amount' => 'required|numeric|min:' . $bounds['min'] . '|max:' . $bounds['max']]);
        $calc = $this->depositService->calculate((float) $request->amount);

        return response()->json(['success' => true, 'data' => $calc]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:' . Setting::bounds()['min'] . '|max:' . Setting::bounds()['max'],
            'payment_proof' => 'nullable|image|max:5120',
        ]);

        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
        }

        $deposit = $this->depositService->create($request->user(), (float) $request->amount, $proofPath);

        return response()->json([
            'success' => true,
            'message' => 'Deposit request submitted. Awaiting admin approval.',
            'data' => $this->formatDeposit($deposit),
        ], 201);
    }

    public function createOrder(Request $request): JsonResponse
    {
        $bounds = Setting::bounds();
        $request->validate(['amount' => 'required|numeric|min:' . $bounds['min'] . '|max:' . $bounds['max']]);

        try {
            $result = $this->depositService->createRazorpayOrder(
                $request->user(),
                (float) $request->amount,
                $this->razorpay
            );
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 502);
        }

        return response()->json([
            'success' => true,
            'message' => 'Razorpay order created.',
            'data' => [
                'deposit' => $this->formatDeposit($result['deposit']),
                'checkout' => $result['checkout'],
            ],
        ], 201);
    }

    public function verifyPayment(Request $request): JsonResponse
    {
        $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $deposit = Deposit::where('razorpay_order_id', $request->razorpay_order_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$deposit) {
            return response()->json(['success' => false, 'message' => 'Deposit order not found.'], 404);
        }

        if (!$this->razorpay->verifyCheckoutSignature(
            $request->razorpay_order_id,
            $request->razorpay_payment_id,
            $request->razorpay_signature
        )) {
            throw ValidationException::withMessages(['signature' => 'Payment signature is invalid.']);
        }

        try {
            $payment = $this->razorpay->captureIfNeeded(
                $this->razorpay->fetchPayment($request->razorpay_payment_id)
            );
            $deposit = $this->depositService->settleVerifiedPayment(
                $deposit,
                $request->razorpay_payment_id,
                $request->razorpay_signature,
                $payment
            );
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment verified. Wallet credited.',
            'data' => $this->formatDeposit($deposit),
        ]);
    }

    public function paymentFailed(Request $request): JsonResponse
    {
        $request->validate([
            'razorpay_order_id' => 'required|string',
            'reason' => 'nullable|string|max:255',
        ]);

        $deposit = Deposit::where('razorpay_order_id', $request->razorpay_order_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$deposit) {
            return response()->json(['success' => false, 'message' => 'Deposit order not found.'], 404);
        }

        $deposit = $this->depositService->markPaymentFailed(
            $deposit,
            $request->input('reason', 'Payment failed')
        );

        return response()->json([
            'success' => true,
            'message' => 'Payment marked as failed.',
            'data' => $this->formatDeposit($deposit),
        ]);
    }

    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');
        $webhookSecret = (string) config('razorpay.webhook_secret');

        if ($webhookSecret !== '' && !$this->razorpay->verifyWebhookSignature($payload, $signature)) {
            return response()->json(['success' => false, 'message' => 'Invalid webhook signature.'], 400);
        }

        $event = $request->input('event');
        $payment = $request->input('payload.payment.entity');

        if ($event !== 'payment.captured' || !is_array($payment) || empty($payment['id'])) {
            return response()->json(['success' => true, 'message' => 'Ignored.']);
        }

        $deposit = Deposit::where('razorpay_order_id', $payment['order_id'] ?? '')->first();

        if (!$deposit) {
            return response()->json(['success' => true, 'message' => 'Order not tracked.']);
        }

        try {
            $fresh = $this->razorpay->captureIfNeeded($this->razorpay->fetchPayment($payment['id']));
            $this->depositService->settleVerifiedPayment($deposit, $payment['id'], null, $fresh);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true]);
    }

    public function index(Request $request): JsonResponse
    {
        $deposits = $request->user()
            ->deposits()
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $deposits->through(fn($d) => $this->formatDeposit($d)),
        ]);
    }

    public function show(Request $request, Deposit $deposit): JsonResponse
    {
        if ($deposit->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        return response()->json(['success' => true, 'data' => $this->formatDeposit($deposit)]);
    }

    private function formatDeposit(Deposit $d): array
    {
        return [
            'id' => $d->id,
            'txn_id' => $d->txn_id,
            'deposit_amount' => (float) $d->deposit_amount,
            'deduction_percent' => (float) $d->deduction_percent,
            'deduction_amount' => (float) $d->deduction_amount,
            'net_amount' => (float) $d->net_amount,
            'withdrawal_amount' => (float) $d->withdrawal_amount,
            'payment_proof' => $d->payment_proof ? asset('storage/' . $d->payment_proof) : null,
            'payment_method' => $d->payment_method,
            'razorpay_order_id' => $d->razorpay_order_id,
            'razorpay_payment_id' => $d->razorpay_payment_id,
            'status' => $d->status,
            'admin_note' => $d->admin_note,
            'created_at' => $d->created_at->toIso8601String(),
            'approved_at' => $d->approved_at?->toIso8601String(),
            'has_withdrawal' => $d->withdrawal()->exists(),
        ];
    }
}
