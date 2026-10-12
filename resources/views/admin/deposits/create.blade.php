@extends('admin.layouts.app')
@section('title', 'Manual deposit')
@section('page-title', 'Manual deposit')
@section('breadcrumb', 'Admin / Deposits / New')

@section('content')
<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start">
    <div class="card">
        <div class="card-header"><h3>Record a deposit for a customer</h3></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.deposits.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Customer ID</label>
                    <input name="customer_id" class="form-control" value="{{ old('customer_id') }}" placeholder="CUS-000001" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Deposit amount (₹{{ number_format($bounds['min'], 0) }} – ₹{{ number_format($bounds['max'], 0) }})</label>
                    <input type="number" step="1" name="amount" class="form-control" value="{{ old('amount') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Note</label>
                    <textarea name="note" class="form-control" rows="3">{{ old('note') }}</textarea>
                </div>
                <label style="display:flex;gap:8px;align-items:center;margin-bottom:16px;color:#E2E8F0;font-size:14px">
                    <input type="checkbox" name="credit_now" value="1" {{ old('credit_now') ? 'checked' : '' }}> Approve now and credit the payout to the wallet
                </label>
                <button class="btn btn-primary" type="submit">Save deposit</button>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h3>Rule on ₹{{ number_format($previewAmount) }}</h3></div>
        <div class="card-body" style="display:grid;gap:10px">
            <div style="display:flex;justify-content:space-between"><span style="color:#94A3B8">Deduction {{ $preview['deduction_percent'] }}%</span><span>₹{{ number_format($preview['deduction_amount'], 2) }}</span></div>
            <div style="display:flex;justify-content:space-between"><span style="color:#94A3B8">Payout</span><strong style="color:#2DD4BF">₹{{ number_format($preview['withdrawal_amount'], 2) }}</strong></div>
        </div>
    </div>
</div>
@endsection
