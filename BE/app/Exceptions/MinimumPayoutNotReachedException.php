<?php

namespace App\Exceptions;

class MinimumPayoutNotReachedException extends BusinessException
{
    public function __construct(string $message = 'Available revenue is below minimum payout threshold.', int $statusCode = 422)
    {
        parent::__construct($message, $statusCode);
    }
}
