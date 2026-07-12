<?php

namespace App\Services;

use App\Jobs\SendCommunicationRecipient;
use App\Models\Communication;
use App\Models\Sponsor;
use App\Models\Student;

class CommunicationService
{
    /**
     * @param  iterable<Student>  $students
     * @param  iterable<Sponsor>  $sponsors
     */
    public function dispatch(Communication $communication, iterable $students, iterable $sponsors = []): void
    {
        $communication->update(['status' => 'processing']);
        $queued = 0;

        foreach ($students as $student) {
            foreach ($this->channels($communication->communication_type) as $channel) {
                $destination = $channel === 'email' ? $student->email : $student->phone;

                if (! filled($destination)) {
                    continue;
                }

                $recipient = $communication->recipients()->create([
                    'student_id' => $student->id,
                    'channel' => $channel,
                    'destination' => $destination,
                    'delivery_status' => 'queued',
                ]);

                SendCommunicationRecipient::dispatch($recipient->id);
                $queued++;
            }
        }

        foreach ($sponsors as $sponsor) {
            foreach ($this->channels($communication->communication_type) as $channel) {
                $destination = $channel === 'email' ? $sponsor->email : $sponsor->phone;

                if (! filled($destination)) {
                    continue;
                }

                $recipient = $communication->recipients()->create([
                    'sponsor_id' => $sponsor->id,
                    'channel' => $channel,
                    'destination' => $destination,
                    'delivery_status' => 'queued',
                ]);

                SendCommunicationRecipient::dispatch($recipient->id);
                $queued++;
            }
        }

        $communication->update([
            'status' => $queued > 0 ? 'processing' : 'completed',
            'sent_at' => $queued > 0 ? null : now(),
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function channels(string $type): array
    {
        return ['email'];
    }
}
