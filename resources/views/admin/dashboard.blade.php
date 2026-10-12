@extends('admin.layouts.app')
@section('title', 'Dashboard - Trading Accelerator Admin')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Admin / Dashboard')

@section('content')
<div class="card" style="margin-bottom:20px;overflow:hidden">
    <div class="card-body" style="background:linear-gradient(135deg,rgba(20,184,166,0.18),rgba(212,175,55,0.1));display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;align-items:center">
        <div>
            <div style="font-size:12px;color:var(--gold);font-weight:700;letter-spacing:0.12em;text-transform:uppercase">Trading Accelerator</div>
            <div style="font-size:24px;font-weight:800;margin-top:6px;letter-spacing:-0.03em">15 Din Me Paisa Double</div>
            <div style="color:var(--text-muted);font-size:13px;margin-top:6px">Modern control room for customers, deposits, withdrawals and payouts.</div>
        </div>
        <img src="{{ asset('images/logo.jpg') }}" alt="Trading Accelerator" style="width:64px;height:64px;border-radius:16px;object-fit:cover;box-shadow:0 10px 28px rgba(20,184,166,0.28)">
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(20,184,166,0.15)"><i class="fas fa-users" style="color:#14B8A6"></i></div>
        <div>
            <div class="value">{{ number_format($stats['total_users']) }}</div>
            <div class="label">Total Users</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(16,185,129,0.15)"><i class="fas fa-users-check" style="color:#10B981"></i></div>
        <div>
            <div class="value">{{ number_format($stats['active_users']) }}</div>
            <div class="label">Active Users</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(245,158,11,0.15)"><i class="fas fa-clock" style="color:#F59E0B"></i></div>
        <div>
            <div class="value">{{ $stats['pending_deposits'] }}</div>
            <div class="label">Pending Deposits</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(239,68,68,0.15)"><i class="fas fa-hourglass-half" style="color:#EF4444"></i></div>
        <div>
            <div class="value">{{ $stats['pending_withdrawals'] }}</div>
            <div class="label">Pending Withdrawals</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(20,184,166,0.15)"><i class="fas fa-indian-rupee-sign" style="color:#14B8A6"></i></div>
        <div>
            <div class="value">₹{{ number_format($stats['total_deposited'], 0) }}</div>
            <div class="label">Total Deposited</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(45,212,191,0.15)"><i class="fas fa-money-bill-wave" style="color:#2DD4BF"></i></div>
        <div>
            <div class="value">₹{{ number_format($stats['total_paid_out'], 0) }}</div>
            <div class="label">Total Paid Out</div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:20px">
    <div class="card-body" style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;align-items:center">
        <div>
            <div style="font-size:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.6px">Current payout rule</div>
            <div style="font-size:18px;font-weight:800;margin-top:4px">{{ $stats['deduction_percent'] }}% deduction, then × {{ $stats['payout_multiplier'] }}</div>
            <div style="color:var(--text-muted);font-size:13px;margin-top:4px">₹1,000 deposit becomes ₹{{ number_format((1000 - (1000 * $stats['deduction_percent'] / 100)) * $stats['payout_multiplier'], 0) }} in the wallet.</div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="fas fa-user-plus"></i> New customer</a>
            <a href="{{ route('admin.deposits.create') }}" class="btn btn-outline"><i class="fas fa-plus"></i> Manual deposit</a>
            <a href="{{ route('admin.withdrawals.index', ['status' => 'approved']) }}" class="btn btn-outline"><i class="fas fa-money-bill"></i> Ready to pay ({{ $stats['ready_to_pay'] }})</a>
            <a href="{{ route('admin.settings.edit') }}" class="btn btn-outline"><i class="fas fa-sliders"></i> Edit rules</a>
        </div>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(245,158,11,0.15)"><i class="fas fa-envelope" style="color:#F59E0B"></i></div>
        <div>
            <div class="value">{{ $stats['unverified_users'] }}</div>
            <div class="label">Email not verified</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(45,212,191,0.15)"><i class="fas fa-wallet" style="color:#2DD4BF"></i></div>
        <div>
            <div class="value">₹{{ number_format($stats['wallet_liability'], 0) }}</div>
            <div class="label">Total wallet balance</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(239,68,68,0.15)"><i class="fas fa-circle-xmark" style="color:#EF4444"></i></div>
        <div>
            <div class="value">{{ $stats['failed_deposits'] }}</div>
            <div class="label">Failed payments</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(20,184,166,0.15)"><i class="fas fa-hand-holding-dollar" style="color:#14B8A6"></i></div>
        <div>
            <div class="value">₹{{ number_format($stats['total_withdrawal_due'], 0) }}</div>
            <div class="label">Withdrawals waiting</div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-arrow-down-to-line" style="color:#14B8A6;margin-right:8px"></i>Recent Deposits</h3>
            <a href="{{ route('admin.deposits.index') }}" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>TXN ID</th>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Payout</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentDeposits as $deposit)
                    <tr>
                        <td style="font-family:monospace;font-size:12px">{{ $deposit->txn_id }}</td>
                        <td>
                            <div style="font-weight:600;font-size:13px">{{ $deposit->user->name }}</div>
                            <div style="font-size:11px;color:#94A3B8">{{ $deposit->user->customer_id }}</div>
                        </td>
                        <td style="font-weight:600">₹{{ number_format($deposit->deposit_amount, 0) }}</td>
                        <td style="color:#2DD4BF;font-weight:600">₹{{ number_format($deposit->withdrawal_amount, 0) }}</td>
                        <td><span class="badge badge-{{ $deposit->status }}">{{ $deposit->status }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center;color:#94A3B8;padding:30px">No deposits yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-arrow-up-from-line" style="color:#EF4444;margin-right:8px"></i>Recent Withdrawals</h3>
            <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>TXN ID</th>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentWithdrawals as $w)
                    <tr>
                        <td style="font-family:monospace;font-size:12px">{{ $w->txn_id }}</td>
                        <td>
                            <div style="font-weight:600;font-size:13px">{{ $w->user->name }}</div>
                            <div style="font-size:11px;color:#94A3B8">{{ $w->user->customer_id }}</div>
                        </td>
                        <td style="font-weight:600;color:#EF4444">₹{{ number_format($w->amount, 0) }}</td>
                        <td><span class="badge badge-{{ $w->status }}">{{ $w->status }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center;color:#94A3B8;padding:30px">No withdrawals yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
