<x-filament-panels::page>
    <form wire:submit="import" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit">
            Import Students
        </x-filament::button>
    </form>

    <x-filament::section class="mt-6">
        <x-slot name="heading">Import Mapping and Validation</x-slot>
        <x-slot name="description">Use programme codes for the cleanest imports. If a row uses programme_name, school_name, and department_name, the importer validates them and reports closest matches for misspellings.</x-slot>

        <div style="display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:16px;">
            <div style="border:1px solid #cbd5e1; padding:12px;">
                <strong>Valid programme values</strong>
                <div style="overflow:auto; max-height:420px; margin-top:8px;">
                    <table style="width:100%; min-width:520px; border-collapse:collapse;">
                        <thead>
                        <tr>
                            <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">#</th>
                            <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Programme value</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($this->importReferences['programmes'] as $programme)
                            <tr>
                                <td style="border:1px solid #cbd5e1; padding:8px;">{{ $loop->iteration }}</td>
                                <td style="border:1px solid #cbd5e1; padding:8px;">{{ $programme }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" style="border:1px solid #cbd5e1; padding:8px;">Create programmes before importing students.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="border:1px solid #cbd5e1; padding:12px;">
                <strong>Valid Type Of Scholarship values</strong>
                <ul style="margin-top:8px; padding-left:18px;">
                    @forelse ($this->importReferences['scholarshipProgrammes'] as $programme)
                        <li>{{ $programme }}</li>
                    @empty
                        <li>Create Types Of Scholarship before assigning awards.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
