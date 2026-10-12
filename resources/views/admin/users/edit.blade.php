@extends('admin.layouts.app')
@section('title', 'Edit ' . $user->customer_id)
@section('page-title', 'Edit profile')
@section('breadcrumb', 'Admin / Users / ' . $user->customer_id)

@section('content')
<div class="card" style="max-width:720px">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label class="form-label">Full name</label>
                    <input name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Customer ID</label>
                    <input name="customer_id" class="form-control" value="{{ old('customer_id', $user->customer_id) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Address</label>
                <input name="address" class="form-control" value="{{ old('address', $user->address) }}">
            </div>
            <label style="display:flex;gap:8px;align-items:center;margin-bottom:10px;color:#E2E8F0;font-size:14px">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}> Account active
            </label>
            <label style="display:flex;gap:8px;align-items:center;margin-bottom:16px;color:#E2E8F0;font-size:14px">
                <input type="checkbox" name="is_email_verified" value="1" {{ old('is_email_verified', $user->is_email_verified) ? 'checked' : '' }}> Email verified
            </label>
            <button class="btn btn-primary" type="submit">Save profile</button>
            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline">Cancel</a>
        </form>
    </div>
</div>
@endsection
