<?php

namespace App\Services\Notifications\Senders;

use App\Services\Notifications\Contracts\EmailSender;
use App\Services\Notifications\Data\EmailMessage;
use App\Services\Notifications\Data\NotificationResult;
use App\Services\Notifications\Exceptions\NotificationConfigurationException;
use App\Services\Notifications\Exceptions\PermanentNotificationException;
use App\Services\Notifications\Exceptions\TransientNotificationException;
use App\Services\Notifications\Support\EmailAddress;
use App\Services\Notifications\Support\SimpleTemplateRenderer;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

final class LaravelSmtpEmailSender implements EmailSender
{
    public function __construct(private readonly SimpleTemplateRenderer $templates) {}

    public function send(EmailMessage $message): NotificationResult
    {
        $this->ensureConfigured();

        $to = EmailAddress::normalize($message->to);
        $subject = trim($message->subject);

        if ($subject === '') {
            throw new PermanentNotificationException('Email subject is required.', 'missing_subject');
        }

        $html = $message->html;
        $text = $message->text;

        if ($message->template) {
            $html ??= $this->templates->renderHtml($message->template, $message->templateData);
            $text ??= $this->templates->renderText(strip_tags($message->template), $message->templateData);
        }

        if (blank($html) && blank($text)) {
            throw new PermanentNotificationException('Email body is required.', 'missing_email_body');
        }

        try {
            if (filled($html)) {
                Mail::html($html, function ($mail) use ($to, $subject, $message): void {
                    $mail->to($to)->subject($subject);

                    if ($message->replyTo) {
                        $mail->replyTo(EmailAddress::normalize($message->replyTo));
                    }

                    foreach ($message->attachments as $attachment) {
                        $mail->attach($attachment['path'], [
                            'as' => $attachment['as'] ?? basename($attachment['path']),
                        ]);
                    }
                });
            } else {
                Mail::raw($text, function ($mail) use ($to, $subject, $message): void {
                    $mail->to($to)->subject($subject);

                    if ($message->replyTo) {
                        $mail->replyTo(EmailAddress::normalize($message->replyTo));
                    }

                    foreach ($message->attachments as $attachment) {
                        $mail->attach($attachment['path'], [
                            'as' => $attachment['as'] ?? basename($attachment['path']),
                        ]);
                    }
                });
            }
        } catch (TransportExceptionInterface $exception) {
            throw new TransientNotificationException(
                'Email provider transport failed. For Gmail, use a Gmail App Password with SMTP enabled; the normal Gmail password is usually rejected.',
                'email_transport_failed',
                $exception,
            );
        } catch (\Throwable $exception) {
            throw new PermanentNotificationException(
                'Email provider rejected the message. Check the Gmail SMTP username, Gmail App Password, sender address, and recipient email.',
                'email_provider_failed',
                $exception,
            );
        }

        return NotificationResult::sent(
            provider: 'smtp',
            idempotencyKey: $message->idempotencyKey,
        );
    }

    private function ensureConfigured(): void
    {
        if (config('mail.default') !== 'smtp') {
            throw new NotificationConfigurationException('Real email requires MAIL_MAILER=smtp.', 'email_mailer_not_smtp');
        }

        foreach (['host', 'port', 'username', 'password'] as $key) {
            if (blank(config("mail.mailers.smtp.{$key}"))) {
                throw new NotificationConfigurationException("SMTP {$key} is not configured.", "missing_smtp_{$key}");
            }
        }

        if (blank(config('mail.from.address'))) {
            throw new NotificationConfigurationException('MAIL_FROM_ADDRESS is not configured.', 'missing_mail_from');
        }
    }
}
