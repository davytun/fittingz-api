<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResendVerificationRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyResetCodeRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Notifications\PasswordResetNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = User::create([
                'email' => strtolower(trim($request->email)),
                'password' => $request->password,
                'business_name' => trim($request->business_name),
                'contact_phone' => trim($request->contact_phone),
                'business_address' => trim($request->business_address),
            ]);

            $user->notify(new VerifyEmailNotification);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Registration successful. Please check your email to verify your account.',
                'data' => [
                    'user' => new UserResource($user),
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed. Please try again.',
            ], 500);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $email = strtolower(trim($request->email));
        $user = User::where('email', $email)->first();

        if ($user && $user->isLocked()) {
            $remainingMinutes = now()->diffInMinutes($user->locked_until);

            return response()->json([
                'status' => 'error',
                'message' => "Account is locked due to multiple failed login attempts. Try again in {$remainingMinutes} minutes.",
            ], 423);
        }

        $credentials = [
            'email' => $email,
            'password' => $request->password,
        ];

        if (! Auth::attempt($credentials)) {
            if ($user) {
                $user->incrementFailedAttempts();

                if ($user->isLocked()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Account locked due to multiple failed login attempts. Try again in 30 minutes.',
                    ], 423);
                }

                $remainingAttempts = 5 - $user->failed_login_attempts;

                return response()->json([
                    'status' => 'error',
                    'message' => "Invalid credentials. {$remainingAttempts} attempts remaining before account lockout.",
                ], 401);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = Auth::user();

        if (! $user->hasVerifiedEmail()) {
            Auth::logout();

            return response()->json([
                'status' => 'error',
                'message' => 'Please verify your email before logging in.',
            ], 403);
        }

        $user->resetFailedAttempts();
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
                'expires_in' => [
                    'minutes' => 10080,
                    'days' => 7,
                ],
            ],
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var PersonalAccessToken $token */
        $token = $request->user()->currentAccessToken();
        $token->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully',
        ], 200);
    }

    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Token refreshed successfully',
            'data' => [
                'token' => $token,
                'expires_in' => [
                    'minutes' => 10080,
                    'days' => 7,
                ],
            ],
        ], 200);
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        $user = User::findOrFail($request->route('id'));

        if (! hash_equals(sha1($user->getEmailForVerification()), (string) $request->route('hash'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid verification link',
            ], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Email already verified',
            ], 200);
        }

        $user->markEmailAsVerified();

        return response()->json([
            'status' => 'success',
            'message' => 'Email verified successfully. You can now log in.',
        ], 200);
    }

    public function resendVerification(ResendVerificationRequest $request): JsonResponse
    {
        $user = User::where('email', strtolower(trim($request->email)))->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email already verified',
            ], 400);
        }

        $user->notify(new VerifyEmailNotification);

        return response()->json([
            'status' => 'success',
            'message' => 'Verification email sent',
        ], 200);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $email = strtolower(trim($request->email));
        $user = User::where('email', $email)->first();

        $token = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $user->notify(new PasswordResetNotification($token));

        return response()->json([
            'status' => 'success',
            'message' => 'Password reset code sent to your email',
        ], 200);
    }

    public function verifyResetCode(VerifyResetCodeRequest $request): JsonResponse
    {
        $email = strtolower(trim($request->email));
        $token = trim($request->token);

        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (! $resetRecord) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid reset code',
            ], 400);
        }

        if (! Hash::check($token, $resetRecord->token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid reset code',
            ], 400);
        }

        if (now()->diffInMinutes($resetRecord->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return response()->json([
                'status' => 'error',
                'message' => 'Reset code has expired. Please request a new one.',
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Reset code verified successfully',
        ], 200);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $email = strtolower(trim($request->email));
        $token = trim($request->token);

        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (! $resetRecord) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid reset code',
            ], 400);
        }

        if (! Hash::check($token, $resetRecord->token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid reset code',
            ], 400);
        }

        if (now()->diffInMinutes($resetRecord->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return response()->json([
                'status' => 'error',
                'message' => 'Reset code has expired. Please request a new one.',
            ], 400);
        }

        $user = User::where('email', $email)->first();
        $user->update([
            'password' => $request->password,
        ]);

        DB::table('password_reset_tokens')->where('email', $email)->delete();
        $user->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Password reset successfully. Please login with your new password.',
        ], 200);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password is incorrect',
            ], 400);
        }

        $user->update([
            'password' => $request->new_password,
        ]);

        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully',
            'data' => [
                'token' => $token,
            ],
        ], 200);
    }
}
