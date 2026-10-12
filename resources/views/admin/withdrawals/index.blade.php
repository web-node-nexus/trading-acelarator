@extends('admin.layouts.app')
@section('title', 'Withdrawals - Trading Accelerator Admin')
@section('page-title', 'Withdrawals')
@section('breadcrumb', 'Admin / Withdrawals')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-arrow-up-from-line" style="margin-right:8px;color:#EF4444"></i>All Withdrawals ({{ $withdrawals->total() }})</h3>
    </div>
    <div class="card-body" style="padding-bottom:0">
        <form method="GET" class="filters">
            <input type="text" name="search" class="form-control" placeholder="Search TXN ID, User name or Customer ID..." value="{{ request('search') }}" style="max-width:320px">
            <select name="status" class="form-control" style="max-width:160px">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
            @if(request()->anyFilled(['search','status']))
                <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-outline"><i class="fas fa-times"></i> Clear</a>
            @endif
        </form>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>TXN ID</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Bank / UPI</th>
                    <th>Account</th>
                    <th>OTP</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($withdrawals as $w)
                <tr>
                    <td style="font-family:monospace;font-size:12px">{{ $w->txn_id }}</td>
                    <td>
                        <div style="font-weight:600;font-size:13px">{{ $w->user->name }}</div>
                        <div style="font-size:11px;color:#94A3B8">{{ $w->user->customer_id }}</div>
                    </td>
                    <td style="font-weight:700;color:#EF4444;font-size:15px">₹{{ number_format($w->amount, 0) }}</td>
                    <td style="font-size:13px">
                        @if($w->bank_name)
                            <div style="font-weight:600">{{ $w->bank_name }}</div>
                            <div style="font-size:11px;color:#94A3B8">{{ $w->ifsc_code }}</div>
                        @elseif($w->upi_id)
                            <span style="color:#14B8A6">UPI · {{ $w->upi_id }}</span>
                        @else -
                        @endif
                    </td>
                    <td style="font-size:12px;color:#94A3B8">
                        @if($w->account_number)
                            {{ $w->account_holder_name }}<br>****{{ substr($w->account_number, -4) }}
                        @else -
                        @endif
                    </td>
                    <td>
                        <span style="color:{{ $w->otp_verified === 'yes' ? '#10B981' : '#EF4444' }};font-size:12px;font-weight:600">
                            {{ $w->otp_verified === 'yes' ? '✓ Verified' : '✗ No' }}
                        </span>
                    </td>
                    <td><span class="badge badge-{{ $w->status }}">{{ $w->status }}</span></td>
                    <td style="font-size:12px;color:#94A3B8">{{ $w->created_at->format('d M Y') }}</td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            <a href="{{ route('admin.withdrawals.show', $w) }}" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i></a>
                            @if($w->status === 'pending')
                                <form method="POST" action="{{ route('admin.withdrawals.approve', $w) }}" style="display:inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Approve this withdrawal?')">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>
                                <button onclick="openModal('reject-w-{{ $w->id }}')" class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Reject</button>
                            @elseif($w->status === 'approved')
                                <form method="POST" action="{{ route('admin.withdrawals.mark-paid', $w) }}" style="display:inline">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Mark as paid? This will deduct ₹{{ number_format($w->amount,0) }} from user wallet.')">
                                        <i class="fas fa-money-bill"></i> Mark Paid
                                    </button>
                                </form>
                            @else
                                <span style="color:#94A3B8;font-size:12px">{{ ucfirst($w->status) }}</span>
                            @endif
                        </div>
                    </td>
                </tr>

                <!-- Reject Modal -->
                <div class="modal-overlay" id="reject-w-{{ $w->id }}">
                    <div class="modal">
                        <h3>❌ Reject Withdrawal</h3>
                        <p style="color:#94A3B8;font-size:13px;margin-bottom:20px">Reject <strong style="color:#fff">{{ $w->txn_id }}</strong> of <strong style="color:#EF4444">₹{{ number_format($w->amount,0) }}</strong>?</p>
                        <form method="POST" action="{{ route('admin.withdrawals.reject', $w) }}">
                            @csrf
                            <div class="form-group">
                                <label class="form-label">Reason (required)</label>
                                <textarea name="note" class="form-control" rows="3" placeholder="Reason for rejection..." required></textarea>
                            </div>
                            <div style="display:flex;gap:10px;margin-top:8px">
                                <button type="submit" class="btn btn-danger" style="flex:1">Reject</button>
                                <button type="button" onclick="closeModal('reject-w-{{ $w->id }}')" class="btn btn-outline" style="flex:1">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
                @empty
                <tr><td colspan="9" style="text-align:center;color:#94A3B8;padding:40px">No withdrawals found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $withdrawals->links() }}</div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }
document.querySelectorAll('.modal-overlay').forEach(el => {
    el.addEventListener('click', e => { if(e.target === el) el.classList.remove('show'); });
});
</script>
@endsection
