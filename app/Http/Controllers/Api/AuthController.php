<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private OtpService $otpService) {}

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:15',
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'user',
        ]);

        $this->otpService->generate($user->email, 'email_verification');

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Please verify your email.',
            'data' => ['email' => $user->email, 'customer_id' => $user->customer_id],
        ], 201);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'type' => 'required|in:email_verification,withdrawal',
        ]);

        $verified = $this->otpService->verify($request->email, $request->otp, $request->type);

        if (!$verified) {
            throw ValidationException::withMessages(['otp' => 'Invalid or expired OTP.']);
        }

        if ($request->type === 'email_verification') {
            User::where('email', $request->email)->update(['is_email_verified' => true]);
        }

        return response()->json(['success' => true, 'message' => 'OTP verified successfully.']);
    }

    public function resendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'type' => 'required|in:email_verification,withdrawal',
        ]);

        $this->otpService->generate($request->email, $request->type);

        return response()->json(['success' => true, 'message' => 'OTP sent to your email.']);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(['email' => 'Invalid credentials.']);
        }

        if (!$user->is_email_verified) {
            $this->otpService->generate($user->email, 'email_verification');
            return response()->json([
                'success' => false,
                'message' => 'Email not verified. OTP sent.',
                'requires_verification' => true,
                'email' => $user->email,
            ], 403);
        }

        if (!$user->is_active) {
            return response()->json(['success' => false, 'message' => 'Account is suspended. Contact admin.'], 403);
        }

        $token = $user->createToken('cashflow_app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'token' => $token,
                'user' => $this->formatUser($user),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logged out.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->formatUser($request->user()),
        ]);
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'customer_id' => $user->customer_id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'is_email_verified' => $user->is_email_verified,
            'is_active' => $user->is_active,
            'wallet_balance' => (float) $user->wallet_balance,
            'profile_photo' => $user->profile_photo ? asset('storage/' . $user->profile_photo) : null,
            'address' => $user->address,
            'total_deposited' => (float) $user->total_deposited,
            'total_withdrawn' => (float) $user->total_withdrawn,
            'available_withdrawal' => (float) $user->available_withdrawal,
        ];
    }
}
