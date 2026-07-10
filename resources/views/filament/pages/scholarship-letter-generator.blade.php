<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        <x-filament::button wire:click="openLetter">
            Generate A4 Scholarship Letter
        </x-filament::button>
    </div>
</x-filament-panels::page>
