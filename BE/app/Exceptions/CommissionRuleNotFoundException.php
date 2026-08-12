<?php

namespace App\Exceptions;

class CommissionRuleNotFoundException extends BusinessException
{
    public function __construct(string $message = 'Commission rule not found for the given sale source.', int $statusCode = 404)
    {
        parent::__construct($message, $statusCode);
    }
}
