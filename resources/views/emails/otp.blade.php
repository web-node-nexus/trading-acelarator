<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background: #070B14; margin: 0; padding: 40px 20px; }
        .card { background: #0E1626; max-width: 480px; margin: 0 auto; border-radius: 22px; overflow: hidden; border: 1px solid rgba(255,255,255,0.08); }
        .header { background: linear-gradient(135deg, #0F766E, #D4AF37); padding: 28px; text-align: center; }
        .header img { width: 72px; height: 72px; border-radius: 16px; object-fit: cover; margin-bottom: 12px; }
        .header h1 { color: #fff; font-size: 20px; font-weight: 800; margin: 0; letter-spacing: -0.02em; }
        .header p { color: rgba(255,255,255,0.9); font-size: 12px; margin: 6px 0 0; letter-spacing: 0.08em; text-transform: uppercase; }
        .body { padding: 36px; }
        .otp-box { background: #070B14; border: 2px solid #14B8A6; border-radius: 14px; text-align: center; padding: 24px; margin: 24px 0; }
        .otp { font-size: 40px; font-weight: 800; letter-spacing: 12px; color: #14B8A6; font-family: monospace; }
        .label { color: #8B9BB4; font-size: 13px; margin-bottom: 8px; }
        p { color: #8B9BB4; font-size: 14px; line-height: 1.6; }
        .footer { color: #475569; font-size: 12px; text-align: center; padding: 20px 36px; border-top: 1px solid rgba(255,255,255,0.06); }
        .warning { background: rgba(212,175,55,0.1); border: 1px solid rgba(212,175,55,0.35); border-radius: 10px; padding: 12px 16px; color: #D4AF37; font-size: 13px; margin-top: 16px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <img src="{{ asset('images/logo.jpg') }}" alt="Trading Accelerator">
            <h1>Trading Accelerator</h1>
            <p>15 Din Me Paisa Double</p>
        </div>
        <div class="body">
            <p>Hello,</p>
            <p>
                @if($type === 'email_verification')
                    Your email verification OTP for Trading Accelerator is:
                @else
                    Your withdrawal OTP for Trading Accelerator is:
                @endif
            </p>

            <div class="otp-box">
                <div class="label">Your OTP Code</div>
                <div class="otp">{{ $otp }}</div>
            </div>

            <div class="warning">
                This OTP expires in <strong>10 minutes</strong>. Do not share it with anyone.
            </div>

            <p style="margin-top:16px">If you didn't request this OTP, please ignore this email or contact support immediately.</p>
        </div>
        <div class="footer">
            © {{ date('Y') }} Trading Accelerator · 15 Din Me Paisa Double
        </div>
    </div>
</body>
</html>
