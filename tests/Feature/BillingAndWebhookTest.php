<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingAndWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_customer_can_checkout_premium_plan(): void
    {
        $user = User::where('email', 'pengantin@undangan.id')->first();
        $goldPlan = Plan::where('code', 'gold')->first();

        $response = $this->actingAs($user)->post('/checkout', [
            'plan_id' => $goldPlan->id,
        ]);

        $order = Order::where('user_id', $user->id)->where('plan_id', $goldPlan->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals('pending', $order->status);
        $response->assertRedirect(route('billing.pay', $order->id));
    }

    public function test_webhook_processing_is_idempotent(): void
    {
        $user = User::where('email', 'pengantin@undangan.id')->first();
        $goldPlan = Plan::where('code', 'gold')->first();

        $order = Order::create([
            'user_id' => $user->id,
            'plan_id' => $goldPlan->id,
            'order_number' => 'ORD-TEST-999',
            'order_type' => 'one_time',
            'amount' => 149000,
            'currency' => 'IDR',
            'status' => 'pending',
            'product_snapshot_json' => ['plan' => 'gold'],
        ]);

        $payload = [
            'event_id' => 'evt_test_123456',
            'order_number' => $order->order_number,
            'status' => 'paid',
            'amount' => 149000,
        ];

        // Webhook pertama kali diterima
        $response1 = $this->postJson(route('webhook', 'sandbox'), $payload);
        $response1->assertOk();
        $this->assertEquals('paid', $order->fresh()->status);
        $this->assertDatabaseHas('webhook_events', [
            'provider_event_id' => 'evt_test_123456',
            'status' => 'processed',
        ]);

        // Webhook kedua (replay / duplicate delivery)
        $response2 = $this->postJson(route('webhook', 'sandbox'), $payload);
        $response2->assertOk();
        $response2->assertJsonFragment([
            'message' => 'Event already processed (idempotent)',
        ]);
    }
}
