<?php

namespace App\Exceptions;

class CourseInstructorMissingException extends BusinessException
{
    public function __construct(string $message = 'Order is missing valid course or instructor information.', int $statusCode = 422)
    {
        parent::__construct($message, $statusCode);
    }
}
