<?php

namespace App\Services\Notifications\Exceptions;

use RuntimeException;

abstract class NotificationException extends RuntimeException
{
    public function __construct(string $message, public readonly string $errorCode, ?\Throwable $previous = null)
    {
        parent::__construct($message, previous: $previous);
    }
}
