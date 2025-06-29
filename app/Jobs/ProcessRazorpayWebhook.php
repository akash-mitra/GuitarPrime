<?php

namespace App\Jobs;

use App\Models\Purchase;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessRazorpayWebhook implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private array $payment
    ) {
        //
    }

    public function handle(): void
    {
        try {
            $orderId = $this->payment['order_id'] ?? null;

            if (! $orderId) {
                Log::warning('Razorpay webhook: Order ID not found in payment data', [
                    'payment_id' => $this->payment['id'] ?? 'unknown',
                ]);

                return;
            }

            $purchase = Purchase::where('payment_id', $orderId)->first();

            if (! $purchase) {
                Log::warning('Razorpay webhook: Purchase not found', [
                    'order_id' => $orderId,
                    'payment_id' => $this->payment['id'] ?? 'unknown',
                ]);

                return;
            }

            if ($purchase->isCompleted()) {
                Log::info('Razorpay webhook: Purchase already completed', [
                    'purchase_id' => $purchase->id,
                ]);

                return;
            }

            if ($this->payment['status'] === 'captured') {
                $purchase->update([
                    'status' => 'completed',
                    'payment_id' => $this->payment['id'],
                    'metadata' => array_merge($purchase->metadata ?? [], [
                        'razorpay_payment_captured_at' => now(),
                        'razorpay_payment_data' => $this->payment,
                        'razorpay_amount_captured' => $this->payment['amount'] ?? null,
                    ]),
                ]);

                Log::info('Razorpay webhook: Purchase completed successfully', [
                    'purchase_id' => $purchase->id,
                    'payment_id' => $this->payment['id'],
                ]);
            } else {
                Log::warning('Razorpay webhook: Payment not captured', [
                    'purchase_id' => $purchase->id,
                    'payment_id' => $this->payment['id'] ?? 'unknown',
                    'payment_status' => $this->payment['status'] ?? 'unknown',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Razorpay webhook processing failed', [
                'payment_id' => $this->payment['id'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
