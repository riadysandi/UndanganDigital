<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Plan;
use App\Services\Payment\SandboxPaymentGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function __construct(
        protected SandboxPaymentGateway $paymentGateway
    ) {}

    public function index(Request $request): Response
    {
        $plans = Plan::with('prices')->where('is_active', true)->get();

        return Inertia::render('Billing/Pricing', [
            'plans' => $plans,
            'currentSubscription' => $request->user() ? $request->user()->hasActivePremium() : false,
        ]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
            'invitation_id' => ['nullable', 'exists:invitations,id'],
        ]);

        $user = $request->user();
        $plan = Plan::with('prices')->findOrFail($request->plan_id);
        $price = $plan->prices->first();

        if (!$price || $price->amount <= 0) {
            return back()->withErrors(['plan_id' => 'Paket ini tidak memerlukan pembayaran.']);
        }

        $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));

        $order = Order::create([
            'user_id' => $user->id,
            'invitation_id' => $request->invitation_id,
            'plan_id' => $plan->id,
            'order_number' => $orderNumber,
            'order_type' => 'one_time',
            'amount' => $price->amount,
            'currency' => $price->currency,
            'status' => 'pending',
            'product_snapshot_json' => [
                'plan_name' => $plan->name,
                'plan_code' => $plan->code,
                'features' => $plan->features_json,
                'price_amount' => $price->amount,
                'billing_interval' => $price->billing_interval,
            ],
        ]);

        // Inisialisasi payment gateway transaction
        $paymentData = $this->paymentGateway->createTransaction($order);

        return redirect(route('billing.pay', $order->id));
    }

    public function pay(Request $request, Order $order): Response
    {
        if ($order->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403);
        }

        $order->load(['plan', 'payments']);

        return Inertia::render('Billing/Pay', [
            'order' => $order,
            'payment' => $order->payments->last(),
        ]);
    }

    public function simulatePayment(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403);
        }

        $result = $this->paymentGateway->handleWebhook([
            'event_id' => 'sim_' . Str::random(12),
            'order_number' => $order->order_number,
            'status' => 'paid',
            'amount' => $order->amount,
            'paid_at' => now()->toIso8601String(),
        ]);

        return response()->json($result);
    }

    public function webhook(Request $request, string $provider): JsonResponse
    {
        $payload = $request->all();
        $signature = $request->header('X-Webhook-Signature');

        $result = $this->paymentGateway->handleWebhook($payload, $signature);

        return response()->json($result);
    }
}
