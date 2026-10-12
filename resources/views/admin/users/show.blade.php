@extends('admin.layouts.app')
@section('title', 'User Details - Trading Accelerator Admin')
@section('page-title', $user->name)
@section('breadcrumb', "Admin / Users / {$user->customer_id}")

@section('content')
<div style="display:grid;grid-template-columns:360px 1fr;gap:20px;align-items:start">
    <!-- Profile Card -->
    <div>
        <div class="card" style="margin-bottom:16px">
            <div class="card-body" style="text-align:center">
                <div style="width:80px;height:80px;background:linear-gradient(135deg,#14B8A6,#D4AF37);border-radius:20px;display:grid;place-items:center;font-size:28px;font-weight:800;margin:0 auto 16px;color:#04110F">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <h2 style="font-size:18px;font-weight:700">{{ $user->name }}</h2>
                <p style="color:#94A3B8;font-size:13px;margin-top:4px">{{ $user->email }}</p>
                <div style="margin-top:12px">
                    <span style="font-family:monospace;font-size:13px;background:rgba(20,184,166,0.1);color:#14B8A6;padding:5px 14px;border-radius:8px;font-weight:600">
                        {{ $user->customer_id }}
                    </span>
                </div>
                <span class="badge badge-{{ $user->is_active ? 'active' : 'inactive' }}" style="margin-top:12px">
                    {{ $user->is_active ? 'Active' : 'Suspended' }}
                </span>
            </div>
        </div>

        <div class="card" style="margin-bottom:16px">
            <div class="card-body">
                <div style="display:grid;gap:12px">
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px;background:rgba(255,255,255,0.03);border-radius:10px">
                        <span style="color:#94A3B8;font-size:13px">Wallet Balance</span>
                        <span style="color:#2DD4BF;font-weight:700;font-size:16px">₹{{ number_format($user->wallet_balance, 2) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px;background:rgba(255,255,255,0.03);border-radius:10px">
                        <span style="color:#94A3B8;font-size:13px">Total Deposited</span>
                        <span style="font-weight:600">₹{{ number_format($user->total_deposited, 2) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px;background:rgba(255,255,255,0.03);border-radius:10px">
                        <span style="color:#94A3B8;font-size:13px">Total Withdrawn</span>
                        <span style="font-weight:600;color:#EF4444">₹{{ number_format($user->total_withdrawn, 2) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px;background:rgba(255,255,255,0.03);border-radius:10px">
                        <span style="color:#94A3B8;font-size:13px">Phone</span>
                        <span>{{ $user->phone ?? '-' }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px;background:rgba(255,255,255,0.03);border-radius:10px">
                        <span style="color:#94A3B8;font-size:13px">Joined</span>
                        <span>{{ $user->created_at->format('d M Y') }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px;background:rgba(255,255,255,0.03);border-radius:10px">
                        <span style="color:#94A3B8;font-size:13px">Email Verified</span>
                        <span style="color:{{ $user->is_email_verified ? '#10B981' : '#EF4444' }}">{{ $user->is_email_verified ? 'Yes' : 'No' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div style="display:grid;gap:8px;margin-bottom:16px">
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary"><i class="fas fa-pen"></i> Edit profile</a>
            <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                @csrf
                <button type="submit" class="btn {{ $user->is_active ? 'btn-danger' : 'btn-success' }}" style="width:100%">
                    <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                    {{ $user->is_active ? 'Suspend Account' : 'Activate Account' }}
                </button>
            </form>
        </div>

        <div class="card" style="margin-bottom:16px">
            <div class="card-header"><h3>Adjust wallet</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.wallet', $user) }}">
                    @csrf
                    <div class="form-group">
                        <select name="type" class="form-control">
                            <option value="credit">Credit</option>
                            <option value="debit">Debit</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="number" step="0.01" name="amount" class="form-control" placeholder="Amount" required>
                    </div>
                    <div class="form-group">
                        <input name="reason" class="form-control" placeholder="Reason" required>
                    </div>
                    <button class="btn btn-outline" type="submit" style="width:100%">Update wallet</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Reset password</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                    @csrf
                    <div class="form-group">
                        <input type="password" name="password" class="form-control" placeholder="New password" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm password" required>
                    </div>
                    <button class="btn btn-warning" type="submit" style="width:100%">Reset password</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Transactions -->
    <div>
        <div class="card" style="margin-bottom:20px">
            <div class="card-header"><h3><i class="fas fa-wallet" style="color:#2DD4BF;margin-right:8px"></i>Wallet ledger</h3></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Type</th><th>Amount</th><th>Balance</th><th>Reason</th><th>When</th></tr></thead>
                    <tbody>
                        @forelse($ledger as $entry)
                        <tr>
                            <td><span class="badge badge-{{ $entry->type === 'credit' ? 'approved' : 'rejected' }}">{{ $entry->type }}</span></td>
                            <td>₹{{ number_format($entry->amount, 2) }}</td>
                            <td>₹{{ number_format($entry->balance_after, 2) }}</td>
                            <td>{{ $entry->reason }}</td>
                            <td style="font-size:12px;color:#94A3B8">{{ $entry->created_at->format('d M Y, h:i A') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="text-align:center;color:#94A3B8;padding:20px">No manual wallet changes yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card" style="margin-bottom:20px">
            <div class="card-header">
                <h3><i class="fas fa-arrow-down-to-line" style="color:#14B8A6;margin-right:8px"></i>Deposit History</h3>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>TXN ID</th>
                            <th>Deposit</th>
                            <th>Deduction</th>
                            <th>Payout</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($user->deposits as $d)
                        <tr>
                            <td style="font-family:monospace;font-size:12px">{{ $d->txn_id }}</td>
                            <td style="font-weight:600">₹{{ number_format($d->deposit_amount, 0) }}</td>
                            <td style="color:#F59E0B">-₹{{ number_format($d->deduction_amount, 0) }}</td>
                            <td style="color:#2DD4BF;font-weight:700">₹{{ number_format($d->withdrawal_amount, 0) }}</td>
                            <td><span class="badge badge-{{ $d->status }}">{{ $d->status }}</span></td>
                            <td style="font-size:12px;color:#94A3B8">{{ $d->created_at->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" style="text-align:center;color:#94A3B8;padding:20px">No deposits</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-arrow-up-from-line" style="color:#EF4444;margin-right:8px"></i>Withdrawal History</h3>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>TXN ID</th>
                            <th>Amount</th>
                            <th>Bank/UPI</th>
                            <th>OTP Verified</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($user->withdrawals as $w)
                        <tr>
                            <td style="font-family:monospace;font-size:12px">{{ $w->txn_id }}</td>
                            <td style="font-weight:700;color:#EF4444">₹{{ number_format($w->amount, 0) }}</td>
                            <td style="font-size:12px;color:#94A3B8">{{ $w->bank_name ?? $w->upi_id ?? '-' }}</td>
                            <td>
                                <span style="color:{{ $w->otp_verified === 'yes' ? '#10B981' : '#EF4444' }};font-size:12px;font-weight:600">
                                    {{ $w->otp_verified === 'yes' ? '✓ Yes' : '✗ No' }}
                                </span>
                            </td>
                            <td><span class="badge badge-{{ $w->status }}">{{ $w->status }}</span></td>
                            <td style="font-size:12px;color:#94A3B8">{{ $w->created_at->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" style="text-align:center;color:#94A3B8;padding:20px">No withdrawals</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
