<?php

namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    public function __construct(
        string $message,
        private readonly int $statusCode = 400,
        private readonly array $errors = []
    ) {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
