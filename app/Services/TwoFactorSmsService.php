<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

class TwoFactorSmsService
{
    public function sendOtp(string $phone): array
    {
        $phone10 = $this->toTenDigit($phone);
        $apiKey = config('services.twofactor.api_key');
        $template = config('services.twofactor.sms_template', 'OTP1');
        $base = rtrim((string) config('services.twofactor.base_url'), '/');

        if (!$apiKey) {
            throw new RuntimeException('TWOFACTOR_API_KEY is not configured.');
        }

        $url = "{$base}/{$apiKey}/SMS/{$phone10}/AUTOGEN/{$template}";

        $response = Http::acceptJson()->timeout(20)->get($url);
        $body = $response->json() ?? [];

        Log::info('2factor send OTP', [
            'phone' => $phone10,
            'http' => $response->status(),
            'body' => $body,
        ]);

        if ($this->isSuccess($response->status(), $body)) {
            return $body;
        }

        $msg = $body['Details'] ?? $body['details'] ?? $body['message'] ?? 'Failed to send OTP SMS.';
        // Don't surface raw session UUIDs as errors if Status parsing failed oddly.
        if (is_string($msg) && preg_match('/^[a-f0-9-]{36}$/i', $msg)) {
            // Treat as success: 2factor returns UUID session id in Details on success.
            return $body + ['Status' => 'Success'];
        }

        throw new RuntimeException(is_string($msg) ? $msg : 'Failed to send OTP SMS.');
    }

    public function verifyOtp(string $phone, string $otp): bool
    {
        $phone91 = $this->toNinetyOne($phone);
        $apiKey = config('services.twofactor.api_key');
        $base = rtrim((string) config('services.twofactor.base_url'), '/');

        if (!$apiKey) {
            throw new RuntimeException('TWOFACTOR_API_KEY is not configured.');
        }

        $url = "{$base}/{$apiKey}/SMS/VERIFY3/{$phone91}/{$otp}";

        $response = Http::acceptJson()->timeout(20)->get($url);
        $body = $response->json() ?? [];

        Log::info('2factor verify OTP', [
            'phone' => $phone91,
            'http' => $response->status(),
            'body' => $body,
        ]);

        return $this->isSuccess($response->status(), $body);
    }

    private function isSuccess(int $httpStatus, array $body): bool
    {
        if ($httpStatus < 200 || $httpStatus >= 300) {
            return false;
        }

        $status = strtoupper((string) ($body['Status'] ?? $body['status'] ?? ''));
        if ($status === 'SUCCESS') {
            return true;
        }

        // Fallback: successful AUTOGEN responses put a session UUID in Details.
        $details = $body['Details'] ?? $body['details'] ?? null;
        if (is_string($details) && preg_match('/^[a-f0-9-]{36}$/i', $details)) {
            return true;
        }

        return false;
    }

    public function toTenDigit(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($digits, '91') && strlen($digits) === 12) {
            $digits = substr($digits, 2);
        }
        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }
        if (strlen($digits) !== 10) {
            throw new InvalidArgumentException('Enter a valid 10-digit Indian mobile number.');
        }

        return $digits;
    }

    public function toNinetyOne(string $phone): string
    {
        return '91' . $this->toTenDigit($phone);
    }
}
