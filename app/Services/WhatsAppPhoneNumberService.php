<?php

namespace App\Services;

final class WhatsAppPhoneNumberService
{
    /**
     * @return array{valid: bool, normalized?: string, error?: string}
     */
    public function normalizeGhana(?string $phone): array
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';

        if ($digits === '') {
            return ['valid' => false, 'error' => 'Phone number is missing.'];
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            $digits = '233'.substr($digits, 1);
        }

        if (str_starts_with($digits, '233') && strlen($digits) === 12 && preg_match('/^233[235]\d{8}$/', $digits)) {
            return ['valid' => true, 'normalized' => $digits];
        }

        return ['valid' => false, 'error' => 'Invalid Ghana phone number. Use a mobile number like 0241234567 or +233241234567.'];
    }
}
