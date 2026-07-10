<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Create Backup</x-slot>
            <x-slot name="description">Creates a full SQLite database backup for restoring accidental deletes or edits later.</x-slot>

            <x-filament::button wire:click="createBackup">
                Create Backup Now
            </x-filament::button>
        </x-filament::section>

        <form wire:submit="restoreBackup" class="space-y-4">
            {{ $this->form }}

            <x-filament::button type="submit" color="danger" wire:confirm="This will replace the current database with the selected backup. Continue?">
                Restore Selected Backup
            </x-filament::button>
        </form>

        <x-filament::section>
            <x-slot name="heading">Available Backups</x-slot>

            <div style="overflow:auto; max-height:360px;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                    <tr>
                        <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Backup</th>
                        <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Size</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($this->backups as $backup)
                        <tr>
                            <td style="border:1px solid #cbd5e1; padding:8px;">{{ $backup['name'] }}</td>
                            <td style="border:1px solid #cbd5e1; padding:8px;">{{ $backup['size'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" style="border:1px solid #cbd5e1; padding:8px;">No backups created yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
