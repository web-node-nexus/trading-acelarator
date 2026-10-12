@extends('admin.layouts.app')
@section('title', 'Deposit Details')
@section('page-title', $deposit->txn_id)
@section('breadcrumb', "Admin / Deposits / {$deposit->txn_id}")

@section('content')
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
    <div class="card">
        <div class="card-header"><h3>Deposit Info</h3></div>
        <div class="card-body">
            @foreach([
                ['TXN ID', $deposit->txn_id],
                ['Customer', $deposit->user->name . ' (' . $deposit->user->customer_id . ')'],
                ['Deposit Amount', '₹' . number_format($deposit->deposit_amount, 2)],
                ['Deduction (20%)', '-₹' . number_format($deposit->deduction_amount, 2)],
                ['Net Amount', '₹' . number_format($deposit->net_amount, 2)],
                ['Payout (2x)', '₹' . number_format($deposit->withdrawal_amount, 2)],
                ['Payment Method', $deposit->payment_method ?? 'manual'],
                ['Razorpay Order', $deposit->razorpay_order_id ?? '-'],
                ['Razorpay Payment', $deposit->razorpay_payment_id ?? '-'],
                ['Status', $deposit->status],
                ['Submitted', $deposit->created_at->format('d M Y, h:i A')],
            ] as [$label, $value])
            <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border)">
                <span style="color:var(--text-muted);font-size:13px">{{ $label }}</span>
                <span style="font-weight:600">{{ $value }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div>
        @if($deposit->payment_proof)
        <div class="card" style="margin-bottom:16px">
            <div class="card-header"><h3>Payment Proof</h3></div>
            <div class="card-body">
                <img src="{{ asset('storage/'.$deposit->payment_proof) }}" style="width:100%;border-radius:10px">
            </div>
        </div>
        @endif

        @if($deposit->status === 'pending')
        <div class="card">
            <div class="card-header"><h3>Take Action</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.deposits.approve', $deposit) }}" style="margin-bottom:12px">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Note (optional)</label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success" style="width:100%">✅ Approve Deposit</button>
                </form>
                <form method="POST" action="{{ route('admin.deposits.reject', $deposit) }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Rejection Reason</label>
                        <textarea name="note" class="form-control" rows="2" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger" style="width:100%">❌ Reject</button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
