<?php

namespace App\Services\Payment;

use App\Jobs\ProcessStripeWebhook;
use App\Models\Purchase;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

class StripePaymentService implements PaymentServiceInterface
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createPayment(Purchase $purchase): array
    {
        try {
            $purchasable = $purchase->purchasable;

            $sessionData = [
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($purchase->currency),
                        'product_data' => [
                            'name' => $purchasable->title,
                            'description' => $purchasable->description ?? '',
                        ],
                        'unit_amount' => (int) ($purchase->amount * 100), // Convert to cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('purchase.success', ['purchase' => $purchase->id]).'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('purchase.cancel', ['purchase' => $purchase->id]),
                'metadata' => [
                    'purchase_id' => $purchase->id,
                    'user_id' => $purchase->user_id,
                    'purchasable_type' => $purchase->purchasable_type,
                    'purchasable_id' => $purchase->purchasable_id,
                ],
            ];

            $session = Session::create($sessionData);

            $purchase->update([
                'checkout_session_id' => $session->id,
                'metadata' => array_merge($purchase->metadata ?? [], [
                    'stripe_session_url' => $session->url,
                ]),
            ]);

            return [
                'success' => true,
                'checkout_url' => $session->url,
                'session_id' => $session->id,
            ];
        } catch (\Exception $e) {
            Log::error('Stripe payment creation failed', [
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function retrievePayment(string $sessionId): array
    {
        try {
            $session = Session::retrieve($sessionId);

            return [
                'success' => true,
                'session' => $session,
                'payment_status' => $session->payment_status,
            ];
        } catch (\Exception $e) {
            Log::error('Stripe session retrieval failed', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function handleWebhook(array $payload, string $signature): bool
    {
        try {
            if (! $this->verifyWebhookSignature($payload, $signature)) {
                Log::warning('Invalid Stripe webhook signature');

                return false;
            }

            $event = json_decode(json_encode($payload), false);

            if ($event->type === 'checkout.session.completed') {
                ProcessStripeWebhook::dispatch($event->data->object);

                return true;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Stripe webhook handling failed', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return false;
        }
    }

    public function verifyWebhookSignature(array $payload, string $signature): bool
    {
        try {
            $webhookSecret = config('services.stripe.webhook_secret');

            if (! $webhookSecret) {
                Log::warning('Stripe webhook secret not configured');

                return false;
            }

            Webhook::constructEvent(
                json_encode($payload),
                $signature,
                $webhookSecret
            );

            return true;
        } catch (SignatureVerificationException $e) {
            Log::error('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
