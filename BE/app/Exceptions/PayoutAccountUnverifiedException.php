<?php

namespace App\Exceptions;

class PayoutAccountUnverifiedException extends BusinessException
{
    public function __construct(string $message = 'Instructor payout account is not verified.', int $statusCode = 422)
    {
        parent::__construct($message, $statusCode);
    }
}
