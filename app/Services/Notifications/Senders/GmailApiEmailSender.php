<?php

namespace App\Services\Notifications\Senders;

use App\Models\GmailAccount;
use App\Services\GmailOAuthService;
use App\Services\Notifications\Data\EmailMessage;
use App\Services\Notifications\Data\NotificationResult;
use App\Services\Notifications\Exceptions\NotificationConfigurationException;
use App\Services\Notifications\Exceptions\PermanentNotificationException;
use App\Services\Notifications\Exceptions\TransientNotificationException;
use App\Services\Notifications\Support\EmailAddress;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GmailApiEmailSender
{
    public function __construct(private readonly GmailOAuthService $gmail) {}

    public function send(EmailMessage $message, GmailAccount $account): NotificationResult
    {
        if (! $this->gmail->configured()) {
            throw new NotificationConfigurationException('Gmail OAuth is not configured. Add GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET.', 'gmail_oauth_not_configured');
        }

        if (blank($account->refresh_token)) {
            throw new NotificationConfigurationException('Reconnect Gmail so PUSMS can refresh the Gmail session before sending.', 'gmail_refresh_token_missing');
        }

        try {
            $response = Http::withToken($this->gmail->accessToken($account))
                ->timeout(30)
                ->post('https://gmail.googleapis.com/gmail/v1/users/me/messages/send', [
                    'raw' => $this->base64UrlEncode($this->mimeMessage($message, $account)),
                ])
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            $body = $exception->response?->body();

            throw new TransientNotificationException(
                'Gmail API send failed: '.($body ?: $exception->getMessage()),
                'gmail_api_send_failed',
                $exception,
            );
        } catch (\Throwable $exception) {
            throw new PermanentNotificationException(
                'Gmail API rejected the message: '.$exception->getMessage(),
                'gmail_api_rejected',
                $exception,
            );
        }

        $account->update(['last_used_at' => now()]);

        return NotificationResult::sent(
            provider: 'gmail_api',
            providerMessageId: $response['id'] ?? null,
            idempotencyKey: $message->idempotencyKey,
        );
    }

    private function mimeMessage(EmailMessage $message, GmailAccount $account): string
    {
        $to = EmailAddress::normalize($message->to);
        $subject = $this->encodedHeader($message->subject);
        $fromName = $account->name ?: $account->email;
        $from = $this->encodedHeader($fromName).' <'.$account->email.'>';
        $text = $message->text ?: trim(strip_tags((string) $message->html));
        $html = $message->html ?: nl2br(e((string) $text));

        $headers = [
            'From: '.$from,
            'To: '.$to,
            'Subject: '.$subject,
            'MIME-Version: 1.0',
        ];

        if ($message->attachments === []) {
            return implode("\r\n", [
                ...$headers,
                'Content-Type: text/html; charset=UTF-8',
                'Content-Transfer-Encoding: quoted-printable',
                '',
                quoted_printable_encode($html),
            ]);
        }

        $mixedBoundary = 'mixed_'.Str::random(24);
        $alternativeBoundary = 'alt_'.Str::random(24);
        $parts = [
            ...$headers,
            'Content-Type: multipart/mixed; boundary="'.$mixedBoundary.'"',
            '',
            '--'.$mixedBoundary,
            'Content-Type: multipart/alternative; boundary="'.$alternativeBoundary.'"',
            '',
            '--'.$alternativeBoundary,
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: quoted-printable',
            '',
            quoted_printable_encode((string) $text),
            '--'.$alternativeBoundary,
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: quoted-printable',
            '',
            quoted_printable_encode($html),
            '--'.$alternativeBoundary.'--',
        ];

        foreach ($message->attachments as $attachment) {
            $path = $attachment['path'];

            if (! is_file($path)) {
                continue;
            }

            $filename = $attachment['as'] ?? basename($path);
            $mime = File::mimeType($path) ?: 'application/octet-stream';
            $parts = [
                ...$parts,
                '--'.$mixedBoundary,
                'Content-Type: '.$mime.'; name="'.$this->escapeHeaderValue($filename).'"',
                'Content-Transfer-Encoding: base64',
                'Content-Disposition: attachment; filename="'.$this->escapeHeaderValue($filename).'"',
                '',
                chunk_split(base64_encode((string) file_get_contents($path)), 76, "\r\n"),
            ];
        }

        $parts[] = '--'.$mixedBoundary.'--';

        return implode("\r\n", $parts);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function encodedHeader(string $value): string
    {
        return mb_encode_mimeheader($value, 'UTF-8', 'B', "\r\n");
    }

    private function escapeHeaderValue(string $value): string
    {
        return str_replace(['\\', '"'], ['\\\\', '\\"'], $value);
    }
}
