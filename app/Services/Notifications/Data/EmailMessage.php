<?php

namespace App\Services\Notifications\Data;

final readonly class EmailMessage
{
    /**
     * @param  array<string, mixed>  $templateData
     * @param  array<int, array{path: string, as?: string}>  $attachments
     */
    public function __construct(
        public string $to,
        public string $subject,
        public ?string $text = null,
        public ?string $html = null,
        public ?string $template = null,
        public array $templateData = [],
        public ?string $replyTo = null,
        public ?string $idempotencyKey = null,
        public array $attachments = [],
    ) {}
}
