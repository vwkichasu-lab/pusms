<?php

namespace App\Jobs;

use App\Models\CommunicationRecipient;
use App\Services\Notifications\Contracts\EmailSender;
use App\Services\Notifications\Contracts\SmsSender;
use App\Services\Notifications\Data\EmailMessage;
use App\Services\Notifications\Data\SmsMessage;
use App\Services\Notifications\Senders\GmailApiEmailSender;
use App\Services\TemplateVariableService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class SendCommunicationRecipient implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function __construct(public int $recipientId) {}

    public function handle(EmailSender $email, SmsSender $sms, TemplateVariableService $templates, ?GmailApiEmailSender $gmailEmail = null): void
    {
        $recipient = CommunicationRecipient::query()
            ->with(['communication.gmailAccount', 'student.level', 'student.programme', 'sponsor'])
            ->findOrFail($this->recipientId);

        if ($recipient->delivery_status === 'sent') {
            return;
        }

        $message = $templates->render(
            $recipient->communication->message,
            $recipient->student,
            $recipient->sponsor,
        );

        if ($recipient->communication->attachment_path) {
            $attachmentUrl = asset('storage/'.$recipient->communication->attachment_path);
            $message .= "\n\nAttachment: {$attachmentUrl}";
        }

        try {
            if ($recipient->channel === 'email') {
                $html = view('emails.communication', [
                    'communication' => $recipient->communication,
                    'recipient' => $recipient,
                    'messageBody' => $message,
                ])->render();

                $emailMessage = new EmailMessage(
                    to: $recipient->destination,
                    subject: $recipient->communication->subject ?? 'Pentecost University Scholarship Update',
                    text: $message,
                    html: $html,
                    toName: $recipient->student?->full_name ?? $recipient->sponsor?->contact_person ?? $recipient->sponsor?->name,
                    replyTo: $recipient->communication->metadata['reply_to'] ?? null,
                    cc: $recipient->communication->metadata['cc'] ?? [],
                    bcc: $recipient->communication->metadata['bcc'] ?? [],
                    idempotencyKey: $this->idempotencyKey($recipient),
                    attachments: $this->attachments($recipient),
                );

                $result = $recipient->communication->gmailAccount
                    ? ($gmailEmail ?? app(GmailApiEmailSender::class))->send($emailMessage, $recipient->communication->gmailAccount)
                    : $email->send($emailMessage);
            } else {
                $result = $sms->send(new SmsMessage(
                    to: $recipient->destination,
                    message: $message,
                    idempotencyKey: $this->idempotencyKey($recipient),
                ));
            }

            $recipient->update([
                'delivery_status' => 'sent',
                'sent_at' => now(),
                'failure_reason' => null,
                'provider_message_id' => $result->providerMessageId,
                'provider_response' => $result->toArray(),
            ]);

            $this->refreshCommunicationStatus($recipient);
        } catch (\Throwable $exception) {
            $recipient->update([
                'delivery_status' => 'failed',
                'failed_at' => now(),
                'failure_reason' => $exception->getMessage(),
            ]);

            $this->refreshCommunicationStatus($recipient);
        }
    }

    private function refreshCommunicationStatus(CommunicationRecipient $recipient): void
    {
        $communication = $recipient->communication()->with('recipients')->first();

        if (! $communication) {
            return;
        }

        $statuses = $communication->recipients->pluck('delivery_status');

        if ($statuses->contains('queued')) {
            $communication->update(['status' => 'processing']);

            return;
        }

        $failed = $statuses->contains('failed');
        $sent = $statuses->contains('sent');

        $communication->update([
            'status' => match (true) {
                $failed && $sent => 'partially_failed',
                $failed => 'failed',
                default => 'completed',
            },
            'sent_at' => $sent ? now() : $communication->sent_at,
        ]);
    }

    private function idempotencyKey(CommunicationRecipient $recipient): string
    {
        return "communication-recipient:{$recipient->id}:{$recipient->channel}";
    }

    /**
     * @return array<int, array{path: string, as?: string}>
     */
    private function attachments(CommunicationRecipient $recipient): array
    {
        if (
            blank($recipient->communication->attachment_path) ||
            ! Storage::disk('public')->exists($recipient->communication->attachment_path)
        ) {
            return [];
        }

        return [[
            'path' => Storage::disk('public')->path($recipient->communication->attachment_path),
            'as' => $recipient->communication->attachment_original_name ?? basename($recipient->communication->attachment_path),
        ]];
    }
}
