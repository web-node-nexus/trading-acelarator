@extends('admin.layouts.app')
@section('title', 'Reports')
@section('page-title', 'Reports')
@section('breadcrumb', 'Admin / Reports')

@section('content')
<div class="card" style="margin-bottom:20px">
    <div class="card-body">
        <form method="GET" class="filters">
            <input type="date" name="from" class="form-control" value="{{ $from->format('Y-m-d') }}" style="max-width:180px">
            <input type="date" name="to" class="form-control" value="{{ $to->format('Y-m-d') }}" style="max-width:180px">
            <button class="btn btn-primary" type="submit"><i class="fas fa-filter"></i> Apply</button>
            <a class="btn btn-outline" href="{{ route('admin.reports.export', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}"><i class="fas fa-download"></i> Export CSV</a>
        </form>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-icon" style="background:rgba(20,184,166,0.15)"><i class="fas fa-user-plus" style="color:#14B8A6"></i></div><div><div class="value">{{ $summary['new_users'] }}</div><div class="label">New customers</div></div></div>
    <div class="stat-card"><div class="stat-icon" style="background:rgba(16,185,129,0.15)"><i class="fas fa-arrow-down" style="color:#10B981"></i></div><div><div class="value">₹{{ number_format($summary['approved_deposit_amount'], 0) }}</div><div class="label">Approved deposits</div></div></div>
    <div class="stat-card"><div class="stat-icon" style="background:rgba(45,212,191,0.15)"><i class="fas fa-wallet" style="color:#2DD4BF"></i></div><div><div class="value">₹{{ number_format($summary['payout_credited'], 0) }}</div><div class="label">Payout credited</div></div></div>
    <div class="stat-card"><div class="stat-icon" style="background:rgba(239,68,68,0.15)"><i class="fas fa-arrow-up" style="color:#EF4444"></i></div><div><div class="value">₹{{ number_format($summary['paid_out'], 0) }}</div><div class="label">Paid to customers</div></div></div>
    <div class="stat-card"><div class="stat-icon" style="background:rgba(245,158,11,0.15)"><i class="fas fa-clock" style="color:#F59E0B"></i></div><div><div class="value">₹{{ number_format($summary['pending_deposit_amount'], 0) }}</div><div class="label">Deposits still pending</div></div></div>
    <div class="stat-card"><div class="stat-icon" style="background:rgba(239,68,68,0.15)"><i class="fas fa-hourglass" style="color:#EF4444"></i></div><div><div class="value">₹{{ number_format($summary['withdrawal_pending'], 0) }}</div><div class="label">Withdrawals not paid</div></div></div>
</div>

<div class="card">
    <div class="card-header"><h3>Deposits in this range ({{ $summary['deposit_count'] }})</h3></div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>TXN</th><th>Customer</th><th>Deposit</th><th>Payout</th><th>Method</th><th>Status</th><th>Date</th></tr>
            </thead>
            <tbody>
                @forelse($rows as $deposit)
                <tr>
                    <td style="font-family:monospace;font-size:12px"><a href="{{ route('admin.deposits.show', $deposit) }}" style="color:#14B8A6">{{ $deposit->txn_id }}</a></td>
                    <td>{{ $deposit->user->name }}<div style="font-size:11px;color:#94A3B8">{{ $deposit->user->customer_id }}</div></td>
                    <td>₹{{ number_format($deposit->deposit_amount, 0) }}</td>
                    <td style="color:#2DD4BF;font-weight:700">₹{{ number_format($deposit->withdrawal_amount, 0) }}</td>
                    <td>{{ $deposit->payment_method }}</td>
                    <td><span class="badge badge-{{ $deposit->status }}">{{ $deposit->status }}</span></td>
                    <td style="font-size:12px;color:#94A3B8">{{ $deposit->created_at->format('d M Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;color:#94A3B8;padding:30px">No deposits in this range</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $rows->links() }}</div>
</div>
@endsection
