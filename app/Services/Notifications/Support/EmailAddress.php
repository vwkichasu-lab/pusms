<?php

namespace App\Services\Notifications\Support;

use App\Services\Notifications\Exceptions\NotificationValidationException;

final class EmailAddress
{
    public static function normalize(string $email): string
    {
        $email = trim($email);

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new NotificationValidationException('Recipient email address is invalid.', 'invalid_email');
        }

        return $email;
    }
}
