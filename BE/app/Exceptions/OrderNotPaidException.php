<?php

namespace App\Exceptions;

class OrderNotPaidException extends BusinessException
{
    public function __construct(string $message = 'Revenue can only be generated for paid orders.', int $statusCode = 409)
    {
        parent::__construct($message, $statusCode);
    }
}
