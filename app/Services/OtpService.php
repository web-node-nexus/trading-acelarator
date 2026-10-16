<?php

namespace App\Services;

use App\Models\Otp;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    public function generate(string $email, string $type = 'email_verification'): string
    {
        // Invalidate old OTPs
        Otp::where('email', $email)->where('type', $type)->where('is_used', false)->update(['is_used' => true]);

        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        Otp::create([
            'email' => $email,
            'otp' => $otp,
            'type' => $type,
            'expires_at' => now()->addMinutes(10),
        ]);

        try {
            Mail::to($email)->send(new OtpMail($otp, $type));
        } catch (\Throwable $e) {
            // Never fail registration/login just because SMTP is down.
            Log::error('OTP mail failed', [
                'email' => $email,
                'type' => $type,
                'otp' => $otp,
                'error' => $e->getMessage(),
            ]);
        }

        return $otp;
    }

    public function verify(string $email, string $otp, string $type = 'email_verification'): bool
    {
        $record = Otp::where('email', $email)
            ->where('otp', $otp)
            ->where('type', $type)
            ->where('is_used', false)
            ->latest()
            ->first();

        if (!$record || !$record->isValid()) {
            return false;
        }

        $record->update(['is_used' => true]);
        return true;
    }
}
