<x-filament-panels::page>
    <form wire:submit="send" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit" icon="heroicon-m-paper-airplane">
            Send SMS
        </x-filament::button>
    </form>
</x-filament-panels::page>
