<?php

namespace App\Services;

use App\Services\Notifications\Contracts\SmsSender;
use App\Services\Notifications\Data\SmsMessage;

class HubtelSmsService
{
    public function __construct(private readonly SmsSender $sms) {}

    /**
     * @return array<string, mixed>
     */
    public function send(string $to, string $message): array
    {
        return $this->sms->send(new SmsMessage($to, $message))->toArray();
    }
}
