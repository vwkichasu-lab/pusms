<?php

namespace App\Filament\Pages;

use App\Models\ResultImport;
use App\Services\ResultImportService;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ImportResults extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static string|UnitEnum|null $navigationGroup = 'Students';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.import-results';

    public array $data = [];

    public ?int $resultImportId = null;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Academic Result Import')
                    ->description('Upload CSV or XLSX with these headers: student_id, academic_year, gpa, score, credits_attempted, credits_passed, performance_status, remarks.')
                    ->schema([
                        FileUpload::make('file')
                            ->label('Academic result file')
                            ->required()
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                            ->directory('result-imports')
                            ->visibility('private'),
                    ]),
            ]);
    }

    public function preview(ResultImportService $service): void
    {
        $state = $this->form->getState();
        $path = $state['file'] ?? null;

        if (! is_string($path)) {
            Notification::make()->title('Upload a result CSV or XLSX file first.')->danger()->send();

            return;
        }

        $import = ResultImport::create([
            'original_filename' => basename($path),
            'stored_path' => $path,
            'uploaded_by' => Auth::id(),
            'status' => 'uploaded',
        ]);

        $service->preview($import);
        $this->resultImportId = $import->id;

        Notification::make()->title('Result import preview generated')->success()->send();
    }

    public function confirm(ResultImportService $service): void
    {
        $import = $this->currentImport();

        if (! $import) {
            Notification::make()->title('Generate a preview first.')->danger()->send();

            return;
        }

        $created = $service->confirm($import);

        Notification::make()->title("Saved {$created} validated result row(s)")->success()->send();
    }

    public function currentImport(): ?ResultImport
    {
        return $this->resultImportId ? ResultImport::find($this->resultImportId) : null;
    }
}
