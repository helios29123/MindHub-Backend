<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pending Order Expiration
    |--------------------------------------------------------------------------
    |
    | Number of hours after which unpaid pending orders should be expired.
    | Default is 24 hours.
    |
    */
    'pending_expire_hours' => (int) env('PENDING_ORDER_EXPIRE_HOURS', 24),

    /*
    |--------------------------------------------------------------------------
    | Minimum Payable Order Amount
    |--------------------------------------------------------------------------
    |
    | Minimum payable amount required when creating a paid order.
    | Paid orders with non-zero amount must be at least this value (in VND).
    |
    */
    'minimum_payable_amount' => (int) env('ORDER_MINIMUM_PAYABLE_AMOUNT', 10000),
];
