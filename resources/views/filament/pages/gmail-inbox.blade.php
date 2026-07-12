<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Scholarship Gmail Inbox</x-slot>
        <x-slot name="description">Recent inbox messages from the connected scholarship Gmail account.</x-slot>

        <div class="space-y-4">
            @if ($this->inbox['needs_reconnect'])
                <div style="border:1px solid #d69e2e; background:#fffaf0; padding:12px;">
                    PUSMS can send email with the connected Gmail account, but Gmail has not yet approved inbox reading for this connection. Click Reconnect Gmail, choose the scholarship Gmail account again, and approve the inbox/read permission. After that, replies from students and sponsors will show here.
                    @if ($this->inbox['error'])
                        <div style="margin-top:8px; color:#92400e;">Reason: {{ $this->inbox['error'] }}</div>
                    @endif
                </div>
                <x-filament::button tag="a" href="{{ route('gmail.connect') }}" icon="heroicon-m-arrow-path">
                    Reconnect Gmail
                </x-filament::button>
            @else
                <div style="border:1px solid #cbd5e1; padding:12px; font-weight:800;">
                    Inbox messages: {{ $this->inbox['count'] }}
                </div>

                <div style="overflow:auto;">
                    <table style="width:100%; min-width:920px; border-collapse:collapse;">
                        <thead>
                        <tr>
                            <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">From</th>
                            <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Subject</th>
                            <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Preview</th>
                            <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($this->inbox['messages'] as $message)
                            <tr>
                                <td style="border:1px solid #cbd5e1; padding:8px;">{{ $message['from'] }}</td>
                                <td style="border:1px solid #cbd5e1; padding:8px; font-weight:700;">{{ $message['subject'] }}</td>
                                <td style="border:1px solid #cbd5e1; padding:8px;">{{ $message['snippet'] }}</td>
                                <td style="border:1px solid #cbd5e1; padding:8px;">{{ $message['date'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="border:1px solid #cbd5e1; padding:8px;">No inbox messages found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-panels::page>
