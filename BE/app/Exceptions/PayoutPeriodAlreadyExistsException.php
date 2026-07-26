<?php

namespace App\Exceptions;

class PayoutPeriodAlreadyExistsException extends BusinessException
{
    public function __construct(string $message = 'Payout statement already exists for this instructor and period.', int $statusCode = 409)
    {
        parent::__construct($message, $statusCode);
    }
}
