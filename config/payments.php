<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for payment processing in the application.
    |
    */

    'default_currency' => env('PAYMENT_DEFAULT_CURRENCY', 'USD'),

    'supported_currencies' => [
        'USD', 'EUR', 'GBP', 'INR', 'CAD', 'AUD',
    ],

    'providers' => [
        'stripe' => [
            'enabled' => env('STRIPE_ENABLED', true),
            'name' => 'Stripe',
            'currencies' => ['USD', 'EUR', 'GBP', 'CAD', 'AUD'],
        ],
        'razorpay' => [
            'enabled' => env('RAZORPAY_ENABLED', true),
            'name' => 'Razorpay',
            'currencies' => ['INR'],
        ],
    ],

    'webhook_tolerance' => env('PAYMENT_WEBHOOK_TOLERANCE', 300), // 5 minutes

    'purchase_limits' => [
        'max_amount' => env('PAYMENT_MAX_AMOUNT', 99999.99),
        'min_amount' => env('PAYMENT_MIN_AMOUNT', 0.50),
    ],

];
