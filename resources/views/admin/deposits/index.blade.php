@extends('admin.layouts.app')
@section('title', 'Deposits - Trading Accelerator Admin')
@section('page-title', 'Deposits')
@section('breadcrumb', 'Admin / Deposits')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-arrow-down-to-line" style="margin-right:8px;color:#14B8A6"></i>All Deposits ({{ $deposits->total() }})</h3>
        <a href="{{ route('admin.deposits.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Manual deposit</a>
    </div>
    <div class="card-body" style="padding-bottom:0">
        <form method="GET" class="filters">
            <input type="text" name="search" class="form-control" placeholder="Search TXN ID, User name or Customer ID..." value="{{ request('search') }}" style="max-width:320px">
            <select name="status" class="form-control" style="max-width:160px">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
            </select>
            <input type="date" name="from" class="form-control" value="{{ request('from') }}" style="max-width:160px">
            <input type="date" name="to" class="form-control" value="{{ request('to') }}" style="max-width:160px">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
            @if(request()->anyFilled(['search','status']))
                <a href="{{ route('admin.deposits.index') }}" class="btn btn-outline"><i class="fas fa-times"></i> Clear</a>
            @endif
        </form>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>TXN ID</th>
                    <th>Customer</th>
                    <th>Deposit Amt</th>
                    <th>Deduction (20%)</th>
                    <th>Net Amount</th>
                    <th>Payout (2x)</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deposits as $deposit)
                <tr>
                    <td style="font-family:monospace;font-size:12px">{{ $deposit->txn_id }}</td>
                    <td>
                        <div style="font-weight:600;font-size:13px">{{ $deposit->user->name }}</div>
                        <div style="font-size:11px;color:#94A3B8">{{ $deposit->user->customer_id }}</div>
                    </td>
                    <td style="font-weight:600">₹{{ number_format($deposit->deposit_amount, 0) }}</td>
                    <td style="color:#F59E0B">-₹{{ number_format($deposit->deduction_amount, 0) }}</td>
                    <td>₹{{ number_format($deposit->net_amount, 0) }}</td>
                    <td style="color:#2DD4BF;font-weight:700">₹{{ number_format($deposit->withdrawal_amount, 0) }}</td>
                    <td>
                        @if($deposit->razorpay_payment_id)
                            <div style="font-size:12px;font-family:monospace;color:#2DD4BF">{{ $deposit->razorpay_payment_id }}</div>
                            <div style="font-size:11px;color:#94A3B8">{{ $deposit->razorpay_order_id }}</div>
                        @elseif($deposit->payment_proof)
                            <a href="{{ asset('storage/'.$deposit->payment_proof) }}" target="_blank" class="btn btn-outline btn-sm">
                                <i class="fas fa-image"></i> View
                            </a>
                        @else
                            <span style="color:#94A3B8;font-size:12px">{{ $deposit->payment_method === 'razorpay' ? 'Awaiting payment' : 'None' }}</span>
                        @endif
                    </td>
                    <td><span class="badge badge-{{ $deposit->status }}">{{ $deposit->status }}</span></td>
                    <td style="font-size:12px;color:#94A3B8">{{ $deposit->created_at->format('d M Y') }}</td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <a href="{{ route('admin.deposits.show', $deposit) }}" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i></a>
                            @if($deposit->status === 'pending')
                                <button onclick="openModal('approve-{{ $deposit->id }}')" class="btn btn-success btn-sm"><i class="fas fa-check"></i></button>
                                <button onclick="openModal('reject-{{ $deposit->id }}')" class="btn btn-danger btn-sm"><i class="fas fa-times"></i></button>
                            @endif
                        </div>
                    </td>
                </tr>

                <!-- Approve Modal -->
                <div class="modal-overlay" id="approve-{{ $deposit->id }}">
                    <div class="modal">
                        <h3>✅ Approve Deposit</h3>
                        <p style="color:#94A3B8;font-size:13px;margin-bottom:20px">
                            Approve <strong style="color:#fff">{{ $deposit->txn_id }}</strong> for
                            <strong style="color:#2DD4BF">₹{{ number_format($deposit->withdrawal_amount, 0) }}</strong> payout?
                        </p>
                        <form method="POST" action="{{ route('admin.deposits.approve', $deposit) }}">
                            @csrf
                            <div class="form-group">
                                <label class="form-label">Note (optional)</label>
                                <textarea name="note" class="form-control" rows="3" placeholder="Optional note..."></textarea>
                            </div>
                            <div style="display:flex;gap:10px;margin-top:8px">
                                <button type="submit" class="btn btn-success" style="flex:1"><i class="fas fa-check"></i> Approve</button>
                                <button type="button" onclick="closeModal('approve-{{ $deposit->id }}')" class="btn btn-outline" style="flex:1">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Reject Modal -->
                <div class="modal-overlay" id="reject-{{ $deposit->id }}">
                    <div class="modal">
                        <h3>❌ Reject Deposit</h3>
                        <p style="color:#94A3B8;font-size:13px;margin-bottom:20px">Reject <strong style="color:#fff">{{ $deposit->txn_id }}</strong>?</p>
                        <form method="POST" action="{{ route('admin.deposits.reject', $deposit) }}">
                            @csrf
                            <div class="form-group">
                                <label class="form-label">Reason (required)</label>
                                <textarea name="note" class="form-control" rows="3" placeholder="Reason for rejection..." required></textarea>
                            </div>
                            <div style="display:flex;gap:10px;margin-top:8px">
                                <button type="submit" class="btn btn-danger" style="flex:1"><i class="fas fa-times"></i> Reject</button>
                                <button type="button" onclick="closeModal('reject-{{ $deposit->id }}')" class="btn btn-outline" style="flex:1">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
                @empty
                <tr><td colspan="10" style="text-align:center;color:#94A3B8;padding:40px">No deposits found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $deposits->links() }}</div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }
document.querySelectorAll('.modal-overlay').forEach(el => {
    el.addEventListener('click', e => { if(e.target === el) el.classList.remove('show'); });
});
</script>
@endsection
