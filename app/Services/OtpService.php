<?php

namespace App\Services;

use InvalidArgumentException;
use RuntimeException;

/**
 * Phone OTP via 2factor.in SMS (replaces email OTP for app auth / withdrawals).
 */
class OtpService
{
    public function __construct(private TwoFactorSmsService $twoFactor) {}

    public function generateForPhone(string $phone): void
    {
        $this->twoFactor->sendOtp($phone);
    }

    public function verifyForPhone(string $phone, string $otp): bool
    {
        try {
            return $this->twoFactor->verifyOtp($phone, $otp);
        } catch (InvalidArgumentException|RuntimeException $e) {
            return false;
        }
    }

    public function normalizePhone(string $phone): string
    {
        return $this->twoFactor->toTenDigit($phone);
    }
}
