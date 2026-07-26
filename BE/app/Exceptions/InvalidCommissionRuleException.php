<?php

namespace App\Exceptions;

class InvalidCommissionRuleException extends BusinessException
{
    public function __construct(string $message = 'Invalid commission rule rates. Instructor and platform rates must sum to 100.', int $statusCode = 422)
    {
        parent::__construct($message, $statusCode);
    }
}
