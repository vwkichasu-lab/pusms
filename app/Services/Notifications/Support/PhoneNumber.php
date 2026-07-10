<?php

namespace App\Services\Notifications\Support;

use App\Services\Notifications\Exceptions\NotificationValidationException;

final class PhoneNumber
{
    public static function normalize(string $phone, ?string $defaultCountryCode = null): string
    {
        $phone = trim($phone);

        if (str_starts_with($phone, '+')) {
            $digits = preg_replace('/\D+/', '', $phone) ?? '';

            return self::validateE164Digits($digits);
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '00')) {
            return self::validateE164Digits(substr($digits, 2));
        }

        if (str_starts_with($digits, '0')) {
            if (blank($defaultCountryCode)) {
                throw new NotificationValidationException('Phone number must include country code or PUSMS_SMS_DEFAULT_COUNTRY_CODE must be configured.', 'missing_country_code');
            }

            return self::validateE164Digits(preg_replace('/\D+/', '', $defaultCountryCode).substr($digits, 1));
        }

        return self::validateE164Digits($digits);
    }

    private static function validateE164Digits(string $digits): string
    {
        if (! preg_match('/^[1-9]\d{7,14}$/', $digits)) {
            throw new NotificationValidationException('Phone number must be a valid E.164 number.', 'invalid_phone');
        }

        return '+'.$digits;
    }

    public static function redact(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (strlen($digits) <= 4) {
            return '****';
        }

        return str_repeat('*', max(strlen($digits) - 4, 0)).substr($digits, -4);
    }
}
