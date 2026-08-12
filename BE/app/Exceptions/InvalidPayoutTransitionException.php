<?php

namespace App\Exceptions;

class InvalidPayoutTransitionException extends BusinessException
{
    public function __construct(string $message = 'Invalid payout status transition.', int $statusCode = 422)
    {
        parent::__construct($message, $statusCode);
    }
}
