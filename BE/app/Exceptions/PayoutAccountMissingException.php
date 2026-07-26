<?php

namespace App\Exceptions;

class PayoutAccountMissingException extends BusinessException
{
    public function __construct(string $message = 'Instructor missing default payout account.', int $statusCode = 422)
    {
        parent::__construct($message, $statusCode);
    }
}
