<?php

namespace App\Jobs;

use App\Models\CommunicationRecipient;
use App\Services\HubtelSmsService;
use App\Services\TemplateVariableService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendCommunicationRecipient implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $recipientId) {}

    public function handle(HubtelSmsService $sms, TemplateVariableService $templates): void
    {
        $recipient = CommunicationRecipient::query()
            ->with(['communication', 'student.level', 'student.programme', 'sponsor'])
            ->findOrFail($this->recipientId);

        $message = $recipient->student
            ? $templates->render($recipient->communication->message, $recipient->student)
            : str($recipient->communication->message)
                ->replace('{{sponsor_name}}', $recipient->sponsor?->name ?? 'Sponsor')
                ->replace('{{contact_person}}', $recipient->sponsor?->contact_person ?? '')
                ->toString();

        if ($recipient->communication->attachment_path) {
            $attachmentUrl = asset('storage/'.$recipient->communication->attachment_path);
            $message .= "\n\nAttachment: {$attachmentUrl}";
        }

        try {
            if ($recipient->channel === 'email') {
                Mail::raw($message, function ($mail) use ($recipient): void {
                    $mail = $mail->to($recipient->destination)
                        ->subject($recipient->communication->subject ?? 'Pentecost University Scholarship Update');

                    if ($recipient->communication->attachment_path) {
                        $mail->attach(storage_path('app/public/'.$recipient->communication->attachment_path), [
                            'as' => $recipient->communication->attachment_original_name ?? basename($recipient->communication->attachment_path),
                        ]);
                    }
                });

                $providerResponse = ['driver' => config('mail.default'), 'message' => 'Email handed to mailer.'];
            } else {
                $providerResponse = $sms->send($recipient->destination, $message);
            }

            $recipient->update([
                'delivery_status' => 'sent',
                'sent_at' => now(),
                'failure_reason' => null,
                'provider_response' => $providerResponse,
            ]);

            $this->refreshCommunicationStatus($recipient);
        } catch (\Throwable $exception) {
            $recipient->update([
                'delivery_status' => 'failed',
                'failed_at' => now(),
                'failure_reason' => $exception->getMessage(),
            ]);

            $this->refreshCommunicationStatus($recipient);

            throw $exception;
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
}
