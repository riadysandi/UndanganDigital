<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;

interface PaymentGatewayInterface
{
    /**
     * Membuat transaksi pembayaran untuk sebuah order.
     */
    public function createTransaction(Order $order): array;

    /**
     * Memproses webhook callback dari payment gateway secara aman dan idempotent.
     */
    public function handleWebhook(array $payload, ?string $signature = null): array;
}
