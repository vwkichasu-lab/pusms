<?php

namespace App\Services\Notifications\Senders;

use App\Services\Notifications\Contracts\EmailSender;
use App\Services\Notifications\Data\EmailMessage;
use App\Services\Notifications\Data\NotificationResult;
use App\Services\Notifications\Exceptions\TransientNotificationException;
use App\Services\Notifications\Support\EmailAddress;

final class FakeEmailSender implements EmailSender
{
    /** @var array<int, EmailMessage> */
    public static array $sent = [];

    public static bool $failTransiently = false;

    public function send(EmailMessage $message): NotificationResult
    {
        if (self::$failTransiently) {
            throw new TransientNotificationException('Fake email transient failure.', 'fake_email_transient');
        }

        EmailAddress::normalize($message->to);
        self::$sent[] = $message;

        return NotificationResult::sent('fake-email', 'fake-email-'.count(self::$sent), $message->idempotencyKey);
    }

    public static function reset(): void
    {
        self::$sent = [];
        self::$failTransiently = false;
    }
}
