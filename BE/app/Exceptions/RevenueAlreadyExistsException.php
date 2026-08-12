<?php

namespace App\Exceptions;

class RevenueAlreadyExistsException extends BusinessException
{
    public function __construct(string $message = 'Revenue record already exists for this order.', int $statusCode = 409)
    {
        parent::__construct($message, $statusCode);
    }
}
