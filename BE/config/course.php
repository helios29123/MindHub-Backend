<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Course Pricing Configuration
    |--------------------------------------------------------------------------
    |
    | Centralized configuration for course pricing and promotion limits.
    |
    */
    'min_price' => (float) env('COURSE_MIN_PRICE', 50000),
    'max_discount_percentage' => (float) env('COURSE_MAX_DISCOUNT_PERCENTAGE', 70),
];
