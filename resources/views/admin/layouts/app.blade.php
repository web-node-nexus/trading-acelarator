<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Trading Accelerator Admin')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --primary: #14B8A6;
            --primary-dark: #0F766E;
            --gold: #D4AF37;
            --danger: #F87171;
            --warning: #FBBF24;
            --success: #34D399;
            --dark: #070B14;
            --dark-2: #0E1626;
            --dark-3: #172033;
            --text: #F4F7FB;
            --text-muted: #8B9BB4;
            --border: rgba(255,255,255,0.08);
            --card-bg: rgba(18, 28, 45, 0.86);
            --sidebar-w: 272px;
        }
        body {
            font-family: 'Outfit', sans-serif;
            background:
                radial-gradient(ellipse 70% 40% at 100% 0%, rgba(20,184,166,0.12), transparent 50%),
                radial-gradient(ellipse 50% 35% at 0% 100%, rgba(212,175,55,0.08), transparent 45%),
                var(--dark);
            color: var(--text);
            min-height: 100vh;
            display: flex;
        }
        .sidebar {
            width: var(--sidebar-w);
            min-height: 100vh;
            background: linear-gradient(180deg, rgba(14,22,38,0.98) 0%, rgba(7,11,20,0.98) 100%);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            left: 0; top: 0; bottom: 0;
            z-index: 100;
        }
        .sidebar-logo {
            padding: 22px 18px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-logo img {
            width: 48px; height: 48px;
            border-radius: 14px;
            object-fit: cover;
            box-shadow: 0 8px 24px rgba(20,184,166,0.25);
        }
        .sidebar-logo h1 {
            font-size: 15px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #fff;
            line-height: 1.2;
        }
        .sidebar-logo span {
            font-size: 10px;
            color: var(--gold);
            display: block;
            margin-top: 3px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .sidebar-nav { padding: 16px 12px; flex: 1; overflow-y: auto; }
        .nav-section-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: var(--text-muted);
            padding: 10px 12px 6px;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 12px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            margin: 3px 0;
            transition: 0.25s ease;
        }
        .nav-item:hover { background: rgba(20,184,166,0.1); color: var(--text); }
        .nav-item.active {
            background: linear-gradient(135deg, rgba(20,184,166,0.22), rgba(212,175,55,0.12));
            color: #fff;
            border: 1px solid rgba(20,184,166,0.25);
        }
        .nav-item.active i { color: var(--primary); }
        .nav-item i { width: 18px; text-align: center; font-size: 15px; }
        .nav-badge {
            margin-left: auto;
            background: var(--danger);
            color: #fff;
            font-size: 10px;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 999px;
        }
        .sidebar-user {
            padding: 16px;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-user .avatar {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--primary), var(--gold));
            border-radius: 12px;
            display: grid; place-items: center;
            font-size: 14px; font-weight: 800;
            color: #04110F;
        }
        .sidebar-user .info { flex: 1; min-width: 0; }
        .sidebar-user .info .name { font-size: 13px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sidebar-user .info .role { font-size: 11px; color: var(--text-muted); }

        .main { margin-left: var(--sidebar-w); flex: 1; min-height: 100vh; display: flex; flex-direction: column; }
        .topbar {
            padding: 18px 28px;
            border-bottom: 1px solid var(--border);
            background: rgba(7,11,20,0.8);
            backdrop-filter: blur(16px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky; top: 0; z-index: 50;
        }
        .topbar h2 { font-size: 22px; font-weight: 800; letter-spacing: -0.03em; }
        .topbar .breadcrumb { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
        .content { padding: 28px; flex: 1; }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            backdrop-filter: blur(14px);
            box-shadow: 0 10px 40px rgba(0,0,0,0.18);
        }
        .card-header {
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .card-header h3 { font-size: 15px; font-weight: 700; }
        .card-body { padding: 22px; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: 0.25s ease;
        }
        .stat-card:hover { transform: translateY(-2px); border-color: rgba(20,184,166,0.28); }
        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 14px;
            display: grid; place-items: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .stat-card .value { font-size: 22px; font-weight: 800; letter-spacing: -0.03em; }
        .stat-card .label { font-size: 12px; color: var(--text-muted); margin-top: 2px; font-weight: 500; }

        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
            text-align: left;
            white-space: nowrap;
        }
        tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
            vertical-align: middle;
        }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: rgba(20,184,166,0.04); }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: capitalize;
        }
        .badge-pending { background: rgba(251,191,36,0.14); color: #FBBF24; }
        .badge-approved { background: rgba(52,211,153,0.14); color: #34D399; }
        .badge-rejected { background: rgba(248,113,113,0.14); color: #F87171; }
        .badge-failed { background: rgba(148,163,184,0.14); color: #94A3B8; }
        .badge-paid { background: rgba(20,184,166,0.14); color: #2DD4BF; }
        .badge-active { background: rgba(52,211,153,0.14); color: #34D399; }
        .badge-inactive { background: rgba(248,113,113,0.14); color: #F87171; }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: 0.25s ease;
            font-family: inherit;
        }
        .btn-primary { background: linear-gradient(135deg, #14B8A6, #0F766E); color: #fff; }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 10px 24px rgba(20,184,166,0.28); }
        .btn-success { background: rgba(52,211,153,0.14); color: var(--success); border: 1px solid rgba(52,211,153,0.28); }
        .btn-success:hover { background: var(--success); color: #04110F; }
        .btn-danger { background: rgba(248,113,113,0.14); color: var(--danger); border: 1px solid rgba(248,113,113,0.28); }
        .btn-danger:hover { background: var(--danger); color: #fff; }
        .btn-warning { background: rgba(251,191,36,0.14); color: var(--warning); border: 1px solid rgba(251,191,36,0.28); }
        .btn-warning:hover { background: var(--warning); color: #04110F; }
        .btn-sm { padding: 6px 10px; font-size: 12px; }
        .btn-outline { background: transparent; border: 1px solid var(--border); color: var(--text-muted); }
        .btn-outline:hover { border-color: var(--primary); color: var(--primary); }

        .alert { padding: 14px 16px; border-radius: 14px; margin-bottom: 18px; font-size: 14px; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: rgba(52,211,153,0.12); border: 1px solid rgba(52,211,153,0.25); color: var(--success); }
        .alert-error { background: rgba(248,113,113,0.12); border: 1px solid rgba(248,113,113,0.25); color: var(--danger); }

        .form-group { margin-bottom: 16px; }
        .form-label { font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 6px; display: block; text-transform: uppercase; letter-spacing: 0.06em; }
        .form-control {
            width: 100%;
            padding: 11px 14px;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text);
            font-size: 14px;
            font-family: inherit;
            transition: 0.25s ease;
        }
        .form-control:focus { outline: none; border-color: rgba(20,184,166,0.7); box-shadow: 0 0 0 4px rgba(20,184,166,0.12); }

        .pagination { display: flex; gap: 6px; flex-wrap: wrap; padding: 16px 22px; border-top: 1px solid var(--border); }
        .pagination a, .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px; height: 34px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid var(--border);
            color: var(--text-muted);
            padding: 0 8px;
            transition: 0.25s ease;
        }
        .pagination a:hover { border-color: var(--primary); color: var(--primary); }
        .pagination .active { background: var(--primary); border-color: var(--primary); color: #04110F; }

        .filters { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 18px; }
        .filters form { display: flex; gap: 10px; flex-wrap: wrap; width: 100%; }

        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.72); z-index:200; place-items:center; padding: 16px; }
        .modal-overlay.show { display:grid; }
        .modal { background: var(--dark-2); border: 1px solid var(--border); border-radius: 22px; padding: 28px; min-width: 360px; max-width: 500px; width: 100%; }
        .modal h3 { font-size: 18px; font-weight: 800; margin-bottom: 16px; letter-spacing: -0.02em; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-logo">
            <img src="{{ asset('images/logo.jpg') }}" alt="Trading Accelerator">
            <div>
                <h1>Trading Accelerator</h1>
                <span>15 Din Me Paisa Double</span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section-label">Overview</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a>

            <div class="nav-section-label">Management</div>
            <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i> Users
            </a>
            <a href="{{ route('admin.deposits.index') }}" class="nav-item {{ request()->routeIs('admin.deposits.*') ? 'active' : '' }}">
                <i class="fas fa-arrow-down"></i> Deposits
                @php $pendingDeposits = \App\Models\Deposit::where('status','pending')->count() @endphp
                @if($pendingDeposits > 0)
                    <span class="nav-badge">{{ $pendingDeposits }}</span>
                @endif
            </a>
            <a href="{{ route('admin.withdrawals.index') }}" class="nav-item {{ request()->routeIs('admin.withdrawals.*') ? 'active' : '' }}">
                <i class="fas fa-arrow-up"></i> Withdrawals
                @php $pendingW = \App\Models\Withdrawal::whereIn('status', ['pending', 'approved'])->count() @endphp
                @if($pendingW > 0)
                    <span class="nav-badge">{{ $pendingW }}</span>
                @endif
            </a>
            <a href="{{ route('admin.otps.index') }}" class="nav-item {{ request()->routeIs('admin.otps.*') ? 'active' : '' }}">
                <i class="fas fa-shield-halved"></i> OTP logs
            </a>

            <div class="nav-section-label">Finance</div>
            <a href="{{ route('admin.reports.index') }}" class="nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <i class="fas fa-file-lines"></i> Reports
            </a>
            <a href="{{ route('admin.settings.edit') }}" class="nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <i class="fas fa-sliders"></i> Payout rules
            </a>
        </nav>
        <div class="sidebar-user">
            <div class="avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
            <div class="info">
                <div class="name">{{ auth()->user()->name }}</div>
                <div class="role">Administrator</div>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" style="background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:15px" title="Logout">
                    <i class="fas fa-right-from-bracket"></i>
                </button>
            </form>
        </div>
    </aside>

    <div class="main">
        <div class="topbar">
            <div>
                <h2>@yield('page-title', 'Dashboard')</h2>
                <div class="breadcrumb">@yield('breadcrumb', 'Admin / Dashboard')</div>
            </div>
            <div style="display:flex;align-items:center;gap:12px">
                <span style="font-size:12px;color:var(--text-muted);font-weight:600">{{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>

        <div class="content">
            @if(session('success'))
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
            @endif

            @yield('content')
        </div>
    </div>
</body>
</html>
