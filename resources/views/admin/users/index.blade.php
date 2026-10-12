@extends('admin.layouts.app')
@section('title', 'Users - Trading Accelerator Admin')
@section('page-title', 'Users')
@section('breadcrumb', 'Admin / Users')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-users" style="margin-right:8px;color:#14B8A6"></i>All Users ({{ $users->total() }})</h3>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="fas fa-user-plus"></i> New customer</a>
    </div>
    <div class="card-body" style="padding-bottom:0">
        <form method="GET" class="filters">
            <input type="text" name="search" class="form-control" placeholder="Search by name, email or Customer ID..." value="{{ request('search') }}" style="max-width:320px">
            <select name="status" class="form-control" style="max-width:160px">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Suspended</option>
            </select>
            <select name="verified" class="form-control" style="max-width:170px">
                <option value="">Email: any</option>
                <option value="yes" {{ request('verified') === 'yes' ? 'selected' : '' }}>Verified</option>
                <option value="no" {{ request('verified') === 'no' ? 'selected' : '' }}>Not verified</option>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
            @if(request()->anyFilled(['search','status']))
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline"><i class="fas fa-times"></i> Clear</a>
            @endif
        </form>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Customer ID</th>
                    <th>Name / Email</th>
                    <th>Phone</th>
                    <th>Wallet Balance</th>
                    <th>Deposits</th>
                    <th>Withdrawals</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <span style="font-family:monospace;font-size:12px;background:rgba(20,184,166,0.1);color:#14B8A6;padding:3px 8px;border-radius:6px">
                            {{ $user->customer_id }}
                        </span>
                    </td>
                    <td>
                        <div style="font-weight:600;font-size:14px">{{ $user->name }}</div>
                        <div style="font-size:12px;color:#94A3B8">{{ $user->email }}</div>
                    </td>
                    <td style="color:#94A3B8">{{ $user->phone ?? '-' }}</td>
                    <td style="font-weight:700;color:#2DD4BF">₹{{ number_format($user->wallet_balance, 2) }}</td>
                    <td><span style="background:rgba(20,184,166,0.1);color:#14B8A6;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600">{{ $user->deposits_count }}</span></td>
                    <td><span style="background:rgba(239,68,68,0.1);color:#EF4444;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600">{{ $user->withdrawals_count }}</span></td>
                    <td>
                        <span class="badge badge-{{ $user->is_active ? 'active' : 'inactive' }}">
                            {{ $user->is_active ? 'Active' : 'Suspended' }}
                        </span>
                    </td>
                    <td style="font-size:12px;color:#94A3B8">{{ $user->created_at->format('d M Y') }}</td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline btn-sm" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" style="display:inline">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $user->is_active ? 'btn-warning' : 'btn-success' }}" title="{{ $user->is_active ? 'Suspend' : 'Activate' }}">
                                    <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center;color:#94A3B8;padding:40px">No users found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $users->links() }}</div>
</div>
@endsection
