<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResendVerificationRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
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
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * @group Authentication
 */
class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = User::create([
                'email'            => strtolower(trim($request->email)),
                'password'         => $request->password,
                'business_name'    => trim($request->business_name),
                'contact_phone'    => trim($request->contact_phone),
                'business_address' => trim($request->business_address),
            ]);

            $code = $user->generateVerificationCode();
            $user->notify(new VerifyEmailNotification($code));

            DB::commit();

            return ApiResponse::success(
                'Registration successful. Please check your email for your verification code.',
                ['user' => new UserResource($user)],
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Registration failed', ['exception' => $e]);

            return ApiResponse::error('Registration failed. Please try again.', null, 500);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $email = strtolower(trim($request->email));
        $user = User::where('email', $email)->first();

        if ($user && $user->isLocked()) {
            Log::channel('security')->warning('Login attempt on locked account', [
                'email' => $email,
                'ip' => $request->ip(),
            ]);

            $remainingMinutes = now()->diffInMinutes($user->locked_until);

            return ApiResponse::error(
                "Account is locked due to multiple failed login attempts. Try again in {$remainingMinutes} minutes.",
                null,
                423
            );
        }

        $credentials = [
            'email' => $email,
            'password' => $request->password,
        ];

        if (! Auth::attempt($credentials)) {
            if ($user) {
                Log::channel('security')->warning('Failed login attempt', [
                    'email' => $email,
                    'ip' => $request->ip(),
                    'attempts' => $user->failed_login_attempts + 1,
                ]);

                $user->incrementFailedAttempts();

                if ($user->isLocked()) {
                    Log::channel('security')->alert('Account locked due to multiple failed attempts', [
                        'email' => $email,
                        'ip' => $request->ip(),
                    ]);

                    return ApiResponse::error(
                        'Account locked due to multiple failed login attempts. Try again in 30 minutes.',
                        null,
                        423
                    );
                }

                $remainingAttempts = 5 - $user->failed_login_attempts;

                return ApiResponse::error(
                    "Invalid email or password. {$remainingAttempts} attempt(s) remaining before lockout.",
                    null,
                    401
                );
            }

            Log::channel('security')->info('Failed login attempt - user not found', [
                'email' => $email,
                'ip' => $request->ip(),
            ]);

            return ApiResponse::error('Invalid email or password.', null, 401);
        }

        $user = Auth::user();

        if (! $user->hasVerifiedEmail()) {
            Auth::logout();

            return ApiResponse::error('Please verify your email before logging in.', null, 403);
        }

        Log::channel('api')->info('User logged in', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
        ]);

        $user->resetFailedAttempts();
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success(
            'Login successful',
            [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => [
                    'minutes' => 10080,
                    'days' => 7,
                ],
            ]
        );
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var PersonalAccessToken $token */
        $token = $request->user()->currentAccessToken();
        $token->delete();

        return ApiResponse::success('Logged out successfully');
    }

    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success(
            'Token refreshed successfully',
            [
                'token' => $token,
                'expires_in' => [
                    'minutes' => 10080,
                    'days' => 7,
                ],
            ]
        );
    }

    public function verifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        $user = User::where('email', strtolower(trim($request->email)))->first();

        if (! $user) {
            return ApiResponse::error('Invalid email or verification code.', null, 400);
        }

        if ($user->hasVerifiedEmail()) {
            return ApiResponse::success('Email already verified');
        }

        if (! $user->verification_code_expires_at || $user->verification_code_expires_at->isPast()) {
            return ApiResponse::error('Verification code has expired. Please request a new one.', null, 400);
        }

        if ($user->verification_code !== $request->code) {
            return ApiResponse::error('Invalid email or verification code.', null, 400);
        }

        $user->markEmailAsVerified();
        $user->update([
            'verification_code'            => null,
            'verification_code_expires_at' => null,
        ]);

        return ApiResponse::success('Email verified successfully. You can now log in.');
    }

    public function resendVerification(ResendVerificationRequest $request): JsonResponse
    {
        $user = User::where('email', strtolower(trim($request->email)))->first();

        if (! $user) {
            return ApiResponse::error('No account found with this email address.', null, 404);
        }

        if ($user->hasVerifiedEmail()) {
            return ApiResponse::error('This email address is already verified.', null, 400);
        }

        $code = $user->generateVerificationCode();
        $user->notify(new VerifyEmailNotification($code));

        return ApiResponse::success('Verification code sent. Please check your email.');
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $email = strtolower(trim($request->email));
        $user = User::where('email', $email)->first();

        if (! $user) {
            return ApiResponse::error('No account found with this email address.', null, 404);
        }

        $token = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token'      => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $user->notify(new PasswordResetNotification($token));

        return ApiResponse::success('Password reset code sent to your email.');
    }

    public function verifyResetCode(VerifyResetCodeRequest $request): JsonResponse
    {
        $email = strtolower(trim($request->email));
        $token = trim($request->token);

        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (! $resetRecord) {
            return ApiResponse::error('Invalid reset code.', null, 400);
        }

        if (! Hash::check($token, $resetRecord->token)) {
            return ApiResponse::error('Invalid reset code.', null, 400);
        }

        if (now()->diffInMinutes($resetRecord->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return ApiResponse::error('Reset code has expired. Please request a new one.', null, 400);
        }

        return ApiResponse::success('Reset code verified successfully');
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $email = strtolower(trim($request->email));
        $token = trim($request->token);

        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (! $resetRecord) {
            return ApiResponse::error('Invalid reset code.', null, 400);
        }

        if (! Hash::check($token, $resetRecord->token)) {
            return ApiResponse::error('Invalid reset code.', null, 400);
        }

        if (now()->diffInMinutes($resetRecord->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return ApiResponse::error('Reset code has expired. Please request a new one.', null, 400);
        }

        $user = User::where('email', $email)->first();
        $user->update([
            'password' => $request->password,
        ]);

        DB::table('password_reset_tokens')->where('email', $email)->delete();
        $user->tokens()->delete();

        return ApiResponse::success('Password reset successfully. Please login with your new password.');
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return ApiResponse::error('Current password is incorrect.', null, 400);
        }

        $user->update([
            'password' => $request->new_password,
        ]);

        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success(
            'Password changed successfully',
            [
                'token' => $token,
            ]
        );
    }
}
