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
            return [
                'simulated' => true,
                'message' => 'Hubtel credentials are not configured. SMS recorded locally.',
            ];
        }

        $response = Http::withBasicAuth($clientId, $clientSecret)
            ->acceptJson()
            ->post(config('services.hubtel.base_url'), [
                'From' => config('services.hubtel.sender_id'),
                'To' => $to,
                'Content' => $message,
            ]);

        $response->throw();

        return $response->json() ?? ['status' => $response->status()];
    }
}
