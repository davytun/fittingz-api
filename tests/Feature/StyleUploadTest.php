<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StyleUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_upload_style_images(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Test Client',
            'email' => 'test@example.com',
            'phone' => '08012345678',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/clients/{$client->id}/styles/upload", [
            'images' => [
                UploadedFile::fake()->image('style1.jpg'),
                UploadedFile::fake()->image('style2.png'),
            ],
            'category' => 'casual',
            'description' => 'Test description',
        ]);

        $response->assertStatus(201);
        $this->assertCount(2, $response->json());

        $this->assertDatabaseHas('style_images', [
            'client_id' => $client->id,
            'category' => 'casual',
        ]);

        // Verify ID is ULID (26 chars)
        $styleImage = \App\Models\StyleImage::first();
        $this->assertEquals(26, strlen($styleImage->id));
    }

    public function test_unauthenticated_returns_json_error_not_route_error(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Test Client',
        ]);

        // Not acting as any user
        $response = $this->getJson("/api/v1/clients/{$client->id}/styles");

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated. Please login to continue.',
            ]);
    }
}
