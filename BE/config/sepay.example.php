<?php

return [
    'api_token' => env('SEPAY_API_TOKEN', ''),
    'api_base_url' => env('SEPAY_API_BASE_URL', 'https://qr.sepay.vn'),
    'bank_account' => env('SEPAY_BANK_ACCOUNT', ''),
    'bank_code' => env('SEPAY_BANK_CODE', ''),
    'account_name' => env('SEPAY_ACCOUNT_NAME', ''),
    'webhook_secret' => env('SEPAY_WEBHOOK_SECRET', ''),
];
