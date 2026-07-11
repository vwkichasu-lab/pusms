<x-filament-panels::page>
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
