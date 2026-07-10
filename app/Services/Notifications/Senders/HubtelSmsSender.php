<?php

namespace App\Services\Notifications\Senders;

use App\Services\Notifications\Contracts\SmsSender;
use App\Services\Notifications\Data\NotificationResult;
use App\Services\Notifications\Data\SmsMessage;
use App\Services\Notifications\Exceptions\NotificationConfigurationException;
use App\Services\Notifications\Exceptions\PermanentNotificationException;
use App\Services\Notifications\Exceptions\TransientNotificationException;
use App\Services\Notifications\Support\PhoneNumber;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

final class HubtelSmsSender implements SmsSender
{
    public function send(SmsMessage $message): NotificationResult
    {
        $this->ensureConfigured();

        $to = PhoneNumber::normalize($message->to, config('notifications.sms.default_country_code'));
        $content = trim($message->message);

        if ($content === '') {
            throw new PermanentNotificationException('SMS message body is required.', 'missing_sms_body');
        }

        if (mb_strlen($content) > (int) config('notifications.sms.max_length')) {
            throw new PermanentNotificationException('SMS message is longer than the configured multipart limit.', 'sms_too_long');
        }

        try {
            $response = Http::withBasicAuth(config('services.hubtel.client_id'), config('services.hubtel.client_secret'))
                ->acceptJson()
                ->timeout((int) config('notifications.sms.timeout'))
                ->post(config('services.hubtel.base_url'), [
                    'From' => config('services.hubtel.sender_id'),
                    'To' => ltrim($to, '+'),
                    'Content' => $content,
                ]);
        } catch (ConnectionException $exception) {
            throw new TransientNotificationException('SMS provider connection failed.', 'sms_connection_failed', $exception);
        }

        if ($response->serverError() || $response->status() === 429) {
            throw new TransientNotificationException('SMS provider is temporarily unavailable.', 'sms_provider_transient_failure');
        }

        if ($response->failed()) {
            throw new PermanentNotificationException('SMS provider rejected the message.', 'sms_provider_rejected');
        }

        $payload = $response->json() ?? [];
        $messageId = data_get($payload, 'messageId')
            ?? data_get($payload, 'MessageId')
            ?? data_get($payload, 'data.messageId')
            ?? Str::after((string) data_get($payload, 'status.description'), 'ID: ');

        return NotificationResult::sent(
            provider: 'hubtel',
            providerMessageId: filled($messageId) ? (string) $messageId : null,
            idempotencyKey: $message->idempotencyKey,
        );
    }

    private function ensureConfigured(): void
    {
        foreach (['client_id', 'client_secret', 'sender_id', 'base_url'] as $key) {
            if (blank(config("services.hubtel.{$key}"))) {
                throw new NotificationConfigurationException("Hubtel {$key} is not configured.", "missing_hubtel_{$key}");
            }
        }
    }
}
