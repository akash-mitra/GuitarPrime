<?php

namespace App\Services\Payment;

use App\Models\Purchase;

interface PaymentServiceInterface
{
    public function createPayment(Purchase $purchase): array;

    public function retrievePayment(string $paymentId): array;

    public function handleWebhook(array $payload, string $signature): bool;

    public function verifyWebhookSignature(array $payload, string $signature): bool;
}
