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

class ClientOrderStyleControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createClient(User $user, string $name = 'Client'): Client
    {
        return Client::create([
            'user_id' => $user->id,
            'name' => $name,
            'email' => strtolower(str_replace(' ', '.', $name)) . fake()->unique()->numerify('##') . '@example.com',
            'phone' => fake()->unique()->numerify('080########'),
            'gender' => Gender::FEMALE,
        ]);
    }

    private function createOrder(User $user, Client $client): Order
    {
        return Order::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'title' => 'Test Order',
            'quantity' => 1,
            'total_amount' => 200.00,
            'status' => 'pending',
        ]);
    }

    private function createStyle(User $user): Style
    {
        Storage::fake('public');

        return Style::create([
            'user_id' => $user->id,
            'title' => 'A Style',
            'description' => 'desc',
            'image_path' => 'styles/sample.jpg',
            'category' => 'dress',
            'tags' => [],
        ]);
    }

    // ─── attach ───────────────────────────────────────────────────────────────

    public function test_attach_links_style_to_order_successfully(): void
    {
        $user = User::factory()->create();
        $client = $this->createClient($user);
        $order = $this->createOrder($user, $client);
        $style = $this->createStyle($user);

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/clients/{$client->id}/orders/{$order->id}/styles", [
            'style_id' => $style->id,
        ])->assertStatus(201)
          ->assertJson([
              'success' => true,
              'message' => 'Style linked to order successfully',
          ]);

        $this->assertDatabaseHas('order_style', [
            'order_id' => $order->id,
            'style_id' => $style->id,
        ]);
    }

    public function test_attach_returns_400_when_style_already_linked(): void
    {
        $user = User::factory()->create();
        $client = $this->createClient($user);
        $order = $this->createOrder($user, $client);
        $style = $this->createStyle($user);

        $order->styles()->attach($style->id);

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/clients/{$client->id}/orders/{$order->id}/styles", [
            'style_id' => $style->id,
        ])->assertStatus(400)
          ->assertJson([
              'success' => false,
              'message' => 'Style is already linked to this order',
          ]);
    }

    public function test_attach_returns_validation_error_for_missing_style_id(): void
    {
        $user = User::factory()->create();
        $client = $this->createClient($user);
        $order = $this->createOrder($user, $client);

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/clients/{$client->id}/orders/{$order->id}/styles", [])
            ->assertStatus(422);
    }

    public function test_attach_returns_validation_error_for_style_owned_by_another_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $client = $this->createClient($user);
        $order = $this->createOrder($user, $client);
        $otherStyle = $this->createStyle($otherUser);

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/clients/{$client->id}/orders/{$order->id}/styles", [
            'style_id' => $otherStyle->id,
        ])->assertStatus(422)
          ->assertJsonPath('errors.style_id', 'Style not found');
    }

    public function test_attach_prevents_duplicate_pivot_entries(): void
    {
        $user = User::factory()->create();
        $client = $this->createClient($user);
        $order = $this->createOrder($user, $client);
        $style = $this->createStyle($user);

        Sanctum::actingAs($user);

        // First attach
        $this->postJson("/api/v1/clients/{$client->id}/orders/{$order->id}/styles", [
            'style_id' => $style->id,
        ])->assertStatus(201);

        // Second attach - should be blocked
        $this->postJson("/api/v1/clients/{$client->id}/orders/{$order->id}/styles", [
            'style_id' => $style->id,
        ])->assertStatus(400);

        $count = \DB::table('order_style')
            ->where('order_id', $order->id)
            ->where('style_id', $style->id)
            ->count();

        $this->assertSame(1, $count);
    }

    // ─── detach ───────────────────────────────────────────────────────────────

    public function test_detach_unlinks_style_from_order(): void
    {
        $user = User::factory()->create();
        $client = $this->createClient($user);
        $order = $this->createOrder($user, $client);
        $style = $this->createStyle($user);

        $order->styles()->attach($style->id);

        Sanctum::actingAs($user);

        $this->deleteJson("/api/v1/clients/{$client->id}/orders/{$order->id}/styles/{$style->id}")
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Style unlinked from order successfully',
            ]);

        $this->assertDatabaseMissing('order_style', [
            'order_id' => $order->id,
            'style_id' => $style->id,
        ]);
    }

    public function test_detach_returns_404_when_style_not_linked_to_order(): void
    {
        $user = User::factory()->create();
        $client = $this->createClient($user);
        $order = $this->createOrder($user, $client);
        $style = $this->createStyle($user);

        // Style exists but is NOT attached to the order
        Sanctum::actingAs($user);

        $this->deleteJson("/api/v1/clients/{$client->id}/orders/{$order->id}/styles/{$style->id}")
            ->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Style is not linked to this order',
            ]);
    }

    public function test_detach_returns_404_for_style_owned_by_another_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $client = $this->createClient($user);
        $order = $this->createOrder($user, $client);
        $otherStyle = $this->createStyle($otherUser);

        Sanctum::actingAs($user);

        $this->deleteJson("/api/v1/clients/{$client->id}/orders/{$order->id}/styles/{$otherStyle->id}")
            ->assertNotFound();
    }

    public function test_detach_error_message_has_no_trailing_period(): void
    {
        $user = User::factory()->create();
        $client = $this->createClient($user);
        $order = $this->createOrder($user, $client);
        $style = $this->createStyle($user);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/clients/{$client->id}/orders/{$order->id}/styles/{$style->id}");
        $message = $response->json('message');

        $this->assertSame('Style is not linked to this order', $message);
    }
}