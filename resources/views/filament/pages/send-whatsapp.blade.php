<x-filament-panels::page>
    <form wire:submit="prepare" class="space-y-6">
        {{ $this->form }}

        <x-filament::section>
            <details>
                <summary style="cursor:pointer; font-weight:800; color:#005eea;">View placeholders</summary>
                <p style="margin-top:8px; color:#526b88;">Click a placeholder to insert it into your WhatsApp message. PUSMS replaces it with each recipient's real details before opening WhatsApp.</p>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px; margin-top:12px;">
                    @foreach ([
                        'Student' => ['student_name', 'first_name', 'student_id', 'programme', 'level', 'academic_year', 'scholarship_name'],
                        'Sponsor' => ['contact_person', 'sponsor_name'],
                        'General' => ['name', 'recipient_name'],
                    ] as $group => $placeholders)
                        <div style="border:1px solid #cbd5e1; padding:12px; border-radius:8px;">
                            <strong>{{ $group }}</strong>
                            <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:8px;">
                                @foreach ($placeholders as $placeholderName)
                                    @php($placeholder = str_repeat('{', 2) . $placeholderName . str_repeat('}', 2))
                                    <button
                                        type="button"
                                        onclick="window.insertPusmsPlaceholder({{ Js::from($placeholder) }})"
                                        style="border:1px solid #bfdbfe; background:#eff6ff; color:#005eea; border-radius:999px; padding:4px 8px; font-weight:700;"
                                    >
                                        {{ $placeholder }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </details>
        </x-filament::section>

        <script>
            window.insertPusmsPlaceholder = function (placeholder) {
                const target = document.getElementById('pusms-message-field');

                if (!target) {
                    navigator.clipboard?.writeText(placeholder);
                    return;
                }

                const start = target.selectionStart ?? target.value.length;
                const end = target.selectionEnd ?? target.value.length;
                target.value = target.value.slice(0, start) + placeholder + target.value.slice(end);
                target.focus();
                target.selectionStart = target.selectionEnd = start + placeholder.length;
                target.dispatchEvent(new Event('input', { bubbles: true }));
                target.dispatchEvent(new Event('change', { bubbles: true }));
                navigator.clipboard?.writeText(placeholder);
            };
        </script>

        <div class="flex flex-wrap gap-3">
            <x-filament::button type="submit" icon="heroicon-m-chat-bubble-left-right">
                Prepare WhatsApp Messages
            </x-filament::button>

            <x-filament::button tag="a" color="gray" href="{{ route('filament.admin.pages.message-history') }}">
                View Message History
            </x-filament::button>
        </div>
    </form>

    @if ($preparedRecipients)
        <x-filament::section>
            <x-slot name="heading">Prepared WhatsApp Messages</x-slot>
            <x-slot name="description">Open each row, review the message in WhatsApp, then press Send.</x-slot>

            <div style="overflow-x:auto;">
                <table style="width:100%; min-width:860px; border-collapse:collapse;">
                    <thead>
                    <tr>
                        <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Recipient</th>
                        <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Phone</th>
                        <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Message Preview</th>
                        <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($preparedRecipients as $recipient)
                        <tr>
                            <td style="border:1px solid #cbd5e1; padding:8px;">{{ $recipient['name'] }}</td>
                            <td style="border:1px solid #cbd5e1; padding:8px;">{{ $recipient['phone'] }}</td>
                            <td style="border:1px solid #cbd5e1; padding:8px;">{{ str($recipient['message'])->limit(120) }}</td>
                            <td style="border:1px solid #cbd5e1; padding:8px;">
                                <x-filament::button tag="a" href="{{ $recipient['url'] }}" target="_blank" size="sm">
                                    Open WhatsApp
                                </x-filament::button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
