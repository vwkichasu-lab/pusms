<x-filament-panels::page>
    <div class="space-y-4">
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

        <x-filament::section>
            <div style="overflow:auto;">
                <table style="width:100%; min-width:1100px; border-collapse:collapse;">
                    <thead>
                    <tr>
                        <th style="border-bottom:1px solid #cbd5e1; padding:10px; text-align:left; width:44px;"></th>
                        <th style="border-bottom:1px solid #cbd5e1; padding:10px; text-align:left;">Type</th>
                        <th style="border-bottom:1px solid #cbd5e1; padding:10px; text-align:left;">Subject</th>
                        <th style="border-bottom:1px solid #cbd5e1; padding:10px; text-align:left;">Preview</th>
                        <th style="border-bottom:1px solid #cbd5e1; padding:10px; text-align:left;">Recipients</th>
                        <th style="border-bottom:1px solid #cbd5e1; padding:10px; text-align:left;">Status</th>
                        <th style="border-bottom:1px solid #cbd5e1; padding:10px; text-align:left;">Sent</th>
                        <th style="border-bottom:1px solid #cbd5e1; padding:10px; text-align:left;">Failed</th>
                        <th style="border-bottom:1px solid #cbd5e1; padding:10px; text-align:left;">Date</th>
                        <th style="border-bottom:1px solid #cbd5e1; padding:10px; text-align:left;">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($this->messages as $message)
                        @php
                            $sent = $message->recipients->where('delivery_status', 'sent')->count();
                            $failed = $message->recipients->where('delivery_status', 'failed')->count();
                            $total = $message->recipients->count();
                        @endphp
                        <tr wire:click="toggleMessage({{ $message->id }})" style="cursor:pointer; background:{{ $this->expandedMessageId === $message->id ? '#eff6ff' : '#fff' }};">
                            <td style="border-bottom:1px solid #e2e8f0; padding:10px;" wire:click.stop>
                                <input type="checkbox" wire:model.live="selectedMessages.{{ $message->id }}">
                            </td>
                            <td style="border-bottom:1px solid #e2e8f0; padding:10px;">{{ strtoupper($message->communication_type) }}</td>
                            <td style="border-bottom:1px solid #e2e8f0; padding:10px; font-weight:800;">{{ $message->subject ?: 'No subject' }}</td>
                            <td style="border-bottom:1px solid #e2e8f0; padding:10px;">{{ str($message->message)->squish()->limit(90) }}</td>
                            <td style="border-bottom:1px solid #e2e8f0; padding:10px;">{{ $total }}</td>
                            <td style="border-bottom:1px solid #e2e8f0; padding:10px;">{{ str($message->status)->headline() }}</td>
                            <td style="border-bottom:1px solid #e2e8f0; padding:10px;">{{ $sent }}</td>
                            <td style="border-bottom:1px solid #e2e8f0; padding:10px;">{{ $failed }}</td>
                            <td style="border-bottom:1px solid #e2e8f0; padding:10px;">{{ $message->created_at?->format('M j, Y g:i A') }}</td>
                            <td style="border-bottom:1px solid #e2e8f0; padding:10px;" wire:click.stop>
                                <button
                                    type="button"
                                    wire:click="deleteMessage({{ $message->id }})"
                                    wire:confirm="Delete this message history record?"
                                    style="color:#b91c1c; font-weight:800;"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>

                        @if ($this->expandedMessageId === $message->id)
                            <tr>
                                <td colspan="10" style="border-bottom:1px solid #cbd5e1; padding:14px; background:#f8fafc;">
                                    <div style="border:1px solid #cbd5e1; padding:12px; background:#fff; margin-bottom:12px;">
                                        <strong>Full Message</strong>
                                        <div style="margin-top:6px; white-space:pre-wrap;">{{ $message->message }}</div>
                                    </div>

                                    <div style="overflow:auto;">
                                        <table style="width:100%; min-width:920px; border-collapse:collapse;">
                                            <thead>
                                            <tr>
                                                <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Recipient</th>
                                                <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Channel</th>
                                                <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Destination</th>
                                                <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Status</th>
                                                <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Sent / Failed At</th>
                                                <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Failure Reason</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($message->recipients as $recipient)
                                                <tr>
                                                    <td style="border:1px solid #cbd5e1; padding:8px;">{{ $recipient->student?->student_id }} {{ $recipient->student?->full_name ?? $recipient->sponsor?->name ?? 'Recipient' }}</td>
                                                    <td style="border:1px solid #cbd5e1; padding:8px;">{{ strtoupper($recipient->channel) }}</td>
                                                    <td style="border:1px solid #cbd5e1; padding:8px;">{{ $recipient->destination }}</td>
                                                    <td style="border:1px solid #cbd5e1; padding:8px;">{{ str($recipient->delivery_status)->headline() }}</td>
                                                    <td style="border:1px solid #cbd5e1; padding:8px;">{{ $recipient->sent_at?->format('M j, Y g:i A') ?: $recipient->failed_at?->format('M j, Y g:i A') ?: '-' }}</td>
                                                    <td style="border:1px solid #cbd5e1; padding:8px; white-space:normal;">{{ $recipient->failure_reason ?: '-' }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="10" style="padding:18px;">No message history yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
