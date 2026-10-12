@extends('admin.layouts.app')
@section('title', 'Withdrawal Details')
@section('page-title', $withdrawal->txn_id)
@section('breadcrumb', "Admin / Withdrawals / {$withdrawal->txn_id}")

@section('content')
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
    <div class="card">
        <div class="card-header"><h3>Withdrawal Info</h3></div>
        <div class="card-body">
            @foreach([
                ['TXN ID', $withdrawal->txn_id],
                ['Customer', $withdrawal->user->name . ' (' . $withdrawal->user->customer_id . ')'],
                ['Amount', '₹' . number_format($withdrawal->amount, 2)],
                ['Bank', $withdrawal->bank_name ?? '-'],
                ['Account holder', $withdrawal->account_holder_name ?: '-'],
                ['Account number', $withdrawal->account_number ?: '-'],
                ['IFSC', $withdrawal->ifsc_code ?? '-'],
                ['UPI ID', $withdrawal->upi_id ?? '-'],
                ['OTP Verified', $withdrawal->otp_verified],
                ['Status', $withdrawal->status],
                ['Requested', $withdrawal->created_at->format('d M Y, h:i A')],
            ] as [$label, $value])
            <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border)">
                <span style="color:var(--text-muted);font-size:13px">{{ $label }}</span>
                <span style="font-weight:600">{{ $value }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3>Actions</h3></div>
        <div class="card-body" style="display:grid;gap:12px">
            @if($withdrawal->status === 'pending')
            <form method="POST" action="{{ route('admin.withdrawals.approve', $withdrawal) }}">
                @csrf
                <textarea name="note" class="form-control" rows="2" placeholder="Note..." style="margin-bottom:8px"></textarea>
                <button type="submit" class="btn btn-success" style="width:100%">✅ Approve</button>
            </form>
            <form method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal) }}">
                @csrf
                <textarea name="note" class="form-control" rows="2" placeholder="Rejection reason..." required style="margin-bottom:8px"></textarea>
                <button type="submit" class="btn btn-danger" style="width:100%">❌ Reject</button>
            </form>
            @elseif($withdrawal->status === 'approved')
            <form method="POST" action="{{ route('admin.withdrawals.mark-paid', $withdrawal) }}">
                @csrf
                <textarea name="note" class="form-control" rows="2" placeholder="Payment reference..." style="margin-bottom:8px"></textarea>
                <button type="submit" class="btn btn-primary" style="width:100%" onclick="return confirm('Mark as paid and deduct ₹{{ number_format($withdrawal->amount,0) }} from wallet?')">
                    💸 Mark as Paid
                </button>
            </form>
            @else
            <p style="color:var(--text-muted)">Status: {{ ucfirst($withdrawal->status) }}</p>
            @endif
        </div>
    </div>
</div>
@endsection
