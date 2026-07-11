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

final class ArkeselSmsSender implements SmsSender
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
            $response = Http::withHeaders([
                'api-key' => config('services.arkesel.api_key'),
                'Content-Type' => 'application/json',
            ])
                ->acceptJson()
                ->timeout((int) config('notifications.sms.timeout'))
                ->post(config('services.arkesel.base_url'), [
                    'sender' => config('services.arkesel.sender_id'),
                    'message' => $content,
                    'recipients' => [ltrim($to, '+')],
                ]);
        } catch (ConnectionException $exception) {
            throw new TransientNotificationException('Arkesel SMS provider connection failed.', 'arkesel_connection_failed', $exception);
        }

        if ($response->serverError() || $response->status() === 429) {
            throw new TransientNotificationException('Arkesel SMS provider is temporarily unavailable.', 'arkesel_transient_failure');
        }

        if ($response->failed()) {
            throw new PermanentNotificationException('Arkesel SMS provider rejected the message. Check your API key, balance, sender ID, and phone number.', 'arkesel_rejected');
        }

        $payload = $response->json() ?? [];
        $messageId = data_get($payload, 'id')
            ?? data_get($payload, 'message_id')
            ?? data_get($payload, 'data.id')
            ?? data_get($payload, 'data.message_id')
            ?? data_get($payload, 'data.0.id');

        return NotificationResult::sent(
            provider: 'arkesel',
            providerMessageId: filled($messageId) ? (string) $messageId : Str::uuid()->toString(),
            idempotencyKey: $message->idempotencyKey,
        );
    }

    private function ensureConfigured(): void
    {
        foreach (['api_key', 'sender_id', 'base_url'] as $key) {
            if (blank(config("services.arkesel.{$key}"))) {
                throw new NotificationConfigurationException("Arkesel {$key} is not configured. Add ARKESEL_SMS_API_KEY and ARKESEL_SMS_SENDER_ID in Railway before SMS can be delivered.", "missing_arkesel_{$key}");
            }
        }
    }
}
