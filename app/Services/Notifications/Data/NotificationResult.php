<?php

namespace App\Services\Notifications\Data;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

final readonly class NotificationResult
{
    public function __construct(
        public bool $success,
        public string $status,
        public string $internalId,
        public string $provider,
        public Carbon $timestamp,
        public ?string $providerMessageId = null,
        public ?string $idempotencyKey = null,
        public ?string $errorCode = null,
    ) {}

    public static function sent(string $provider, ?string $providerMessageId = null, ?string $idempotencyKey = null): self
    {
        return new self(
            success: true,
            status: 'sent',
            internalId: (string) Str::uuid(),
            provider: $provider,
            timestamp: now(),
            providerMessageId: $providerMessageId,
            idempotencyKey: $idempotencyKey,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'status' => $this->status,
            'internal_id' => $this->internalId,
            'provider' => $this->provider,
            'provider_message_id' => $this->providerMessageId,
            'idempotency_key' => $this->idempotencyKey,
            'error_code' => $this->errorCode,
            'timestamp' => $this->timestamp->toIso8601String(),
        ];
    }
}
