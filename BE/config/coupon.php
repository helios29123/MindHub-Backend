<?php

return [
    'discount_max_percent' => (float) env('COUPON_DISCOUNT_MAX_PERCENT', 70),
    'trial_campaign_max_days' => (int) env('COUPON_TRIAL_CAMPAIGN_MAX_DAYS', 3),
    'trial_max_uses' => (int) env('COUPON_TRIAL_MAX_USES', 15),
    'trial_access_days' => (int) env('COUPON_TRIAL_ACCESS_DAYS', 7),
    'trial_campaigns_per_month' => (int) env('COUPON_TRIAL_CAMPAIGNS_PER_MONTH', 2),
];
