<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NestedApiAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_another_users_client(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $client = $this->createClient($owner);

        Sanctum::actingAs($intruder);

        $this->getJson("/api/v1/clients/{$client->id}")
            ->assertForbidden()
            ->assertJson([
                'success' => false,
                'message' => 'This action is unauthorized.',
            ]);
    }

    public function test_nested_order_must_belong_to_client(): void
    {
        $user = User::factory()->create();
        $clientA = $this->createClient($user, 'Client A');
        $clientB = $this->createClient($user, 'Client B');
        $orderForClientB = $this->createOrder($user, $clientB);

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/clients/{$clientA->id}/orders/{$orderForClientB->id}")
            ->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Resource not found',
            ]);
    }

    public function test_nested_payment_can_be_created_for_owned_order(): void
    {
        $user = User::factory()->create();
        $client = $this->createClient($user);
        $order = $this->createOrder($user, $client, 500);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/clients/{$client->id}/orders/{$order->id}/payments", [
            'amount' => 150,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'reference' => 'PAY-001',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.amount', 150)
            ->assertJsonPath('data.order.id', $order->id);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'user_id' => $user->id,
            'reference' => 'PAY-001',
        ]);
    }

    public function test_nested_payment_must_belong_to_order(): void
    {
        $user = User::factory()->create();
        $client = $this->createClient($user);
        $firstOrder = $this->createOrder($user, $client, 500);
        $secondOrder = $this->createOrder($user, $client, 300);
        $paymentForSecondOrder = Payment::create([
            'order_id' => $secondOrder->id,
            'user_id' => $user->id,
            'amount' => 50,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/clients/{$client->id}/orders/{$firstOrder->id}/payments/{$paymentForSecondOrder->id}")
            ->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Resource not found',
            ]);
    }

    private function createClient(User $user, string $name = 'Test Client'): Client
    {
        return Client::create([
            'user_id' => $user->id,
            'name' => $name,
            'email' => strtolower(str_replace(' ', '.', $name)) . '@example.com',
            'phone' => fake()->unique()->numerify('080########'),
            'gender' => Gender::MALE,
        ]);
    }

    private function createOrder(User $user, Client $client, float $totalAmount = 250): Order
    {
        return Order::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'title' => 'Custom Outfit',
            'description' => 'Tailored order',
            'quantity' => 1,
            'total_amount' => $totalAmount,
            'status' => 'pending_payment',
            'due_date' => now()->addWeek()->toDateString(),
        ]);
    }
}
