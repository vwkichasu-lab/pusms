<x-filament-panels::page>
    <div class="space-y-6">
        @if ($this->messages)
            <div style="display:flex; justify-content:flex-end;">
                <button
                    type="button"
                    wire:click="deleteSelected"
                    wire:confirm="Delete the selected message history records?"
                    style="background:#dc2626; color:#fff; border:1px solid #b91c1c; border-radius:8px; padding:10px 14px; font-weight:700;"
                >
                    Delete selected
                </button>
            </div>
        @endif

        @forelse ($this->messages as $message)
            @php
                $sent = $message->recipients->where('delivery_status', 'sent')->count();
                $failed = $message->recipients->where('delivery_status', 'failed')->count();
                $queued = $message->recipients->where('delivery_status', 'queued')->count();
            @endphp

            <x-filament::section>
                <x-slot name="heading">
                    {{ $message->communication_type === 'sms' ? 'SMS' : 'Email' }} - {{ $message->subject ?: 'No subject' }}
                </x-slot>

                <x-slot name="description">
                    Status: {{ str($message->status)->headline() }}
                    | Sent: {{ $sent }}
                    | Failed: {{ $failed }}
                    | Queued: {{ $queued }}
                    | Created: {{ $message->created_at?->format('M j, Y g:i A') }}
                </x-slot>

                <div style="display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:12px;">
                    <label style="display:flex; align-items:center; gap:8px; font-weight:700;">
                        <input type="checkbox" wire:model.live="selectedMessages.{{ $message->id }}">
                        Select this message
                    </label>

                    <button
                        type="button"
                        wire:click="deleteMessage({{ $message->id }})"
                        wire:confirm="Delete this message history record?"
                        style="background:#fff; color:#b91c1c; border:1px solid #b91c1c; border-radius:8px; padding:8px 12px; font-weight:700;"
                    >
                        Delete
                    </button>
                </div>

                <div style="border:1px solid #cbd5e1; margin-bottom:12px; padding:12px; background:#fff;">
                    <strong>Message</strong>
                    <div style="margin-top:6px; white-space:pre-wrap;">{{ $message->message }}</div>
                </div>

                <div style="overflow-x:auto;">
                    <table style="width:100%; min-width:980px; border-collapse:collapse;">
                        <thead>
                        <tr>
                            <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Student / Recipient</th>
                            <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Channel</th>
                            <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Destination</th>
                            <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Status</th>
                            <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Provider ID</th>
                            <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Sent / Failed At</th>
                            <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Failure Reason</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($message->recipients as $recipient)
                            <tr>
                                <td style="border:1px solid #cbd5e1; padding:8px;">
                                    {{ $recipient->student?->student_id }}
                                    {{ $recipient->student?->full_name ?? $recipient->sponsor?->name ?? 'Recipient' }}
                                </td>
                                <td style="border:1px solid #cbd5e1; padding:8px;">{{ strtoupper($recipient->channel) }}</td>
                                <td style="border:1px solid #cbd5e1; padding:8px;">{{ $recipient->destination }}</td>
                                <td style="border:1px solid #cbd5e1; padding:8px;">{{ str($recipient->delivery_status)->headline() }}</td>
                                <td style="border:1px solid #cbd5e1; padding:8px;">{{ $recipient->provider_message_id ?: '-' }}</td>
                                <td style="border:1px solid #cbd5e1; padding:8px;">
                                    {{ $recipient->sent_at?->format('M j, Y g:i A') ?: $recipient->failed_at?->format('M j, Y g:i A') ?: '-' }}
                                </td>
                                <td style="border:1px solid #cbd5e1; padding:8px; white-space:normal;">
                                    {{ $recipient->failure_reason ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="border:1px solid #cbd5e1; padding:8px;">No recipients were created for this message.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        @empty
            <x-filament::section>
                <x-slot name="heading">No Message History</x-slot>
                Send an email or SMS first. The delivery result for each student will appear here.
            </x-filament::section>
        @endforelse
    </div>
</x-filament-panels::page>
