<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use RuntimeException;

class AuthController extends Controller
{
    public function __construct(private OtpService $otpService) {}

    /**
     * Send SMS OTP to phone.
     * Register: pass name. Login: phone only (user must already exist).
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string|max:20',
            'name' => 'nullable|string|max:100',
            'mode' => 'nullable|in:login,register',
        ]);

        try {
            $phone = $this->otpService->normalizePhone($validated['phone']);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['phone' => $e->getMessage()]);
        }

        $mode = $validated['mode'] ?? ($validated['name'] ? 'register' : 'login');
        $user = User::where('phone', $phone)->where('role', 'user')->first();

        if ($mode === 'register') {
            if (empty($validated['name'])) {
                throw ValidationException::withMessages(['name' => 'Name is required to create an account.']);
            }
            if ($user) {
                throw ValidationException::withMessages(['phone' => 'This number is already registered. Please sign in.']);
            }
            Cache::put($this->pendingKey($phone), [
                'name' => trim($validated['name']),
                'phone' => $phone,
            ], now()->addMinutes(15));
        } else {
            if (!$user) {
                throw ValidationException::withMessages(['phone' => 'No account found. Please create an account.']);
            }
            if (!$user->is_active) {
                return response()->json(['success' => false, 'message' => 'Account is suspended. Contact admin.'], 403);
            }
        }

        try {
            $this->otpService->generateForPhone($phone);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 502);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to your phone.',
            'data' => [
                'phone' => $phone,
                'mode' => $mode,
            ],
        ]);
    }

    /**
     * Verify SMS OTP and issue a long-lived Sanctum token (until user logs out).
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string|max:20',
            'otp' => 'required|string|min:4|max:8',
            'name' => 'nullable|string|max:100',
            'mode' => 'nullable|in:login,register',
        ]);

        try {
            $phone = $this->otpService->normalizePhone($validated['phone']);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['phone' => $e->getMessage()]);
        }

        if (!$this->otpService->verifyForPhone($phone, $validated['otp'])) {
            throw ValidationException::withMessages(['otp' => 'Invalid or expired OTP.']);
        }

        $mode = $validated['mode'] ?? 'login';
        $user = User::where('phone', $phone)->where('role', 'user')->first();

        if (!$user) {
            $pending = Cache::pull($this->pendingKey($phone)) ?? [];
            $name = trim((string) ($validated['name'] ?? $pending['name'] ?? ''));
            if ($name === '') {
                throw ValidationException::withMessages(['name' => 'Name is required to create an account.']);
            }

            $user = User::create([
                'name' => $name,
                'email' => $phone . '@phone.tradingaccelerator.local',
                'phone' => $phone,
                'password' => Hash::make(Str::random(40)),
                'role' => 'user',
                'is_email_verified' => true,
                'is_active' => true,
            ]);
        } else {
            if (!$user->is_active) {
                return response()->json(['success' => false, 'message' => 'Account is suspended. Contact admin.'], 403);
            }
            if (!$user->is_email_verified) {
                $user->update(['is_email_verified' => true]);
            }
        }

        // One active app session token; persists until explicit logout (sanctum expiration = null).
        $user->tokens()->where('name', 'cashflow_app')->delete();
        $token = $user->createToken('cashflow_app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => $mode === 'register' ? 'Account created.' : 'Login successful.',
            'data' => [
                'token' => $token,
                'user' => $this->formatUser($user->fresh()),
            ],
        ]);
    }

    public function resendOtp(Request $request): JsonResponse
    {
        return $this->sendOtp($request);
    }

    /** @deprecated Use sendOtp — kept for older clients */
    public function register(Request $request): JsonResponse
    {
        $request->merge(['mode' => 'register']);
        return $this->sendOtp($request);
    }

    /** @deprecated Use sendOtp — kept for older clients */
    public function login(Request $request): JsonResponse
    {
        $request->merge(['mode' => 'login']);
        return $this->sendOtp($request);
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

    private function pendingKey(string $phone): string
    {
        return 'otp_register:' . $phone;
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
