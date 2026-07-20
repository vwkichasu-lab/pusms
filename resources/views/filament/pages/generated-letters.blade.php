<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        <x-filament::section>
            <x-slot name="heading">Saved Scholarship Letters</x-slot>
            <x-slot name="description">Letters are saved by generated date, day, month, and year for preview later.</x-slot>

            <div style="display:flex; justify-content:flex-end; margin-bottom:12px;">
                <x-filament::button
                    color="danger"
                    wire:click="deleteSelectedLetters"
                    wire:confirm="Delete selected generated letters?"
                >
                    Delete selected
                </x-filament::button>
            </div>

            <div style="overflow:auto;">
                <table style="width:100%; min-width:980px; border-collapse:collapse;">
                    <thead>
                    <tr>
                        <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Select</th>
                        <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Generated At</th>
                        <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Day</th>
                        <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Month</th>
                        <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Year</th>
                        <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Student</th>
                        <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Scholarship</th>
                        <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Reference</th>
                        <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($this->letters as $letter)
                        <tr>
                            <td style="border:1px solid #cbd5e1; padding:8px;">
                                <input type="checkbox" wire:model.live="selectedLetters" value="{{ $letter->id }}">
                            </td>
                            <td style="border:1px solid #cbd5e1; padding:8px;">{{ $letter->generated_at?->format('M j, Y g:i A') }}</td>
                            <td style="border:1px solid #cbd5e1; padding:8px;">{{ $letter->generated_at?->format('l') }}</td>
                            <td style="border:1px solid #cbd5e1; padding:8px;">{{ $letter->generated_at?->format('F') }}</td>
                            <td style="border:1px solid #cbd5e1; padding:8px;">{{ $letter->generated_at?->format('Y') }}</td>
                            <td style="border:1px solid #cbd5e1; padding:8px;">{{ $letter->student?->student_id }} - {{ $letter->student?->full_name }}</td>
                            <td style="border:1px solid #cbd5e1; padding:8px;">{{ $letter->award?->scholarshipProgramme?->name }}</td>
                            <td style="border:1px solid #cbd5e1; padding:8px;">{{ $letter->reference }}</td>
                            <td style="border:1px solid #cbd5e1; padding:8px;">
                                <x-filament::button tag="a" size="sm" href="{{ route('student-scholarships.letter', ['award' => $letter->student_scholarship_id]) }}" target="_blank">
                                    Preview
                                </x-filament::button>
                                <x-filament::button
                                    color="danger"
                                    size="sm"
                                    wire:click="deleteLetter({{ $letter->id }})"
                                    wire:confirm="Delete this generated letter?"
                                >
                                    Delete
                                </x-filament::button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="border:1px solid #cbd5e1; padding:8px;">No generated letters saved yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
