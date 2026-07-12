<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Gmail Sending Account</x-slot>
        <x-slot name="description">PUSMS sends through the connected scholarship Gmail account. Use {{ '{' }}{{ '{' }}name{{ '}' }}{{ '}' }} in the message to personalize each student or sponsor.</x-slot>

        @php
            $gmailAccounts = \App\Models\GmailAccount::query()->where('status', 'connected')->whereNull('revoked_at')->latest('last_used_at')->latest()->get();
        @endphp

        <div class="space-y-4">
            <div class="flex flex-wrap gap-3">
                <x-filament::button tag="a" href="{{ route('gmail.connect') }}" icon="heroicon-m-link">
                    Connect Gmail
                </x-filament::button>
                <x-filament::button tag="a" color="gray" href="{{ route('filament.admin.pages.gmail-settings') }}" icon="heroicon-m-cog-6-tooth">
                    Gmail Settings
                </x-filament::button>
            </div>

            @if ($gmailAccounts->isNotEmpty())
                <div style="overflow-x:auto;">
                    <table style="width:100%; min-width:720px; border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Email</th>
                                <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Name</th>
                                <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Last Used</th>
                                <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($gmailAccounts as $account)
                                <tr>
                                    <td style="border:1px solid #cbd5e1; padding:8px;">{{ $account->email }}</td>
                                    <td style="border:1px solid #cbd5e1; padding:8px;">{{ $account->name ?: '-' }}</td>
                                    <td style="border:1px solid #cbd5e1; padding:8px;">{{ $account->last_used_at?->format('M j, Y g:i A') ?: 'Not used yet' }}</td>
                                    <td style="border:1px solid #cbd5e1; padding:8px;">
                                        <form method="POST" action="{{ route('gmail.disconnect', $account) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="color:#b91c1c; font-weight:700;">Disconnect</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="border:1px solid #cbd5e1; padding:12px; background:#fff;">
                    No Gmail account is connected yet.
                </div>
            @endif
        </div>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">Message Placeholders</x-slot>
        <x-slot name="description">Use these to personalize bulk emails. PUSMS replaces them for each selected student or sponsor.</x-slot>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px;">
            @foreach ([
                'Student' => ['{{student_name}}', '{{first_name}}', '{{student_id}}', '{{programme}}', '{{level}}', '{{academic_year}}', '{{scholarship_name}}'],
                'Sponsor' => ['{{contact_person}}', '{{sponsor_name}}'],
                'General' => ['{{name}}', '{{recipient_name}}'],
            ] as $group => $placeholders)
                <div style="border:1px solid #cbd5e1; padding:12px; border-radius:8px;">
                    <strong>{{ $group }}</strong>
                    <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:8px;">
                        @foreach ($placeholders as $placeholder)
                            <button
                                type="button"
                                onclick="navigator.clipboard?.writeText('{{ $placeholder }}')"
                                style="border:1px solid #bfdbfe; background:#eff6ff; color:#005eea; border-radius:999px; padding:4px 8px; font-weight:700;"
                            >
                                {{ $placeholder }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>

    <form wire:submit="send" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap gap-3">
            <x-filament::button type="submit" icon="heroicon-m-paper-airplane">
                Send Through Scholarship Gmail
            </x-filament::button>

            <x-filament::button tag="a" color="gray" href="{{ route('filament.admin.pages.message-history') }}">
                View Message History
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
