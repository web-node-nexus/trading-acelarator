@extends('admin.layouts.app')
@section('title', 'Payout rules')
@section('page-title', 'Payout rules')
@section('breadcrumb', 'Admin / Payout rules')

@section('content')
<div style="display:grid;grid-template-columns:1.2fr 0.8fr;gap:20px;align-items:start">
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-sliders" style="color:#14B8A6;margin-right:8px"></i>Business settings</h3></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                @method('PUT')
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group">
                        <label class="form-label">Deduction percent</label>
                        <input type="number" step="0.01" name="deduction_percent" class="form-control" value="{{ old('deduction_percent', $settings['deduction_percent']) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payout multiplier</label>
                        <input type="number" step="0.01" name="payout_multiplier" class="form-control" value="{{ old('payout_multiplier', $settings['payout_multiplier']) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Minimum deposit (₹)</label>
                        <input type="number" step="1" name="min_deposit" class="form-control" value="{{ old('min_deposit', $settings['min_deposit']) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Maximum deposit (₹)</label>
                        <input type="number" step="1" name="max_deposit" class="form-control" value="{{ old('max_deposit', $settings['max_deposit']) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Minimum withdrawal (₹)</label>
                        <input type="number" step="1" name="min_withdrawal" class="form-control" value="{{ old('min_withdrawal', $settings['min_withdrawal']) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Support email</label>
                        <input type="email" name="support_email" class="form-control" value="{{ old('support_email', $settings['support_email']) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Support phone</label>
                        <input type="text" name="support_phone" class="form-control" value="{{ old('support_phone', $settings['support_phone']) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Payment note</label>
                    <textarea name="payment_note" class="form-control" rows="3">{{ old('payment_note', $settings['payment_note']) }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save rules</button>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h3>Example on ₹{{ number_format($example) }}</h3></div>
        <div class="card-body">
            <div style="display:grid;gap:12px">
                <div style="display:flex;justify-content:space-between"><span style="color:var(--text-muted)">Deduction</span><strong style="color:#F59E0B">₹{{ number_format($deduction, 2) }}</strong></div>
                <div style="display:flex;justify-content:space-between"><span style="color:var(--text-muted)">Customer receives</span><strong style="color:#2DD4BF;font-size:20px">₹{{ number_format($payout, 2) }}</strong></div>
            </div>
            <p style="color:var(--text-muted);font-size:13px;margin-top:16px;line-height:1.5">This applies to new deposits and the in-app calculator. Deposits already saved keep the numbers they were created with.</p>
        </div>
    </div>
</div>
@endsection
