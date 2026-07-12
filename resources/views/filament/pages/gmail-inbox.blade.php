<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Scholarship Gmail Inbox</x-slot>
        <x-slot name="description">Only scholarship-related replies and messages from the connected scholarship Gmail account.</x-slot>

        <div class="space-y-4">
            @if ($this->inbox['needs_reconnect'])
                <div style="border:1px solid #d69e2e; background:#fffaf0; padding:12px;">
                    PUSMS can send email with the connected Gmail account, but Gmail has not yet approved inbox reading/management for this connection. Click Reconnect Gmail, choose the scholarship Gmail account again, and approve the inbox read/manage permission. After that, replies from students and sponsors will show here and can be deleted from PUSMS.
                    @if ($this->inbox['error'])
                        <div style="margin-top:8px; color:#92400e;">Reason: {{ $this->inbox['error'] }}</div>
                    @endif
                </div>
                <x-filament::button tag="a" href="{{ route('gmail.connect') }}" icon="heroicon-m-arrow-path">
                    Reconnect Gmail
                </x-filament::button>
            @else
                <div style="display:flex; flex-wrap:wrap; gap:10px; justify-content:space-between; align-items:center; border:1px solid #cbd5e1; padding:12px;">
                    <strong>Scholarship inbox messages: {{ $this->inbox['count'] }}</strong>
                    <div style="display:flex; flex-wrap:wrap; gap:8px;">
                        <x-filament::button type="button" color="gray" wire:click="selectAllVisible">
                            Select all
                        </x-filament::button>
                        <x-filament::button type="button" color="gray" wire:click="clearSelection">
                            Clear selection
                        </x-filament::button>
                        <x-filament::button type="button" color="danger" wire:click="deleteSelected" wire:confirm="Move selected scholarship inbox messages to Gmail Trash?">
                            Delete selected
                        </x-filament::button>
                        <x-filament::button type="button" color="danger" wire:click="deleteAllVisible" wire:confirm="Move all visible filtered scholarship inbox messages to Gmail Trash?">
                            Delete all filtered
                        </x-filament::button>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:minmax(320px, 1fr) minmax(360px, 1.2fr); gap:16px; align-items:start;">
                    <div style="border:1px solid #cbd5e1;">
                        @forelse ($this->inbox['messages'] as $message)
                            <div style="display:grid; grid-template-columns:32px minmax(0, 1fr); gap:8px; border-bottom:1px solid #e2e8f0; padding:10px 12px; background:#fff;">
                                <input
                                    type="checkbox"
                                    wire:model.live="selectedMessageIds"
                                    value="{{ $message['id'] }}"
                                    aria-label="Select inbox message"
                                    style="margin-top:6px;"
                                >
                                <button
                                    type="button"
                                    wire:click="selectMessage('{{ $message['id'] }}')"
                                    style="display:grid; grid-template-columns:minmax(150px, .7fr) minmax(0, 1.3fr) auto; gap:12px; width:100%; border:0; padding:0; background:transparent; text-align:left; cursor:pointer;"
                                >
                                    <span style="font-weight:800; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $message['from'] }}</span>
                                    <span style="min-width:0;">
                                        <strong style="display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $message['subject'] }}</strong>
                                        <span style="display:block; color:#526b88; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $message['snippet'] }}</span>
                                    </span>
                                    <span style="color:#526b88; white-space:nowrap;">{{ $message['date'] }}</span>
                                </button>
                            </div>
                        @empty
                            <div style="padding:12px;">No scholarship-related inbox messages found.</div>
                        @endforelse
                    </div>

                    <div style="border:1px solid #cbd5e1; min-height:240px; padding:16px; background:#fff;">
                        @if ($this->selectedMessage)
                            <div style="display:grid; gap:6px; padding-bottom:12px; border-bottom:1px solid #e2e8f0;">
                                <strong style="font-size:1.1rem;">{{ $this->selectedMessage['subject'] }}</strong>
                                <span><strong>From:</strong> {{ $this->selectedMessage['from'] }}</span>
                                <span><strong>To:</strong> {{ $this->selectedMessage['to'] }}</span>
                                <span><strong>Date:</strong> {{ $this->selectedMessage['date'] }}</span>
                            </div>
                            <div style="white-space:pre-wrap; margin-top:14px; line-height:1.55;">
                                {{ $this->selectedMessage['body'] ?: $this->selectedMessage['snippet'] }}
                            </div>
                        @else
                            <div style="color:#526b88;">Select a scholarship message to read the full content.</div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-panels::page>
