<?php

namespace App\Filament\Pages;

use App\Models\GeneratedScholarshipLetter;
use App\Models\StudentScholarship;
use BackedEnum;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ScholarshipLetterGenerator extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.scholarship-letter-generator';

    public array $data = [];

    public function mount(): void
    {
        $this->data['award_id'] ??= null;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Scholarship Letter')
                    ->description('Select a scholarship beneficiary, then open the fixed A4 letterhead template. You can edit the letter body and signatory before printing.')
                    ->schema([
                        Select::make('award_id')
                            ->label('Search Student By ID Or Name')
                            ->options(fn (): array => StudentScholarship::query()
                                ->with(['student', 'scholarshipProgramme'])
                                ->whereHas('scholarshipProgramme', fn ($query) => $query->where('scholarship_type', 'pu_bursary'))
                                ->latest()
                                ->get()
                                ->mapWithKeys(fn (StudentScholarship $award): array => [
                                    $award->id => "{$award->student?->student_id} - {$award->student?->full_name} / {$award->scholarshipProgramme?->name}",
                                ])
                                ->all())
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn ($state) => $this->data['award_id'] = $state),
                        Placeholder::make('selected_student_details')
                            ->label('Selected Student Details')
                            ->content(function (Get $get): string {
                                $awardId = $get('award_id');
                                $award = $awardId ? StudentScholarship::query()->with(['student.programme', 'academicYear', 'scholarshipProgramme'])->find($awardId) : null;

                                if (! $award) {
                                    return 'Search and select a PU Bursary student to generate the letter.';
                                }

                                return "{$award->student?->student_id} - {$award->student?->full_name} | {$award->student?->programme?->name} | {$award->academicYear?->name} | {$award->coverage_percentage}%";
                            }),
                    ]),
            ]);
    }

    public function openLetter(): void
    {
        $state = $this->form->getRawState();
        $awardId = $state['award_id'] ?? $this->data['award_id'] ?? null;

        if (! $awardId) {
            Notification::make()
                ->title('Select a PU Bursary student first.')
                ->danger()
                ->send();

            return;
        }

        $award = StudentScholarship::query()->with(['student', 'academicYear', 'scholarshipProgramme'])->find($awardId);

        if ($award) {
            GeneratedScholarshipLetter::create([
                'student_scholarship_id' => $award->id,
                'student_id' => $award->student_id,
                'generated_by' => Auth::id(),
                'reference' => 'PU/BA/'.str_pad((string) $award->id, 3, '0', STR_PAD_LEFT).'/'.now()->format('m/y'),
                'letter_date' => now()->toDateString(),
                'signatory_name' => 'REV. AUGUSTINE ARTHUR-NORMAN',
                'signatory_title' => 'SCHOLARSHIP COORDINATOR',
                'body' => null,
                'generated_at' => now(),
            ]);
        }

        $this->redirect(route('student-scholarships.letter', ['award' => $awardId]));
    }

    public function getLetterUrlProperty(): ?string
    {
        $state = $this->form->getRawState();
        $awardId = $state['award_id'] ?? $this->data['award_id'] ?? null;

        return $awardId ? route('student-scholarships.letter', ['award' => $awardId]) : null;
    }
}
