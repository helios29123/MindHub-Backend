<?php

return [
    'driver' => env('PAYOUT_DRIVER', 'fake'),

    'fake' => [
        'result' => env('FAKE_PAYOUT_RESULT', 'success'),
    ],
];
