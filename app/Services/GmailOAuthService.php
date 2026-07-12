<?php

namespace App\Services;

use App\Models\GmailAccount;
use App\Models\User;
use App\Services\Notifications\Data\EmailMessage;
use App\Services\Notifications\Data\NotificationResult;
use App\Services\Notifications\Exceptions\NotificationConfigurationException;
use App\Services\Notifications\Exceptions\NotificationValidationException;
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
            'https://www.googleapis.com/auth/gmail.readonly',
        ];
    }

    public function getAuthorizationUrl(string $state): string
    {
        $this->assertConfigured();

        return 'https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query([
            'client_id' => config('services.gmail.client_id'),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => implode(' ', $this->scopes()),
            'access_type' => 'offline',
            'include_granted_scopes' => 'true',
            'prompt' => 'consent',
            'state' => $state,
        ]);
    }

    public function authorizationUrl(string $state): string
    {
        return $this->getAuthorizationUrl($state);
    }

    public function handleCallback(User $user, string $code): GmailAccount
    {
        return $this->connect($user, $code);
    }

    public function connect(User $user, string $code): GmailAccount
    {
        $this->assertConfigured();

        try {
            $token = Http::asForm()
                ->timeout(30)
                ->post('https://oauth2.googleapis.com/token', [
                    'code' => $code,
                    'client_id' => config('services.gmail.client_id'),
                    'client_secret' => config('services.gmail.client_secret'),
                    'redirect_uri' => $this->redirectUri(),
                    'grant_type' => 'authorization_code',
                ])
                ->throw()
                ->json();

            $profile = Http::withToken($token['access_token'])
                ->timeout(30)
                ->get('https://openidconnect.googleapis.com/v1/userinfo')
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            throw new NotificationConfigurationException(
                $this->safeOAuthError($exception, 'Gmail authorization failed. Check the Google OAuth client, redirect URI, and enabled APIs.'),
                'gmail_oauth_callback_failed',
                $exception,
            );
        }

        if (blank($token['refresh_token'] ?? null)) {
            throw new NotificationConfigurationException(
                'Google did not return a refresh token. Use Reconnect Gmail and approve the consent screen again.',
                'gmail_refresh_token_missing',
            );
        }

        $account = GmailAccount::query()->firstOrNew([
            'user_id' => $user->id,
            'email' => $profile['email'],
        ]);

        $account->fill([
            'google_user_id' => $profile['sub'] ?? null,
            'name' => $profile['name'] ?? null,
            'access_token' => $token['access_token'],
            'refresh_token' => $token['refresh_token'],
            'token_expires_at' => now()->addSeconds((int) ($token['expires_in'] ?? 3600) - 60),
            'scopes' => isset($token['scope']) ? explode(' ', $token['scope']) : $this->scopes(),
            'connected_at' => $account->connected_at ?: now(),
            'revoked_at' => null,
            'status' => 'connected',
        ]);

        $account->save();

        return $account;
    }

    public function refreshAccessToken(GmailAccount $account): string
    {
        $this->assertConfigured();

        if ($account->token_expires_at && $account->token_expires_at->isFuture() && $account->status === 'connected') {
            return $account->access_token;
        }

        if (blank($account->refresh_token) || $account->status !== 'connected') {
            throw new NotificationConfigurationException('Reconnect Gmail before sending. The stored Gmail session is not active.', 'gmail_not_connected');
        }

        try {
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
        } catch (RequestException $exception) {
            $account->update([
                'status' => 'needs_reconnect',
                'revoked_at' => now(),
            ]);

            throw new NotificationConfigurationException(
                $this->safeOAuthError($exception, 'Gmail token refresh failed. Reconnect Gmail and try again.'),
                'gmail_refresh_failed',
                $exception,
            );
        }

        $account->update([
            'access_token' => $token['access_token'],
            'token_expires_at' => now()->addSeconds((int) ($token['expires_in'] ?? 3600) - 60),
            'scopes' => isset($token['scope']) ? explode(' ', $token['scope']) : $account->scopes,
            'status' => 'connected',
            'revoked_at' => null,
        ]);

        return $account->access_token;
    }

    public function accessToken(GmailAccount $account): string
    {
        return $this->refreshAccessToken($account);
    }

    public function sendEmail(GmailAccount $account, EmailMessage $message): NotificationResult
    {
        return app(\App\Services\Notifications\Senders\GmailApiEmailSender::class)->send($message, $account);
    }

    public function sendTestEmail(GmailAccount $account, string $to, ?string $toName = null): NotificationResult
    {
        if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            throw new NotificationValidationException('Enter a valid recipient email address.', 'invalid_recipient_email');
        }

        return $this->sendEmail($account, new EmailMessage(
            to: $to,
            subject: 'PUSMS Gmail Test Email',
            text: 'This is a test email from the Pentecost University Scholarship Management System.',
            html: '<p>This is a test email from the Pentecost University Scholarship Management System.</p>',
            toName: $toName,
            idempotencyKey: 'gmail-test:'.$account->id.':'.now()->timestamp,
        ));
    }

    /**
     * @return array{count:int, messages:array<int, array<string, string|null>>, needs_reconnect:bool}
     */
    public function inboxPreview(GmailAccount $account, int $limit = 15): array
    {
        if (! in_array('https://www.googleapis.com/auth/gmail.readonly', $account->scopes ?? [], true)) {
            return ['count' => 0, 'messages' => [], 'needs_reconnect' => true];
        }

        $token = $this->refreshAccessToken($account);
        $query = 'in:inbox newer_than:365d (scholarship OR bursary OR "PU Bursary" OR COPCEF OR PUSMS OR "Pentecost University Scholarship" OR sponsor OR sponsorship)';

        $list = Http::withToken($token)
            ->timeout(30)
            ->get('https://gmail.googleapis.com/gmail/v1/users/me/messages', [
                'labelIds' => 'INBOX',
                'maxResults' => $limit,
                'q' => $query,
            ])
            ->throw()
            ->json();

        $messages = collect($list['messages'] ?? [])
            ->take($limit)
            ->map(function (array $message) use ($token): array {
                $detail = Http::withToken($token)
                    ->timeout(30)
                    ->get("https://gmail.googleapis.com/gmail/v1/users/me/messages/{$message['id']}", [
                        'format' => 'metadata',
                        'metadataHeaders' => ['From', 'Subject', 'Date'],
                    ])
                    ->throw()
                    ->json();

                $headers = collect($detail['payload']['headers'] ?? [])
                    ->mapWithKeys(fn (array $header): array => [strtolower($header['name'] ?? '') => $header['value'] ?? null]);

                return [
                    'id' => $message['id'] ?? null,
                    'from' => $headers['from'] ?? '-',
                    'subject' => $headers['subject'] ?? '(no subject)',
                    'date' => $headers['date'] ?? null,
                    'snippet' => $detail['snippet'] ?? '',
                ];
            })
            ->all();

        return [
            'count' => (int) ($list['resultSizeEstimate'] ?? count($messages)),
            'messages' => $messages,
            'needs_reconnect' => false,
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public function inboxMessage(GmailAccount $account, string $messageId): array
    {
        if (! in_array('https://www.googleapis.com/auth/gmail.readonly', $account->scopes ?? [], true)) {
            throw new NotificationConfigurationException(
                'Reconnect Gmail and approve inbox reading before opening messages.',
                'gmail_read_scope_missing',
            );
        }

        $token = $this->refreshAccessToken($account);

        $detail = Http::withToken($token)
            ->timeout(30)
            ->get("https://gmail.googleapis.com/gmail/v1/users/me/messages/{$messageId}", [
                'format' => 'full',
            ])
            ->throw()
            ->json();

        $headers = collect($detail['payload']['headers'] ?? [])
            ->mapWithKeys(fn (array $header): array => [strtolower($header['name'] ?? '') => $header['value'] ?? null]);

        return [
            'id' => $messageId,
            'from' => $headers['from'] ?? '-',
            'to' => $headers['to'] ?? '-',
            'subject' => $headers['subject'] ?? '(no subject)',
            'date' => $headers['date'] ?? null,
            'snippet' => $detail['snippet'] ?? '',
            'body' => $this->extractReadableBody($detail['payload'] ?? []),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function extractReadableBody(array $payload): string
    {
        $plain = $this->findMimeBody($payload, 'text/plain');

        if (filled($plain)) {
            return trim($plain);
        }

        $html = $this->findMimeBody($payload, 'text/html');

        if (filled($html)) {
            return trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5));
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function findMimeBody(array $payload, string $mimeType): ?string
    {
        if (($payload['mimeType'] ?? null) === $mimeType && filled($payload['body']['data'] ?? null)) {
            return $this->decodeGmailBody((string) $payload['body']['data']);
        }

        foreach (($payload['parts'] ?? []) as $part) {
            if (! is_array($part)) {
                continue;
            }

            $body = $this->findMimeBody($part, $mimeType);

            if (filled($body)) {
                return $body;
            }
        }

        return null;
    }

    private function decodeGmailBody(string $value): string
    {
        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        return $decoded === false ? '' : $decoded;
    }

    public function disconnect(GmailAccount $account): void
    {
        $account->update([
            'access_token' => '',
            'refresh_token' => null,
            'token_expires_at' => null,
            'revoked_at' => now(),
            'status' => 'revoked',
        ]);
    }

    public function isConnected(?GmailAccount $account): bool
    {
        return $account instanceof GmailAccount
            && $account->status === 'connected'
            && filled($account->refresh_token)
            && blank($account->revoked_at);
    }

    public function state(): string
    {
        return Str::random(64);
    }

    public function configured(): bool
    {
        return filled(config('services.gmail.client_id'))
            && filled(config('services.gmail.client_secret'))
            && filled($this->redirectUri())
            && (! app()->environment('production') || str_starts_with($this->redirectUri(), 'https://'));
    }

    public function assertConfigured(): void
    {
        if (! $this->configured()) {
            throw new NotificationConfigurationException(
                'Gmail OAuth is not configured. Set GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, and GOOGLE_REDIRECT_URI in Railway.',
                'gmail_oauth_not_configured',
            );
        }
    }

    public function redirectUri(): string
    {
        return config('services.gmail.redirect_uri') ?: route('gmail.callback');
    }

    private function safeOAuthError(RequestException $exception, string $fallback): string
    {
        $payload = $exception->response?->json();
        $error = is_array($payload) ? ($payload['error'] ?? null) : null;

        return match ($error) {
            'redirect_uri_mismatch' => 'Google rejected the callback URL. Confirm GOOGLE_REDIRECT_URI exactly matches the Google OAuth redirect URI.',
            'invalid_client' => 'Google rejected the OAuth client. Confirm the Client ID and rotated Client Secret in Railway.',
            'invalid_grant' => 'Google rejected or revoked the authorization grant. Reconnect Gmail and approve consent again.',
            'access_denied' => 'Gmail authorization was denied on the Google consent screen.',
            default => $fallback,
        };
    }
}
