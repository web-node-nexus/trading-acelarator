@extends('admin.layouts.app')
@section('title', 'OTP logs')
@section('page-title', 'OTP logs')
@section('breadcrumb', 'Admin / OTP logs')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-shield-halved" style="color:#14B8A6;margin-right:8px"></i>Verification codes</h3>
    </div>
    <div class="card-body" style="padding-bottom:0">
        <p style="color:#94A3B8;font-size:13px;margin-bottom:14px">Codes stay in the customer's email. This list shows whether a code is still valid, used, or expired.</p>
        <form method="GET" class="filters">
            <input type="text" name="search" class="form-control" placeholder="Search email" value="{{ request('search') }}" style="max-width:260px">
            <select name="type" class="form-control" style="max-width:200px">
                <option value="">All types</option>
                <option value="email_verification" {{ request('type') === 'email_verification' ? 'selected' : '' }}>Email verification</option>
                <option value="withdrawal" {{ request('type') === 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
            </select>
            <select name="status" class="form-control" style="max-width:160px">
                <option value="">All states</option>
                <option value="valid" {{ request('status') === 'valid' ? 'selected' : '' }}>Valid</option>
                <option value="used" {{ request('status') === 'used' ? 'selected' : '' }}>Used</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
            </select>
            <button class="btn btn-primary" type="submit">Filter</button>
        </form>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Email</th><th>Type</th><th>State</th><th>Expires</th><th>Sent</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($otps as $otp)
                @php
                    $state = $otp->is_used ? 'used' : ($otp->expires_at->isPast() ? 'expired' : 'valid');
                @endphp
                <tr>
                    <td>{{ $otp->email }}</td>
                    <td>{{ str_replace('_', ' ', $otp->type) }}</td>
                    <td><span class="badge badge-{{ $state === 'valid' ? 'approved' : ($state === 'used' ? 'paid' : 'rejected') }}">{{ $state }}</span></td>
                    <td style="font-size:12px;color:#94A3B8">{{ $otp->expires_at->format('d M Y, h:i A') }}</td>
                    <td style="font-size:12px;color:#94A3B8">{{ $otp->created_at->format('d M Y, h:i A') }}</td>
                    <td>
                        @if($state === 'valid')
                        <form method="POST" action="{{ route('admin.otps.invalidate', $otp) }}">
                            @csrf
                            <button class="btn btn-danger btn-sm" type="submit">Invalidate</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;color:#94A3B8;padding:30px">No OTP records</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $otps->links() }}</div>
</div>
@endsection
