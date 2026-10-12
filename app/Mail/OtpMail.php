<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $otp,
        public readonly string $type = 'email_verification'
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->type) {
            'email_verification' => 'Verify Your Email - Trading Accelerator',
            'withdrawal' => 'Withdrawal OTP - Trading Accelerator',
            default => 'Your OTP - Trading Accelerator',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.otp', with: [
            'otp' => $this->otp,
            'type' => $this->type,
        ]);
    }
}
