<?php

namespace App\Services\Payment;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Payment;
use App\Models\WebhookEvent;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SandboxPaymentGateway implements PaymentGatewayInterface
{
    public function createTransaction(Order $order): array
    {
        $transactionId = 'SANDBOX-' . strtoupper(Str::random(12));

        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'sandbox',
            'provider_transaction_id' => $transactionId,
            'status' => 'pending',
            'amount' => $order->amount,
            'currency' => $order->currency,
            'metadata_json' => [
                'payment_type' => 'qris_or_va',
                'qris_mock_string' => '00020101021226670016ID.CO.SANDBOX.WWW01189360099800000000005204581253033605802ID5914UndanganDigital6007Jakarta62070703A016304B7A9',
                'va_number' => '8808' . rand(10000000, 99999999),
            ],
        ]);

        return [
            'transaction_id' => $transactionId,
            'payment_id' => $payment->id,
            'redirect_url' => url("/orders/{$order->id}/pay"),
            'va_number' => $payment->metadata_json['va_number'],
            'qris_mock' => $payment->metadata_json['qris_mock_string'],
        ];
    }

    public function handleWebhook(array $payload, ?string $signature = null): array
    {
        $eventId = $payload['event_id'] ?? 'evt_' . md5(json_encode($payload));
        $transactionId = $payload['transaction_id'] ?? null;
        $orderNumber = $payload['order_number'] ?? null;
        $status = $payload['status'] ?? 'paid'; // 'paid', 'expired', 'failed'

        // 1. Idempotency Check: Pastikan event_id belum pernah diproses
        $existingEvent = WebhookEvent::where('provider_event_id', $eventId)->first();
        if ($existingEvent) {
            return [
                'success' => true,
                'message' => 'Event already processed (idempotent)',
                'event_id' => $eventId,
            ];
        }

        return DB::transaction(function () use ($eventId, $transactionId, $orderNumber, $status, $payload) {
            // Catat webhook event
            $webhookEvent = WebhookEvent::create([
                'provider' => 'sandbox',
                'provider_event_id' => $eventId,
                'event_type' => 'payment.' . $status,
                'status' => 'processed',
                'sanitized_payload_json' => $payload,
                'processed_at' => now(),
            ]);

            // Cari order
            $order = Order::where('order_number', $orderNumber)->first();
            if (!$order) {
                $webhookEvent->update(['status' => 'ignored']);
                return ['success' => false, 'message' => 'Order not found'];
            }

            // Update status payment & order
            if ($status === 'paid' || $status === 'settlement') {
                $order->update(['status' => 'paid']);

                $payment = Payment::where('order_id', $order->id)->latest()->first();
                if ($payment) {
                    $payment->update([
                        'status' => 'settlement',
                        'paid_at' => now(),
                    ]);
                }

                // Audit log
                AuditLog::create([
                    'actor_user_id' => $order->user_id,
                    'action' => 'order.paid',
                    'subject_type' => Order::class,
                    'subject_id' => $order->id,
                    'metadata_json' => [
                        'order_number' => $order->order_number,
                        'amount' => $order->amount,
                        'event_id' => $eventId,
                    ],
                ]);
            } elseif ($status === 'expired') {
                $order->update(['status' => 'expired']);
            }

            return [
                'success' => true,
                'message' => "Order {$orderNumber} successfully updated to {$status}",
                'order_id' => $order->id,
            ];
        });
    }
}
