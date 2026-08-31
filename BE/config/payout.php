<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payout Gateway Driver
    |--------------------------------------------------------------------------
    */
    'driver' => env('PAYOUT_DRIVER', 'fake'),

    'fake' => [
        'result' => env('FAKE_PAYOUT_RESULT', 'success'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Automatic Periodic Payout Settings (Default Flow)
    |--------------------------------------------------------------------------
    | Monthly automatic payout schedule for eligible mature revenues.
    | Minimum payout amount threshold and monthly processing window.
    */
    'minimum_amount' => (float) env('PAYOUT_MINIMUM_AMOUNT', 200000),
    'window_start_day' => (int) env('PAYOUT_WINDOW_START_DAY', 5),
    'window_end_day' => (int) env('PAYOUT_WINDOW_END_DAY', 10),

    /*
    |--------------------------------------------------------------------------
    | Early Withdrawal Settings (On-Demand Flow with OTP)
    |--------------------------------------------------------------------------
    | Strict rules and limits for optional instructor early payment requests.
    | Independent minimum amount threshold from periodic payout.
    */
    'early_withdrawal' => [
        'enabled' => (bool) env('EARLY_WITHDRAWAL_ENABLED', true),
        'minimum_amount' => (float) env('EARLY_WITHDRAWAL_MINIMUM_AMOUNT', 200000),
        'otp_expires_minutes' => (int) env('EARLY_WITHDRAWAL_OTP_EXPIRES_MINUTES', 5),
        'otp_resend_seconds' => (int) env('EARLY_WITHDRAWAL_OTP_RESEND_SECONDS', 60),
        'otp_max_attempts' => (int) env('EARLY_WITHDRAWAL_OTP_MAX_ATTEMPTS', 5),
    ],
];
