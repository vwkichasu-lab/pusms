<?php

namespace App\Filament\Pages;

use App\Models\StudentImport;
use App\Models\Programme;
use App\Models\ScholarshipProgramme;
use App\Services\StudentBulkImportService;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ImportStudents extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Students';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.import-students';

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Bulk Add Students')
                    ->description('Upload a CSV file with these headers: student_id, first_name, middle_name, last_name, email, phone, programme_code or programme_name, school_name, department_name, level, admission_year, student_status, student_batch, graduation_year, alumni_status, alumni_badge, home_town, district, region.')
                    ->schema([
                        FileUpload::make('file')
                            ->label('Student CSV file')
                            ->required()
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                            ->directory('student-imports')
                            ->visibility('private'),
                    ]),
            ]);
    }

    public function import(StudentBulkImportService $service): void
    {
        $state = $this->form->getState();
        $path = $state['file'] ?? null;

        if (! is_string($path)) {
            Notification::make()->title('Upload a CSV file first.')->danger()->send();

            return;
        }

        $import = StudentImport::create([
            'original_filename' => basename($path),
            'stored_path' => $path,
            'uploaded_by' => Auth::id(),
            'status' => 'processing',
        ]);

        $result = $service->import($import);

        Notification::make()
            ->title("Imported {$result['successful']} of {$result['total']} rows")
            ->body($result['failed'] > 0 ? "{$result['failed']} rows need attention. Check the student_imports table." : 'All rows were imported successfully.')
            ->success()
            ->send();
    }

    /**
     * @return array{programmes: array<int, string>, scholarshipProgrammes: array<int, string>}
     */
    public function getImportReferencesProperty(): array
    {
        return [
            'programmes' => Programme::query()
                ->with('department.school')
                ->orderBy('name')
                ->limit(30)
                ->get()
                ->map(fn (Programme $programme): string => "{$programme->code} - {$programme->name} / {$programme->department?->name} / {$programme->department?->school?->name}")
                ->all(),
            'scholarshipProgrammes' => ScholarshipProgramme::query()
                ->orderBy('name')
                ->limit(30)
                ->get()
                ->map(fn (ScholarshipProgramme $programme): string => "{$programme->code} - {$programme->name}")
                ->all(),
        ];
    }
}
