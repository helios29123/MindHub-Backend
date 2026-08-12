<?php

namespace App\Exceptions;

class InvalidOrderAmountException extends BusinessException
{
    public function __construct(string $message = 'Order amount is invalid or negative for revenue calculation.', int $statusCode = 422)
    {
        parent::__construct($message, $statusCode);
    }
}
