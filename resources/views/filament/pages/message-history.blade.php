<x-filament-panels::page>
    <style>
        .pusms-mailbox {
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            overflow: hidden;
            background: #ffffff;
        }

        .pusms-mailbox-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 10px 14px;
            border-bottom: 1px solid #dbe3ee;
            background: #ffffff;
        }

        .pusms-mailbox-chips,
        .pusms-mailbox-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .pusms-mailbox-chip {
            border: 1px solid #94a3b8;
            border-radius: 8px;
            padding: 7px 12px;
            font-weight: 700;
            color: #334155;
            background: #ffffff;
        }

        .pusms-mailbox-link {
            color: #005eea;
            font-weight: 700;
        }

        .pusms-mailbox-icon {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            background: transparent;
            color: #475569;
            font-size: 18px;
            cursor: pointer;
        }

        .pusms-mailbox-delete {
            background: #dc2626;
            color: #ffffff;
            border: 1px solid #b91c1c;
            border-radius: 8px;
            padding: 9px 13px;
            font-weight: 800;
        }

        .pusms-mailbox-table {
            width: 100%;
            min-width: 1080px;
            border-collapse: collapse;
        }

        .pusms-mailbox-row {
            cursor: pointer;
            background: #eef4fb;
        }

        .pusms-mailbox-row:hover {
            background: #e2edf8;
        }

        .pusms-mailbox-row.is-expanded {
            background: #dbeafe;
        }

        .pusms-mailbox-row td {
            border-bottom: 1px solid #d6e0eb;
            padding: 11px 10px;
            vertical-align: middle;
            white-space: nowrap;
        }

        .pusms-mailbox-check {
            width: 44px;
            text-align: center;
        }

        .pusms-mailbox-status {
            width: 88px;
            color: #475569;
            font-weight: 700;
        }

        .pusms-mailbox-subject {
            width: 28%;
            color: #111827;
            font-weight: 800;
        }

        .pusms-mailbox-preview {
            color: #475569;
            max-width: 540px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pusms-mailbox-date {
            width: 130px;
            color: #475569;
            text-align: right;
        }

        .pusms-mailbox-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 2px 8px;
            background: #eef2f7;
            color: #334155;
            font-weight: 700;
            font-size: 12px;
        }

        .pusms-mailbox-detail {
            background: #ffffff;
        }

        .pusms-mailbox-detail td {
            border-bottom: 1px solid #cbd5e1;
            padding: 14px;
        }

        .pusms-mailbox-message {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            padding: 12px;
            margin-bottom: 12px;
        }

        .pusms-recipient-table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
        }

        .pusms-recipient-table th,
        .pusms-recipient-table td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            text-align: left;
            white-space: nowrap;
        }

        .pusms-recipient-table td:last-child {
            white-space: normal;
        }

        html.dark .pusms-mailbox,
        html.dark .pusms-mailbox-toolbar,
        html.dark .pusms-mailbox-chip,
        html.dark .pusms-mailbox-detail,
        html.dark .pusms-mailbox-message {
            background: #0d1117;
            border-color: #30363d;
        }

        html.dark .pusms-mailbox-row {
            background: #161b22;
        }

        html.dark .pusms-mailbox-row:hover,
        html.dark .pusms-mailbox-row.is-expanded {
            background: #1f2937;
        }

        html.dark .pusms-mailbox-subject,
        html.dark .pusms-mailbox-chip {
            color: #f8fafc;
        }
    </style>

    <div class="space-y-4">
        @if ($this->messages)
            <div style="display:flex; justify-content:flex-end;">
                <button
                    type="button"
                    wire:click="deleteSelected"
                    wire:confirm="Delete the selected message history records?"
                    class="pusms-mailbox-delete"
                >
                    Delete selected
                </button>
            </div>
        @endif

        <div class="pusms-mailbox">
            <div class="pusms-mailbox-toolbar">
                <div class="pusms-mailbox-chips">
                    <span class="pusms-mailbox-chip">Any time</span>
                    <span class="pusms-mailbox-chip">Has attachment</span>
                    <span class="pusms-mailbox-chip">To</span>
                    <span class="pusms-mailbox-link">Advanced search</span>
                </div>

                <div class="pusms-mailbox-actions">
                    <button type="button" class="pusms-mailbox-icon" aria-label="Select messages">☐</button>
                    <button type="button" class="pusms-mailbox-icon" aria-label="Refresh">↻</button>
                    <button type="button" class="pusms-mailbox-icon" aria-label="More actions">⋮</button>
                    <span style="color:#64748b; font-weight:700;">
                        {{ $this->messages->count() ? '1-' . $this->messages->count() . ' of ' . $this->messages->count() : '0 of 0' }}
                    </span>
                </div>
            </div>

            <div style="overflow:auto;">
                <table class="pusms-mailbox-table">
                    <tbody>
                    @forelse ($this->messages as $message)
                        @php
                            $sent = $message->recipients->where('delivery_status', 'sent')->count();
                            $failed = $message->recipients->where('delivery_status', 'failed')->count();
                            $total = $message->recipients->count();
                            $recipientPreview = $message->recipients
                                ->take(3)
                                ->map(fn ($recipient) => $recipient->student?->full_name ?? $recipient->sponsor?->name ?? $recipient->destination)
                                ->filter()
                                ->join(', ');
                        @endphp
                        <tr
                            wire:click="toggleMessage({{ $message->id }})"
                            class="pusms-mailbox-row {{ $this->expandedMessageId === $message->id ? 'is-expanded' : '' }}"
                        >
                            <td class="pusms-mailbox-check" wire:click.stop>
                                <input type="checkbox" wire:model.live="selectedMessages.{{ $message->id }}">
                            </td>
                            <td class="pusms-mailbox-status">
                                {{ strtoupper($message->communication_type) }}
                            </td>
                            <td class="pusms-mailbox-subject">
                                To: {{ $recipientPreview ?: 'Recipients' }}
                                @if ($total > 3)
                                    +{{ $total - 3 }}
                                @endif
                            </td>
                            <td class="pusms-mailbox-preview">
                                <span style="font-weight:800; color:#111827;">{{ $message->subject ?: 'No subject' }}</span>
                                <span style="color:#64748b;">- {{ str($message->message)->squish()->limit(120) }}</span>
                                <span class="pusms-mailbox-pill">{{ str($message->status)->headline() }}</span>
                                <span class="pusms-mailbox-pill">Sent {{ $sent }}</span>
                                @if ($failed)
                                    <span class="pusms-mailbox-pill">Failed {{ $failed }}</span>
                                @endif
                            </td>
                            <td class="pusms-mailbox-date">
                                {{ $message->created_at?->isToday() ? $message->created_at?->format('g:i A') : $message->created_at?->format('M j') }}
                            </td>
                            <td style="width:82px; text-align:right;" wire:click.stop>
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
                            <tr class="pusms-mailbox-detail">
                                <td colspan="6">
                                    <div class="pusms-mailbox-message">
                                        <strong>{{ $message->subject ?: 'No subject' }}</strong>
                                        <div style="margin-top:6px; white-space:pre-wrap;">{{ $message->message }}</div>
                                    </div>

                                    <div style="overflow:auto;">
                                        <table class="pusms-recipient-table">
                                            <thead>
                                            <tr>
                                                <th>Recipient</th>
                                                <th>Channel</th>
                                                <th>Destination</th>
                                                <th>Status</th>
                                                <th>Sent / Failed At</th>
                                                <th>Failure Reason</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($message->recipients as $recipient)
                                                <tr>
                                                    <td>{{ trim(($recipient->student?->student_id ? $recipient->student?->student_id . ' ' : '') . ($recipient->student?->full_name ?? $recipient->sponsor?->name ?? 'Recipient')) }}</td>
                                                    <td>{{ strtoupper($recipient->channel) }}</td>
                                                    <td>{{ $recipient->destination }}</td>
                                                    <td>{{ str($recipient->delivery_status)->headline() }}</td>
                                                    <td>{{ $recipient->sent_at?->format('M j, Y g:i A') ?: $recipient->failed_at?->format('M j, Y g:i A') ?: '-' }}</td>
                                                    <td>{{ $recipient->failure_reason ?: '-' }}</td>
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
                            <td style="padding:18px;">No message history yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
