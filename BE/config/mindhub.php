<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pending Order Expiration
    |--------------------------------------------------------------------------
    |
    | Number of hours after which unpaid pending orders should be expired.
    | Keep this value between 1 and 168 to match console command validation.
    |
    */

    'pending_order_expire_hours' => (int) env('PENDING_ORDER_EXPIRE_HOURS', 24),
    'max_learner_sessions' => (int) env('MAX_LEARNER_SESSIONS', 2),
];