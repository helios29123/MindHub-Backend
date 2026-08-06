<?php

return [
    'api_token' => env('SEPAY_API_TOKEN', 'SGCXJKTQVNDBTE3RAAZR5RBC68YISKXXUFV0KALWSZ2TWR5ISGFMIQUNUWB2A7HH'),
    'api_base_url' => env('SEPAY_API_BASE_URL', 'https://qr.sepay.vn'),
    'bank_account' => env('SEPAY_BANK_ACCOUNT', '67363636363'),
    'bank_code' => env('SEPAY_BANK_CODE', 'TPBANK'),
    'account_name' => env('SEPAY_ACCOUNT_NAME', 'LE DUY'),
    'webhook_secret' => env('SEPAY_WEBHOOK_SECRET', 'whsec_IBzGhpipWDlrzbeID8gsP1ffT3gkSyjw'),
];
