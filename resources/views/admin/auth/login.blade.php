<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login · Trading Accelerator</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            color: #F4F7FB;
            display: grid;
            place-items: center;
            padding: 24px;
            background:
                radial-gradient(ellipse 80% 50% at 15% 20%, rgba(20,184,166,0.22), transparent 55%),
                radial-gradient(ellipse 60% 45% at 90% 80%, rgba(212,175,55,0.16), transparent 50%),
                linear-gradient(160deg, #070B14 0%, #0A1A18 45%, #070B14 100%);
        }
        .shell {
            width: 100%;
            max-width: 980px;
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            gap: 0;
            border-radius: 28px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(14, 22, 38, 0.72);
            backdrop-filter: blur(22px);
            box-shadow: 0 30px 80px rgba(0,0,0,0.45);
        }
        .hero {
            padding: 48px 40px;
            background:
                linear-gradient(160deg, rgba(15,118,110,0.35), rgba(7,11,20,0.2)),
                url('{{ asset('images/logo.jpg') }}') center/cover;
            position: relative;
            min-height: 520px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }
        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(7,11,20,0.25) 0%, rgba(7,11,20,0.92) 85%);
        }
        .hero-content { position: relative; z-index: 1; }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(212,175,55,0.16);
            border: 1px solid rgba(212,175,55,0.35);
            color: #F5E6A8;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 18px;
        }
        .hero h1 {
            font-size: clamp(28px, 4vw, 40px);
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.03em;
            margin-bottom: 10px;
        }
        .hero p {
            color: #B7C4D8;
            font-size: 15px;
            line-height: 1.55;
            max-width: 320px;
        }
        .panel { padding: 48px 40px; display: flex; flex-direction: column; justify-content: center; }
        .brand-mark {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
        }
        .brand-mark img {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            object-fit: cover;
            box-shadow: 0 10px 30px rgba(20,184,166,0.25);
        }
        .brand-mark strong {
            display: block;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .brand-mark span {
            color: #D4AF37;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .panel h2 {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin-bottom: 6px;
        }
        .panel .lead {
            color: #8B9BB4;
            margin-bottom: 28px;
            font-size: 14px;
        }
        .form-group { margin-bottom: 16px; }
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #8B9BB4;
            margin-bottom: 8px;
        }
        .input-wrap { position: relative; }
        .input-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #6B7C96;
        }
        .form-control {
            width: 100%;
            padding: 14px 14px 14px 42px;
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.04);
            color: #F4F7FB;
            font-size: 15px;
            font-family: inherit;
            transition: 0.25s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: rgba(20,184,166,0.7);
            box-shadow: 0 0 0 4px rgba(20,184,166,0.15);
            background: rgba(20,184,166,0.05);
        }
        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #8B9BB4;
            font-size: 13px;
            margin: 8px 0 22px;
            cursor: pointer;
        }
        .btn-submit {
            width: 100%;
            border: none;
            border-radius: 14px;
            padding: 15px 18px;
            font-family: inherit;
            font-size: 15px;
            font-weight: 800;
            color: #04110F;
            cursor: pointer;
            background: linear-gradient(135deg, #2DD4BF, #D4AF37);
            transition: 0.25s ease;
        }
        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 34px rgba(20,184,166,0.28);
        }
        .alert {
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 13px;
            background: rgba(248,113,113,0.12);
            border: 1px solid rgba(248,113,113,0.28);
            color: #FCA5A5;
        }
        @media (max-width: 860px) {
            .shell { grid-template-columns: 1fr; }
            .hero { min-height: 240px; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <section class="hero">
            <div class="hero-content">
                <div class="hero-badge">Admin Console</div>
                <h1>Trading Accelerator</h1>
                <p>15 Din Me Paisa Double — manage customers, deposits, withdrawals and payouts from one modern control room.</p>
            </div>
        </section>
        <section class="panel">
            <div class="brand-mark">
                <img src="{{ asset('images/logo.jpg') }}" alt="Trading Accelerator">
                <div>
                    <strong>Trading Accelerator</strong>
                    <span>15 Din Me Paisa Double</span>
                </div>
            </div>
            <h2>Welcome back</h2>
            <p class="lead">Sign in to continue managing the platform.</p>

            @if($errors->any())
                <div class="alert"><i class="fas fa-circle-exclamation"></i> {{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin.login.post') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" class="form-control" placeholder="admin@cashflow.com" value="{{ old('email') }}" required autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                    </div>
                </div>
                <label class="remember">
                    <input type="checkbox" name="remember"> Remember me
                </label>
                <button type="submit" class="btn-submit">Enter dashboard</button>
            </form>
        </section>
    </div>
</body>
</html>
