<?php

namespace App\Services\Notifications\Data;

final readonly class SmsMessage
{
    public function __construct(
        public string $to,
        public string $message,
        public ?string $idempotencyKey = null,
    ) {}
}
