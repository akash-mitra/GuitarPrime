<?php

namespace App\Services\Payment;

use App\Jobs\ProcessRazorpayWebhook;
use App\Models\Purchase;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\BadRequestError;

class RazorpayPaymentService implements PaymentServiceInterface
{
    protected Api $api;

    public function __construct()
    {
        $this->api = new Api(
            config('services.razorpay.key_id'),
            config('services.razorpay.key_secret')
        );
    }

    public function createPayment(Purchase $purchase): array
    {
        try {
            $purchasable = $purchase->purchasable;

            $orderData = [
                'amount' => (int) ($purchase->amount * 100), // Convert to paise
                'currency' => $purchase->currency,
                'receipt' => 'purchase_'.$purchase->id,
                'notes' => [
                    'purchase_id' => $purchase->id,
                    'user_id' => $purchase->user_id,
                    'purchasable_type' => $purchase->purchasable_type,
                    'purchasable_id' => $purchase->purchasable_id,
                    'title' => $purchasable->title,
                ],
            ];

            $order = $this->api->order->create($orderData);

            $purchase->update([
                'payment_id' => $order->id,
                'metadata' => array_merge($purchase->metadata ?? [], [
                    'razorpay_order' => $order->toArray(),
                ]),
            ]);

            return [
                'success' => true,
                'order_id' => $order->id,
                'amount' => $order->amount,
                'currency' => $order->currency,
                'key_id' => config('services.razorpay.key_id'),
                'name' => config('app.name'),
                'description' => $purchasable->title,
                'prefill' => [
                    'name' => $purchase->user->name,
                    'email' => $purchase->user->email,
                ],
                'theme' => [
                    'color' => '#3399cc',
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay order creation failed', [
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function retrievePayment(string $paymentId): array
    {
        try {
            $payment = $this->api->payment->fetch($paymentId);

            return [
                'success' => true,
                'payment' => $payment,
                'status' => $payment->status,
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay payment retrieval failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function verifyPayment(string $paymentId, string $orderId, string $signature): bool
    {
        try {
            $attributes = [
                'razorpay_order_id' => $orderId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature,
            ];

            $this->api->utility->verifyPaymentSignature($attributes);

            return true;
        } catch (BadRequestError $e) {
            Log::error('Razorpay payment verification failed', [
                'payment_id' => $paymentId,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function handleWebhook(array $payload, string $signature): bool
    {
        try {
            if (! $this->verifyWebhookSignature($payload, $signature)) {
                Log::warning('Invalid Razorpay webhook signature');

                return false;
            }

            if ($payload['event'] === 'payment.captured') {
                ProcessRazorpayWebhook::dispatch($payload['payload']['payment']['entity']);

                return true;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Razorpay webhook handling failed', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return false;
        }
    }

    public function verifyWebhookSignature(array $payload, string $signature): bool
    {
        try {
            $webhookSecret = config('services.razorpay.webhook_secret');

            if (! $webhookSecret) {
                Log::warning('Razorpay webhook secret not configured');

                return false;
            }

            $expectedSignature = hash_hmac('sha256', json_encode($payload), $webhookSecret);

            return hash_equals($expectedSignature, $signature);
        } catch (\Exception $e) {
            Log::error('Razorpay webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
