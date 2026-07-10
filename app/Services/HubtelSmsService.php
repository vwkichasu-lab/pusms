<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class HubtelSmsService
{
    /**
     * @return array<string, mixed>
     */
    public function send(string $to, string $message): array
    {
        $clientId = config('services.hubtel.client_id');
        $clientSecret = config('services.hubtel.client_secret');

        if (blank($clientId) || blank($clientSecret)) {
            throw new \RuntimeException('Hubtel SMS credentials are not configured. Add HUBTEL_CLIENT_ID and HUBTEL_CLIENT_SECRET.');
        }

        $response = Http::withBasicAuth($clientId, $clientSecret)
            ->acceptJson()
            ->timeout(30)
            ->post(config('services.hubtel.base_url'), [
                'From' => config('services.hubtel.sender_id'),
                'To' => $this->formatPhoneNumber($to),
                'Content' => $message,
            ]);

        $response->throw();

        return $response->json() ?? ['status' => $response->status()];
    }

    private function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($phone, '0')) {
            return '233'.substr($phone, 1);
        }

        return $phone;
    }
}
