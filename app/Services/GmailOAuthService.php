<?php

namespace App\Services;

use App\Models\GmailAccount;
use App\Models\User;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GmailOAuthService
{
    /**
     * @return array<int, string>
     */
    public function scopes(): array
    {
        return [
            'openid',
            'email',
            'profile',
            'https://www.googleapis.com/auth/gmail.send',
        ];
    }

    public function authorizationUrl(string $state): string
    {
        return 'https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query([
            'client_id' => config('services.gmail.client_id'),
            'redirect_uri' => route('gmail.callback'),
            'response_type' => 'code',
            'scope' => implode(' ', $this->scopes()),
            'access_type' => 'offline',
            'include_granted_scopes' => 'true',
            'prompt' => 'consent',
            'state' => $state,
        ]);
    }

    /**
     * @throws RequestException
     */
    public function connect(User $user, string $code): GmailAccount
    {
        $token = Http::asForm()
            ->timeout(30)
            ->post('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => config('services.gmail.client_id'),
                'client_secret' => config('services.gmail.client_secret'),
                'redirect_uri' => route('gmail.callback'),
                'grant_type' => 'authorization_code',
            ])
            ->throw()
            ->json();

        $profile = Http::withToken($token['access_token'])
            ->timeout(30)
            ->get('https://openidconnect.googleapis.com/v1/userinfo')
            ->throw()
            ->json();

        $account = GmailAccount::query()->firstOrNew([
            'user_id' => $user->id,
            'email' => $profile['email'],
        ]);

        $account->fill([
            'google_user_id' => $profile['sub'] ?? null,
            'name' => $profile['name'] ?? null,
            'access_token' => $token['access_token'],
            'refresh_token' => $token['refresh_token'] ?? $account->refresh_token,
            'token_expires_at' => now()->addSeconds((int) ($token['expires_in'] ?? 3600) - 60),
            'scopes' => isset($token['scope']) ? explode(' ', $token['scope']) : $this->scopes(),
        ]);

        $account->save();

        return $account;
    }

    /**
     * @throws RequestException
     */
    public function accessToken(GmailAccount $account): string
    {
        if ($account->token_expires_at && $account->token_expires_at->isFuture()) {
            return $account->access_token;
        }

        $token = Http::asForm()
            ->timeout(30)
            ->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.gmail.client_id'),
                'client_secret' => config('services.gmail.client_secret'),
                'refresh_token' => $account->refresh_token,
                'grant_type' => 'refresh_token',
            ])
            ->throw()
            ->json();

        $account->update([
            'access_token' => $token['access_token'],
            'token_expires_at' => now()->addSeconds((int) ($token['expires_in'] ?? 3600) - 60),
            'scopes' => isset($token['scope']) ? explode(' ', $token['scope']) : $account->scopes,
        ]);

        return $account->access_token;
    }

    public function state(): string
    {
        return Str::random(48);
    }

    public function configured(): bool
    {
        return filled(config('services.gmail.client_id')) && filled(config('services.gmail.client_secret'));
    }
}
