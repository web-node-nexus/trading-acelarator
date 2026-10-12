@extends('admin.layouts.app')
@section('title', 'New customer')
@section('page-title', 'New customer')
@section('breadcrumb', 'Admin / Users / New')

@section('content')
<div class="card" style="max-width:720px">
    <div class="card-header"><h3>Create a customer account</h3></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label class="form-label">Full name</label>
                    <input name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input name="phone" class="form-control" value="{{ old('phone') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Customer ID</label>
                    <input name="customer_id" class="form-control" value="{{ old('customer_id') }}" placeholder="Leave blank to auto-generate">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Address</label>
                <input name="address" class="form-control" value="{{ old('address') }}">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>
            <label style="display:flex;gap:8px;align-items:center;margin-bottom:16px;color:#94A3B8;font-size:14px">
                <input type="checkbox" name="is_email_verified" value="1" {{ old('is_email_verified') ? 'checked' : '' }}> Mark email as verified
            </label>
            <button class="btn btn-primary" type="submit">Create customer</button>
        </form>
    </div>
</div>
@endsection
