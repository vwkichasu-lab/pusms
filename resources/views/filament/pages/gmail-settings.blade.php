<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Gmail OAuth Connection</x-slot>
            <x-slot name="description">Administrators connect Gmail securely through Google. PUSMS never stores a Gmail password. Reconnect once to approve inbox read access for the in-system Inbox page.</x-slot>

            <div class="flex flex-wrap gap-3">
                <x-filament::button tag="a" href="{{ route('gmail.connect') }}" icon="heroicon-m-link">
                    Connect Gmail
                </x-filament::button>
                <x-filament::button tag="a" color="gray" href="{{ route('gmail.connect') }}" icon="heroicon-m-arrow-path">
                    Reconnect Gmail
                </x-filament::button>
                <x-filament::button tag="a" color="gray" href="{{ route('filament.admin.pages.send-email') }}" icon="heroicon-m-envelope">
                    Send Email
                </x-filament::button>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Connected Accounts</x-slot>

            <div style="overflow-x:auto;">
                <table style="width:100%; min-width:980px; border-collapse:collapse;">
                    <thead>
                    <tr>
                        <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Status</th>
                        <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Gmail Address</th>
                        <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Connected At</th>
                        <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Token Expiry</th>
                        <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Last Used</th>
                        <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($this->gmailAccounts as $account)
                        <tr>
                            <td style="border:1px solid #cbd5e1; padding:8px;">{{ str($account->status)->headline() }}</td>
                            <td style="border:1px solid #cbd5e1; padding:8px;">{{ $account->email }}</td>
                            <td style="border:1px solid #cbd5e1; padding:8px;">{{ $account->connected_at?->format('M j, Y g:i A') ?: $account->created_at?->format('M j, Y g:i A') }}</td>
                            <td style="border:1px solid #cbd5e1; padding:8px;">
                                @if ($account->status !== 'connected')
                                    Not active
                                @elseif ($account->token_expires_at?->isPast())
                                    Expired, will refresh on next send
                                @else
                                    {{ $account->token_expires_at?->format('M j, Y g:i A') ?: 'Unknown' }}
                                @endif
                            </td>
                            <td style="border:1px solid #cbd5e1; padding:8px;">{{ $account->last_used_at?->format('M j, Y g:i A') ?: '-' }}</td>
                            <td style="border:1px solid #cbd5e1; padding:8px;">
                                @if ($account->status === 'connected')
                                    <x-filament::button
                                        color="danger"
                                        size="sm"
                                        wire:click="disconnect({{ $account->id }})"
                                        wire:confirm="Disconnect this Gmail account from PUSMS?"
                                    >
                                        Disconnect
                                    </x-filament::button>
                                @else
                                    <x-filament::button tag="a" size="sm" color="gray" href="{{ route('gmail.connect') }}">
                                        Reconnect
                                    </x-filament::button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="border:1px solid #cbd5e1; padding:8px;">Not Connected</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
