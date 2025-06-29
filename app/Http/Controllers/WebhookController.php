<?php

namespace App\Http\Controllers;

use App\Services\Payment\RazorpayPaymentService;
use App\Services\Payment\StripePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends Controller
{
    public function stripe(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('Stripe-Signature');

        if (! $signature) {
            Log::warning('Missing Stripe signature header');

            return response('Missing signature', Response::HTTP_BAD_REQUEST);
        }

        $service = new StripePaymentService;

        if ($service->handleWebhook($payload, $signature)) {
            return response('Webhook handled', Response::HTTP_OK);
        }

        return response('Webhook failed', Response::HTTP_BAD_REQUEST);
    }

    public function razorpay(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('X-Razorpay-Signature');

        if (! $signature) {
            Log::warning('Missing Razorpay signature header');

            return response('Missing signature', Response::HTTP_BAD_REQUEST);
        }

        $service = new RazorpayPaymentService;

        if ($service->handleWebhook($payload, $signature)) {
            return response('Webhook handled', Response::HTTP_OK);
        }

        return response('Webhook failed', Response::HTTP_BAD_REQUEST);
    }
}
