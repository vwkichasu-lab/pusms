<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">SMS Placeholders</x-slot>
        <x-slot name="description">Use these to personalize bulk SMS messages. Keep SMS short because every placeholder becomes the real recipient detail.</x-slot>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px;">
            @foreach ([
                'Student' => ['{{student_name}}', '{{first_name}}', '{{student_id}}', '{{programme}}', '{{level}}', '{{scholarship_name}}'],
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
                Send SMS
            </x-filament::button>

            <x-filament::button tag="a" color="gray" href="{{ route('filament.admin.pages.message-history') }}">
                View Message History
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
