<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Models\Client;
use App\Models\Order;
use App\Models\Style;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StyleControllerDestroyTest extends TestCase
{
    use RefreshDatabase;

    private function createStyle(User $user, string $imagePath = 'styles/test.jpg'): Style
    {
        return Style::create([
            'user_id' => $user->id,
            'title' => 'Test Style',
            'description' => 'A test style',
            'image_path' => $imagePath,
            'category' => 'tops',
            'tags' => ['casual'],
        ]);
    }

    private function createClient(User $user): Client
    {
        return Client::create([
            'user_id' => $user->id,
            'name' => 'Test Client',
            'email' => 'client@example.com',
            'phone' => '08012345678',
            'gender' => Gender::MALE,
        ]);
    }

    private function createOrder(User $user, Client $client): Order
    {
        return Order::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'title' => 'Test Order',
            'quantity' => 1,
            'total_amount' => 100.00,
            'status' => 'pending',
        ]);
    }

    public function test_destroy_returns_400_when_style_is_linked_to_orders(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $style = $this->createStyle($user);
        $client = $this->createClient($user);
        $order = $this->createOrder($user, $client);
        $order->styles()->attach($style->id);

        Sanctum::actingAs($user);

        $this->deleteJson("/api/v1/styles/{$style->id}")
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot delete style. It is linked to one or more orders.',
            ]);

        $this->assertDatabaseHas('styles', ['id' => $style->id]);
    }

    public function test_destroy_returns_200_when_style_is_not_linked_to_orders(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $style = $this->createStyle($user);

        Sanctum::actingAs($user);

        $this->deleteJson("/api/v1/styles/{$style->id}")
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Style deleted successfully',
            ]);

        $this->assertDatabaseMissing('styles', ['id' => $style->id]);
    }

    public function test_destroy_returns_404_for_another_users_style(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $style = $this->createStyle($owner);

        Sanctum::actingAs($intruder);

        $this->deleteJson("/api/v1/styles/{$style->id}")
            ->assertNotFound();
    }

    public function test_destroy_error_message_does_not_include_period_at_end(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $style = $this->createStyle($user);
        $client = $this->createClient($user);
        $order = $this->createOrder($user, $client);
        $order->styles()->attach($style->id);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/styles/{$style->id}");
        $message = $response->json('message');

        // Verify the exact new message (without trailing period, and without 'this')
        $this->assertSame('Cannot delete style. It is linked to one or more orders.', $message);
    }
}