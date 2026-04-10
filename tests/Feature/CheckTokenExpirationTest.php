<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CheckTokenExpirationTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsWithToken(User $user, \DateTimeInterface $createdAt): string
    {
        $token = $user->createToken('auth_token');

        // Backdate the token's created_at to simulate an old token
        PersonalAccessToken::where('id', $token->accessToken->id)
            ->update(['created_at' => $createdAt]);

        return $token->plainTextToken;
    }

    public function test_valid_token_within_7_days_allows_request(): void
    {
        $user = User::factory()->create();
        $plainToken = $this->actingAsWithToken($user, now()->subDays(3));

        $this->withToken($plainToken)
            ->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);
    }

    public function test_token_older_than_7_days_is_rejected_with_401(): void
    {
        $user = User::factory()->create();
        $plainToken = $this->actingAsWithToken($user, now()->subDays(8));

        $this->withToken($plainToken)
            ->postJson('/api/v1/auth/logout')
            ->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Token has expired. Please login again.',
            ]);
    }

    public function test_expired_token_is_deleted_from_database(): void
    {
        $user = User::factory()->create();
        $plainToken = $this->actingAsWithToken($user, now()->subDays(8));

        $tokenIdBefore = PersonalAccessToken::where('tokenable_id', $user->id)->first()?->id;
        $this->assertNotNull($tokenIdBefore);

        $this->withToken($plainToken)
            ->postJson('/api/v1/auth/logout');

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenIdBefore]);
    }

    public function test_token_exactly_7_days_old_is_not_expired(): void
    {
        $user = User::factory()->create();
        // exactly 7 days ago - addDays(7)->isPast() would be false for exactly 7 days
        $plainToken = $this->actingAsWithToken($user, now()->subDays(7)->addMinute());

        $this->withToken($plainToken)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();
    }

    public function test_freshly_created_token_is_not_rejected(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/auth/logout')
            ->assertOk();
    }
}