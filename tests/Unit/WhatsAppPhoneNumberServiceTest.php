<?php

use App\Services\WhatsAppPhoneNumberService;

it('normalizes valid Ghana phone numbers for WhatsApp', function (string $input) {
    $result = app(WhatsAppPhoneNumberService::class)->normalizeGhana($input);

    expect($result['valid'])->toBeTrue()
        ->and($result['normalized'])->toBe('233241234567');
})->with([
    '0241234567',
    '+233241234567',
    '233241234567',
    '024 123 4567',
    '024-123-4567',
]);

it('rejects invalid Ghana phone numbers', function () {
    $result = app(WhatsAppPhoneNumberService::class)->normalizeGhana('12345');

    expect($result['valid'])->toBeFalse()
        ->and($result['error'])->toBeString();
});
