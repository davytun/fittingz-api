<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\PasswordResetNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    // ─── verifyEmail ─────────────────────────────────────────────────────────

    public function test_verify_email_returns_error_when_code_is_wrong(): void
    {
        $user = User::factory()->unverified()->create([
            'verification_code' => '1234',
            'verification_code_expires_at' => now()->addMinutes(10),
        ]);

        $this->postJson('/api/v1/auth/verify-email', [
            'email' => $user->email,
            'code' => '9999',
        ])->assertStatus(400)
          ->assertJson([
              'success' => false,
              'message' => 'Invalid verification code',
          ]);
    }

    public function test_verify_email_returns_error_when_code_is_expired(): void
    {
        $user = User::factory()->unverified()->create([
            'verification_code' => '1234',
            'verification_code_expires_at' => now()->subMinutes(1),
        ]);

        $this->postJson('/api/v1/auth/verify-email', [
            'email' => $user->email,
            'code' => '1234',
        ])->assertStatus(400)
          ->assertJson([
              'success' => false,
              'message' => 'Verification code has expired. Please request a new one.',
          ]);
    }

    public function test_verify_email_checks_code_before_expiry(): void
    {
        // When the code is wrong AND expired, the code error should appear first
        $user = User::factory()->unverified()->create([
            'verification_code' => '1234',
            'verification_code_expires_at' => now()->subMinutes(1),
        ]);

        $this->postJson('/api/v1/auth/verify-email', [
            'email' => $user->email,
            'code' => '9999', // wrong code
        ])->assertStatus(400)
          ->assertJson([
              'success' => false,
              'message' => 'Invalid verification code',
          ]);
    }

    public function test_verify_email_succeeds_with_valid_code_and_not_expired(): void
    {
        $user = User::factory()->unverified()->create([
            'verification_code' => '5678',
            'verification_code_expires_at' => now()->addMinutes(10),
        ]);

        $this->postJson('/api/v1/auth/verify-email', [
            'email' => $user->email,
            'code' => '5678',
        ])->assertOk()
          ->assertJson([
              'success' => true,
              'message' => 'Email verified successfully. You can now log in.',
          ]);

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_verify_email_returns_success_if_already_verified(): void
    {
        $user = User::factory()->create(); // email_verified_at is set by default

        $this->postJson('/api/v1/auth/verify-email', [
            'email' => $user->email,
            'code' => '1234',
        ])->assertOk()
          ->assertJson([
              'success' => true,
              'message' => 'Email already verified',
          ]);
    }

    public function test_verify_email_returns_validation_error_for_unknown_email(): void
    {
        $this->postJson('/api/v1/auth/verify-email', [
            'email' => 'nonexistent@example.com',
            'code' => '1234',
        ])->assertStatus(422)
          ->assertJsonPath('errors.email', 'No account found with this email address');
    }

    public function test_verify_email_returns_validation_error_for_non_4_char_code(): void
    {
        $user = User::factory()->unverified()->create();

        $this->postJson('/api/v1/auth/verify-email', [
            'email' => $user->email,
            'code' => '12345', // 5 chars instead of 4
        ])->assertStatus(422)
          ->assertJsonPath('errors.code', 'Verification code must be 4 digits');
    }

    // ─── resendVerification ───────────────────────────────────────────────────

    public function test_resend_verification_returns_error_if_already_verified(): void
    {
        $user = User::factory()->create(); // verified by default

        $this->postJson('/api/v1/auth/resend-verification', [
            'email' => $user->email,
        ])->assertStatus(400)
          ->assertJson([
              'success' => false,
              'message' => 'Email already verified',
          ]);
    }

    public function test_resend_verification_sends_code_for_unverified_user(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->postJson('/api/v1/auth/resend-verification', [
            'email' => $user->email,
        ])->assertOk()
          ->assertJson([
              'success' => true,
              'message' => 'Verification code resent. Please check your email.',
          ]);

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_resend_verification_returns_validation_error_for_unknown_email(): void
    {
        $this->postJson('/api/v1/auth/resend-verification', [
            'email' => 'unknown@example.com',
        ])->assertStatus(422)
          ->assertJsonPath('errors.email', 'No account found with this email address');
    }

    // ─── forgotPassword ───────────────────────────────────────────────────────

    public function test_forgot_password_sends_reset_code_for_existing_user(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $user->email,
        ])->assertOk()
          ->assertJson([
              'success' => true,
              'message' => 'Password reset code sent to your email',
          ]);

        Notification::assertSentTo($user, PasswordResetNotification::class);

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    public function test_forgot_password_returns_validation_error_for_unknown_email(): void
    {
        $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'nobody@example.com',
        ])->assertStatus(422)
          ->assertJsonPath('errors.email', 'No account found with this email address');
    }

    public function test_forgot_password_requires_valid_email_format(): void
    {
        $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'not-an-email',
        ])->assertStatus(422)
          ->assertJsonPath('errors.email', 'Please provide a valid email address');
    }

    // ─── verifyResetCode ─────────────────────────────────────────────────────

    public function test_verify_reset_code_returns_error_for_no_reset_record(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/verify-reset-code', [
            'email' => $user->email,
            'token' => '1234',
        ])->assertStatus(400)
          ->assertJson([
              'success' => false,
              'message' => 'Invalid reset code',
          ]);
    }

    public function test_verify_reset_code_returns_error_for_wrong_token(): void
    {
        $user = User::factory()->create();
        $realToken = '5678';

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($realToken),
            'created_at' => now(),
        ]);

        $this->postJson('/api/v1/auth/verify-reset-code', [
            'email' => $user->email,
            'token' => '9999',
        ])->assertStatus(400)
          ->assertJson([
              'success' => false,
              'message' => 'Invalid reset code',
          ]);
    }

    public function test_verify_reset_code_returns_error_for_expired_token(): void
    {
        $user = User::factory()->create();
        $realToken = '1234';

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($realToken),
            'created_at' => now()->subMinutes(61),
        ]);

        $this->postJson('/api/v1/auth/verify-reset-code', [
            'email' => $user->email,
            'token' => $realToken,
        ])->assertStatus(400)
          ->assertJson([
              'success' => false,
              'message' => 'Reset code has expired. Please request a new one.',
          ]);
    }

    public function test_verify_reset_code_succeeds_with_valid_token(): void
    {
        $user = User::factory()->create();
        $realToken = '4321';

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($realToken),
            'created_at' => now(),
        ]);

        $this->postJson('/api/v1/auth/verify-reset-code', [
            'email' => $user->email,
            'token' => $realToken,
        ])->assertOk()
          ->assertJson([
              'success' => true,
              'message' => 'Reset code verified successfully',
          ]);
    }

    public function test_verify_reset_code_requires_email_to_exist_in_users(): void
    {
        $this->postJson('/api/v1/auth/verify-reset-code', [
            'email' => 'ghost@example.com',
            'token' => '1234',
        ])->assertStatus(422)
          ->assertJsonPath('errors.email', 'No account found with this email address');
    }

    public function test_verify_reset_code_requires_exactly_4_char_token(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/verify-reset-code', [
            'email' => $user->email,
            'token' => '12345', // 5 chars
        ])->assertStatus(422)
          ->assertJsonPath('errors.token', 'Reset code must be 4 digits');
    }

    // ─── resetPassword ───────────────────────────────────────────────────────

    public function test_reset_password_returns_error_when_token_not_found(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/reset-password', [
            'email' => $user->email,
            'token' => '1234',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ])->assertStatus(400)
          ->assertJson([
              'success' => false,
              'message' => 'Invalid reset code',
          ]);
    }

    public function test_reset_password_succeeds_with_valid_token(): void
    {
        $user = User::factory()->create();
        $resetToken = '7890';

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($resetToken),
            'created_at' => now(),
        ]);

        $this->postJson('/api/v1/auth/reset-password', [
            'email' => $user->email,
            'token' => $resetToken,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertOk()
          ->assertJson([
              'success' => true,
              'message' => 'Password reset successfully. Please login with your new password.',
          ]);

        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    public function test_reset_password_requires_email_exists_in_users(): void
    {
        $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'ghost@example.com',
            'token' => '1234',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertStatus(422)
          ->assertJsonPath('errors.email', 'No account found with this email address');
    }

    public function test_reset_password_requires_exactly_4_char_token(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/reset-password', [
            'email' => $user->email,
            'token' => '123', // only 3 chars
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertStatus(422)
          ->assertJsonPath('errors.token', 'Reset code must be 4 digits');
    }

    // ─── login ───────────────────────────────────────────────────────────────

    public function test_login_returns_invalid_credentials_for_wrong_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('correctpass')]);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrongpass',
        ])->assertStatus(401)
          ->assertJsonFragment(['message' => "Invalid credentials. 5 attempts remaining before account lockout."]);
    }

    public function test_login_returns_invalid_credentials_for_unknown_email(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'nobody@example.com',
            'password' => 'anypassword',
        ])->assertStatus(401)
          ->assertJson([
              'success' => false,
              'message' => 'Invalid credentials',
          ]);
    }

    public function test_login_succeeds_with_correct_credentials(): void
    {
        $user = User::factory()->create(['password' => Hash::make('correctpass')]);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'correctpass',
        ])->assertOk()
          ->assertJson([
              'success' => true,
              'message' => 'Login successful',
          ])
          ->assertJsonPath('data.token_type', 'Bearer');
    }

    public function test_login_returns_error_for_unverified_email(): void
    {
        $user = User::factory()->unverified()->create(['password' => Hash::make('correctpass')]);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'correctpass',
        ])->assertStatus(403)
          ->assertJson([
              'success' => false,
              'message' => 'Please verify your email before logging in.',
          ]);
    }

    // ─── changePassword ───────────────────────────────────────────────────────

    public function test_change_password_returns_error_for_wrong_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('oldpassword')]);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/auth/change-password', [
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ])->assertStatus(400)
          ->assertJson([
              'success' => false,
              'message' => 'Current password is incorrect',
          ]);
    }

    public function test_change_password_succeeds_with_correct_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('oldpassword')]);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/auth/change-password', [
            'current_password' => 'oldpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ])->assertOk()
          ->assertJson([
              'success' => true,
              'message' => 'Password changed successfully',
          ]);
    }
}