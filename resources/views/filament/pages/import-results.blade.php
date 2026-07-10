<x-filament-panels::page>
    <form wire:submit="preview" class="space-y-6">
        {{ $this->form }}

        <div class="flex gap-3">
            <x-filament::button type="submit">
                Preview Import
            </x-filament::button>

            <x-filament::button tag="a" color="gray" href="{{ asset('templates/result-import-template.csv') }}" target="_blank">
                Download Result Import Template
            </x-filament::button>
        </div>
    </form>

    @if ($this->currentImport())
        <x-filament::section class="mt-6">
            <x-slot name="heading">Import Preview</x-slot>
            <x-slot name="description">
                {{ $this->currentImport()->valid_rows }} valid row(s), {{ $this->currentImport()->invalid_rows }} row(s) needing attention.
            </x-slot>

            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th style="border:1px solid #cbd5e1; padding:8px;">Line</th>
                            <th style="border:1px solid #cbd5e1; padding:8px;">Status</th>
                            <th style="border:1px solid #cbd5e1; padding:8px;">Student ID</th>
                            <th style="border:1px solid #cbd5e1; padding:8px;">Academic Year</th>
                            <th style="border:1px solid #cbd5e1; padding:8px;">GPA</th>
                            <th style="border:1px solid #cbd5e1; padding:8px;">Errors</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (($this->currentImport()->preview_rows ?? []) as $row)
                            <tr>
                                <td style="border:1px solid #cbd5e1; padding:8px;">{{ $row['line'] }}</td>
                                <td style="border:1px solid #cbd5e1; padding:8px;">{{ $row['status'] }}</td>
                                <td style="border:1px solid #cbd5e1; padding:8px;">{{ $row['data']['student_id'] ?? '' }}</td>
                                <td style="border:1px solid #cbd5e1; padding:8px;">{{ $row['data']['academic_year'] ?? '' }}</td>
                                <td style="border:1px solid #cbd5e1; padding:8px;">{{ $row['data']['gpa'] ?? '' }}</td>
                                <td style="border:1px solid #cbd5e1; padding:8px;">{{ implode(', ', $row['errors'] ?? []) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-filament::button wire:click="confirm" class="mt-4">
                Confirm and Save Valid Results
            </x-filament::button>
        </x-filament::section>
    @endif
</x-filament-panels::page>
