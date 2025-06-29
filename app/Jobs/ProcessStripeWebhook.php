<?php

namespace App\Jobs;

use App\Models\Purchase;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessStripeWebhook implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private object $session
    ) {
        //
    }

    public function handle(): void
    {
        try {
            $purchaseId = $this->session->metadata->purchase_id ?? null;

            if (! $purchaseId) {
                Log::warning('Stripe webhook: Purchase ID not found in session metadata', [
                    'session_id' => $this->session->id,
                ]);

                return;
            }

            $purchase = Purchase::find($purchaseId);

            if (! $purchase) {
                Log::warning('Stripe webhook: Purchase not found', [
                    'purchase_id' => $purchaseId,
                    'session_id' => $this->session->id,
                ]);

                return;
            }

            if ($purchase->isCompleted()) {
                Log::info('Stripe webhook: Purchase already completed', [
                    'purchase_id' => $purchaseId,
                ]);

                return;
            }

            if ($this->session->payment_status === 'paid') {
                $purchase->update([
                    'status' => 'completed',
                    'payment_id' => $this->session->payment_intent,
                    'metadata' => array_merge($purchase->metadata ?? [], [
                        'stripe_session_completed_at' => now(),
                        'stripe_payment_intent' => $this->session->payment_intent,
                        'stripe_customer' => $this->session->customer,
                    ]),
                ]);

                Log::info('Stripe webhook: Purchase completed successfully', [
                    'purchase_id' => $purchaseId,
                    'payment_intent' => $this->session->payment_intent,
                ]);
            } else {
                Log::warning('Stripe webhook: Session not paid', [
                    'purchase_id' => $purchaseId,
                    'session_id' => $this->session->id,
                    'payment_status' => $this->session->payment_status,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Stripe webhook processing failed', [
                'session_id' => $this->session->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
