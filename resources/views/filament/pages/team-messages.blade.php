<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit="send" class="space-y-4">
            {{ $this->form }}
            <x-filament::button type="submit">Send Message</x-filament::button>
        </form>

        <x-filament::section>
            <x-slot name="heading">Inbox</x-slot>
            <div style="overflow:auto;">
                <table style="width:100%; min-width:820px; border-collapse:collapse;">
                    <tbody>
                    @forelse ($this->inbox as $message)
                        <tr wire:click="openMessage({{ $message->id }})" style="cursor:pointer; background:{{ $message->read_at ? '#fff' : '#eff6ff' }};">
                            <td style="border-bottom:1px solid #cbd5e1; padding:10px; font-weight:800;">{{ $message->sender?->name }}</td>
                            <td style="border-bottom:1px solid #cbd5e1; padding:10px;">{{ $message->subject }}</td>
                            <td style="border-bottom:1px solid #cbd5e1; padding:10px; color:#64748b;">{{ str($message->body)->squish()->limit(90) }}</td>
                            <td style="border-bottom:1px solid #cbd5e1; padding:10px; text-align:right;">{{ $message->created_at?->format('M j, g:i A') }}</td>
                        </tr>
                        @if ($this->openMessageId === $message->id)
                            <tr>
                                <td colspan="4" style="border-bottom:1px solid #cbd5e1; padding:14px; background:#f8fafc;">
                                    <div style="white-space:pre-wrap;">{{ $message->body }}</div>
                                    @if ($message->attachment_path)
                                        <div style="margin-top:10px;">
                                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($message->attachment_path) }}" target="_blank" style="color:#005eea; font-weight:800;">Open attachment</a>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr><td style="padding:12px;">No messages yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Sent</x-slot>
            <div style="overflow:auto;">
                <table style="width:100%; min-width:720px; border-collapse:collapse;">
                    <tbody>
                    @forelse ($this->sent as $message)
                        <tr>
                            <td style="border-bottom:1px solid #cbd5e1; padding:10px; font-weight:800;">To: {{ $message->recipient?->name }}</td>
                            <td style="border-bottom:1px solid #cbd5e1; padding:10px;">{{ $message->subject }}</td>
                            <td style="border-bottom:1px solid #cbd5e1; padding:10px; color:#64748b;">{{ str($message->body)->squish()->limit(100) }}</td>
                            <td style="border-bottom:1px solid #cbd5e1; padding:10px; text-align:right;">{{ $message->created_at?->format('M j, g:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td style="padding:12px;">No sent messages.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
