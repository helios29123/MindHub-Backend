<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Refund Hold Period
    |--------------------------------------------------------------------------
    | Number of days before a paid order revenue matures from pending to available.
    */
    'refund_hold_days' => 30,

    /*
    |--------------------------------------------------------------------------
    | Automatic Periodic Payout Settings (Default Flow)
    |--------------------------------------------------------------------------
    | Monthly automatic payout schedule for eligible mature revenues.
    */
    'payout' => [
        'enabled' => true,
        'frequency' => 'monthly',
        'minimum_amount' => 200000,
        'window_start_day' => 5,
        'window_end_day' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Early Withdrawal Settings (On-Demand Flow with OTP)
    |--------------------------------------------------------------------------
    | Strict rules and limits for optional instructor early payment requests.
    */
    'early_withdrawal' => [
        'enabled' => true,
        'minimum_amount' => 200000,
        'maximum_per_request' => null,
        'maximum_active_requests' => 1,
        'maximum_requests_per_month' => 2,
        'cooldown_days' => 7,
        'bank_account_change_hold_hours' => 48,
        'automatic_payout_lock_days' => 3,
        'otp_expires_minutes' => 5,
        'otp_resend_seconds' => 60,
        'otp_max_attempts' => 5,
    ],
];
