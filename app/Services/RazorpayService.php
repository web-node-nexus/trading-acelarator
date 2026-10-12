<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class RazorpayService
{
    private const BASE = 'https://api.razorpay.com/v1';

    public function keyId(): string
    {
        $key = (string) config('razorpay.key_id');
        $secret = (string) config('razorpay.key_secret');

        if ($key === '' || $secret === '') {
            throw new RuntimeException('Razorpay keys are missing. Set RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET in .env.');
        }

        return $key;
    }

    public function createOrder(int $amountPaise, string $receipt, array $notes = []): array
    {
        $response = Http::withBasicAuth($this->keyId(), $this->secret())
            ->acceptJson()
            ->timeout(20)
            ->post(self::BASE . '/orders', [
                'amount' => $amountPaise,
                'currency' => config('razorpay.currency', 'INR'),
                'receipt' => $receipt,
                'notes' => $notes,
            ]);

        if (!$response->successful()) {
            throw new RuntimeException($this->errorMessage($response->json(), 'Could not create Razorpay order.'));
        }

        return $response->json();
    }

    public function fetchPayment(string $paymentId): array
    {
        $response = Http::withBasicAuth($this->keyId(), $this->secret())
            ->acceptJson()
            ->timeout(20)
            ->get(self::BASE . '/payments/' . $paymentId);

        if (!$response->successful()) {
            throw new RuntimeException($this->errorMessage($response->json(), 'Could not fetch Razorpay payment.'));
        }

        return $response->json();
    }

    public function captureIfNeeded(array $payment): array
    {
        $status = $payment['status'] ?? '';

        if ($status === 'captured') {
            return $payment;
        }

        if ($status !== 'authorized') {
            throw new RuntimeException('Payment is not successful. Status: ' . ($status ?: 'unknown'));
        }

        $response = Http::withBasicAuth($this->keyId(), $this->secret())
            ->acceptJson()
            ->timeout(20)
            ->post(self::BASE . '/payments/' . $payment['id'] . '/capture', [
                'amount' => $payment['amount'],
                'currency' => $payment['currency'] ?? 'INR',
            ]);

        if (!$response->successful()) {
            throw new RuntimeException($this->errorMessage($response->json(), 'Could not capture Razorpay payment.'));
        }

        return $response->json();
    }

    public function verifyCheckoutSignature(string $orderId, string $paymentId, string $signature): bool
    {
        $expected = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->secret());

        if (strlen($signature) !== strlen($expected)) {
            return false;
        }

        return hash_equals($expected, $signature);
    }

    public function verifyWebhookSignature(string $payload, ?string $signature): bool
    {
        $secret = (string) config('razorpay.webhook_secret');

        if ($secret === '' || $signature === null || $signature === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $secret);

        if (strlen($signature) !== strlen($expected)) {
            return false;
        }

        return hash_equals($expected, $signature);
    }

    private function secret(): string
    {
        return (string) config('razorpay.key_secret');
    }

    private function errorMessage(?array $body, string $fallback): string
    {
        return $body['error']['description'] ?? $fallback;
    }
}
