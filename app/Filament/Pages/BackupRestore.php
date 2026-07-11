<?php

namespace App\Filament\Pages;

use App\Models\ScholarshipProgramme;
use App\Models\Sponsor;
use App\Models\Student;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class BackupRestore extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 20;

    protected static ?string $title = 'Backup & Restore';

    protected string $view = 'filament.pages.backup-restore';

    public array $data = [];

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Restore Database')
                    ->description('Restore from a backup created by this page or upload a SQLite backup file. A safety backup is created before every restore.')
                    ->schema([
                        Select::make('backup_path')
                            ->label('Existing Backup')
                            ->options(fn (): array => collect(Storage::disk('local')->files('backups'))
                                ->filter(fn (string $file): bool => str_ends_with($file, '.sqlite'))
                                ->sortDesc()
                                ->mapWithKeys(fn (string $file): array => [$file => basename($file)])
                                ->all())
                            ->searchable(),
                        FileUpload::make('uploaded_backup')
                            ->label('Upload SQLite Backup')
                            ->disk('local')
                            ->directory('uploaded-backups')
                            ->acceptedFileTypes(['application/octet-stream', 'application/x-sqlite3', 'application/vnd.sqlite3'])
                            ->visibility('private'),
                    ])
                    ->columns(2),
            ]);
    }

    public function createBackup(): void
    {
        $backupPath = $this->createBackupFile('manual');

        Notification::make()
            ->title('Backup created')
            ->body(basename($backupPath))
            ->success()
            ->send();
    }

    public function restoreBackup(): void
    {
        $state = $this->form->getState();
        $source = $state['uploaded_backup'] ?? $state['backup_path'] ?? null;

        if (! is_string($source) || ! Storage::disk('local')->exists($source)) {
            Notification::make()
                ->title('Choose a backup file first.')
                ->danger()
                ->send();

            return;
        }

        $this->createBackupFile('before-restore');
        File::copy(Storage::disk('local')->path($source), database_path('database.sqlite'));

        Notification::make()
            ->title('Database restored')
            ->body('Refresh the page to see restored data.')
            ->success()
            ->send();
    }

    public function getBackupsProperty(): array
    {
        return collect(Storage::disk('local')->files('backups'))
            ->filter(fn (string $file): bool => str_ends_with($file, '.sqlite'))
            ->sortDesc()
            ->map(fn (string $file): array => [
                'name' => basename($file),
                'size' => number_format(Storage::disk('local')->size($file) / 1024, 1).' KB',
                'path' => $file,
            ])
            ->values()
            ->all();
    }

    public function restoreDeleted(string $type, int $id): void
    {
        $model = match ($type) {
            'student' => Student::withTrashed()->find($id),
            'sponsor' => Sponsor::withTrashed()->find($id),
            'scholarship' => ScholarshipProgramme::withTrashed()->find($id),
            default => null,
        };

        if (! $model || ! method_exists($model, 'restore')) {
            Notification::make()
                ->title('Record not found')
                ->danger()
                ->send();

            return;
        }

        $model->restore();

        Notification::make()
            ->title('Record restored')
            ->success()
            ->send();
    }

    public function getRecycleBinProperty(): array
    {
        return [
            'Students' => Student::onlyTrashed()
                ->latest('deleted_at')
                ->limit(50)
                ->get()
                ->map(fn (Student $student): array => [
                    'type' => 'student',
                    'id' => $student->id,
                    'name' => "{$student->student_id} - {$student->full_name}",
                    'deleted_at' => $student->deleted_at?->format('M j, Y g:i A') ?? '-',
                ])
                ->all(),
            'Sponsors' => Sponsor::onlyTrashed()
                ->latest('deleted_at')
                ->limit(50)
                ->get()
                ->map(fn (Sponsor $sponsor): array => [
                    'type' => 'sponsor',
                    'id' => $sponsor->id,
                    'name' => $sponsor->name,
                    'deleted_at' => $sponsor->deleted_at?->format('M j, Y g:i A') ?? '-',
                ])
                ->all(),
            'Types Of Scholarship' => ScholarshipProgramme::onlyTrashed()
                ->latest('deleted_at')
                ->limit(50)
                ->get()
                ->map(fn (ScholarshipProgramme $programme): array => [
                    'type' => 'scholarship',
                    'id' => $programme->id,
                    'name' => $programme->name,
                    'deleted_at' => $programme->deleted_at?->format('M j, Y g:i A') ?? '-',
                ])
                ->all(),
        ];
    }

    private function createBackupFile(string $reason): string
    {
        Storage::disk('local')->makeDirectory('backups');

        $path = 'backups/pusms-'.$reason.'-'.now()->format('Ymd-His').'.sqlite';
        File::copy(database_path('database.sqlite'), Storage::disk('local')->path($path));

        return $path;
    }
}
