<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Refund Hold Period
    |--------------------------------------------------------------------------
    | Number of days before a paid order revenue matures from pending to available.
    */
    'refund_hold_days' => (int) env('REVENUE_REFUND_HOLD_DAYS', 30),
];
