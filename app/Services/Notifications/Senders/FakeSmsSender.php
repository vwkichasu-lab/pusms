<?php

namespace App\Services\Notifications\Senders;

use App\Services\Notifications\Contracts\SmsSender;
use App\Services\Notifications\Data\NotificationResult;
use App\Services\Notifications\Data\SmsMessage;
use App\Services\Notifications\Exceptions\TransientNotificationException;
use App\Services\Notifications\Support\PhoneNumber;

final class FakeSmsSender implements SmsSender
{
    /** @var array<int, SmsMessage> */
    public static array $sent = [];

    public static bool $failTransiently = false;

    public function send(SmsMessage $message): NotificationResult
    {
        if (self::$failTransiently) {
            throw new TransientNotificationException('Fake SMS transient failure.', 'fake_sms_transient');
        }

        PhoneNumber::normalize($message->to, config('notifications.sms.default_country_code'));
        self::$sent[] = $message;

        return NotificationResult::sent('fake-sms', 'fake-sms-'.count(self::$sent), $message->idempotencyKey);
    }

    public static function reset(): void
    {
        self::$sent = [];
        self::$failTransiently = false;
    }
}
