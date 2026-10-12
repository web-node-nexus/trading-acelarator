<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    public function index(Request $request)
    {
        $query = Otp::query()->latest();

        if ($request->search) {
            $query->where('email', 'like', '%' . $request->search . '%');
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->status === 'valid') {
            $query->where('is_used', false)->where('expires_at', '>', now());
        } elseif ($request->status === 'used') {
            $query->where('is_used', true);
        } elseif ($request->status === 'expired') {
            $query->where('is_used', false)->where('expires_at', '<=', now());
        }

        $otps = $query->paginate(25)->withQueryString();

        return view('admin.otps.index', compact('otps'));
    }

    public function invalidate(Otp $otp)
    {
        $otp->update(['is_used' => true]);

        return back()->with('success', 'OTP invalidated for ' . $otp->email . '.');
    }
}
