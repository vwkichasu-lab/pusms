<x-filament-panels::page>
    <form wire:submit="preview" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit">
            Preview Variables
        </x-filament::button>
    </form>
</x-filament-panels::page>
