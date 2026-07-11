<?php

namespace App\Filament\Pages;

use App\Models\Communication;
use App\Models\Programme;
use App\Models\ScholarshipProgramme;
use App\Models\Student;
use App\Services\CommunicationService;
use BackedEnum;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class SendEmail extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|UnitEnum|null $navigationGroup = 'Messaging';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Send Email';

    protected string $view = 'filament.pages.send-email';

    /**
     * @var array<string, mixed>
     */
    public array $data = [
        'send_all' => true,
    ];

    public static function canAccess(): bool
    {
        return Auth::user()?->can('send email') ?? false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Email Message')
                    ->description('Email is sent from the configured scholarship Gmail/SMTP account to each selected student email address.')
                    ->schema([
                        TextInput::make('subject')
                            ->required()
                            ->maxLength(255),
                        FileUpload::make('attachment_path')
                            ->label('Attachment')
                            ->disk('public')
                            ->directory('communication-attachments')
                            ->visibility('public')
                            ->storeFileNamesIn('attachment_original_name')
                            ->downloadable()
                            ->openable(),
                        Textarea::make('message')
                            ->required()
                            ->rows(10)
                            ->placeholder('Dear {{student_name}}, ...')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                $this->studentFilterSection('email'),
            ]);
    }

    public function send(CommunicationService $communications): void
    {
        $state = $this->form->getState();
        $students = $this->studentsForState($state, 'email')->get();

        if ($students->isEmpty()) {
            Notification::make()
                ->title('No students with email addresses matched your selection.')
                ->danger()
                ->send();

            return;
        }

        $communication = Communication::create([
            'subject' => $state['subject'],
            'message' => $state['message'],
            'attachment_path' => $state['attachment_path'] ?? null,
            'attachment_original_name' => $state['attachment_original_name'] ?? null,
            'communication_type' => 'email',
            'created_by' => Auth::id(),
            'status' => 'draft',
            'metadata' => [
                'source_page' => 'send_email',
                'student_count' => $students->count(),
            ],
        ]);

        $communications->dispatch($communication, $students);
        $communication->refresh()->load('recipients');
        $sent = $communication->recipients->where('delivery_status', 'sent')->count();
        $failed = $communication->recipients->where('delivery_status', 'failed')->count();
        $queued = $communication->recipients->where('delivery_status', 'queued')->count();

        Notification::make()
            ->title("Email processed for {$students->count()} student(s)")
            ->body("Sent: {$sent}. Failed: {$failed}. Queued: {$queued}. Open Message History to see the reason for any failure.")
            ->status($failed > 0 ? 'warning' : 'success')
            ->send();

        $this->data['selected_student_ids'] = [];
    }

    private function studentFilterSection(string $channel): Section
    {
        return Section::make('Students')
            ->description('Choose all matching students or select individual students. Only students with an email address are listed here.')
            ->schema([
                Select::make('scholarship_type')
                    ->label('Type Of Scholarship')
                    ->options([
                        'pu_bursary' => 'PU Bursary',
                        'area' => 'Area Scholarship',
                        'copcef' => 'COPCEF',
                        'sponsor' => 'Institution / Sponsor Scholarship',
                        'other' => 'Other',
                    ])
                    ->searchable()
                    ->live(),
                Select::make('scholarship_programme_id')
                    ->label('Scholarship Name')
                    ->options(fn (Get $get): array => ScholarshipProgramme::query()
                        ->when($get('scholarship_type'), fn (Builder $query, string $type): Builder => $query->where('scholarship_type', $type))
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->live(),
                Select::make('programme_id')
                    ->label('Programme')
                    ->options(fn (): array => Programme::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->live(),
                Select::make('alumni_status')
                    ->label('Student Type')
                    ->options(['not_alumni' => 'Continuing Student', 'alumni' => 'Alumni'])
                    ->live(),
                Toggle::make('send_all')
                    ->label('Send to all matching students')
                    ->default(true)
                    ->live(),
                CheckboxList::make('selected_student_ids')
                    ->label('Select Students')
                    ->options(fn (Get $get): array => $this->studentsForState($get, $channel)
                        ->limit(500)
                        ->get()
                        ->mapWithKeys(fn (Student $student): array => [
                            $student->id => "{$student->student_id} | {$student->full_name} | {$student->email}",
                        ])
                        ->all())
                    ->searchable()
                    ->bulkToggleable()
                    ->columns(1)
                    ->columnSpanFull()
                    ->visible(fn (Get $get): bool => ! (bool) $get('send_all')),
            ])
            ->columns(3);
    }

    /**
     * @param  array<string, mixed>|Get  $state
     */
    private function studentsForState(array|Get $state, string $channel): Builder
    {
        $get = fn (string $key): mixed => $state instanceof Get ? $state($key) : ($state[$key] ?? null);
        $destinationColumn = $channel === 'email' ? 'email' : 'phone';

        return Student::query()
            ->with(['programme', 'level'])
            ->whereNotNull($destinationColumn)
            ->where($destinationColumn, '!=', '')
            ->when($get('programme_id'), fn (Builder $query, int|string $id): Builder => $query->where('programme_id', $id))
            ->when($get('alumni_status'), fn (Builder $query, string $status): Builder => $query->where('alumni_status', $status))
            ->when($get('scholarship_type'), fn (Builder $query, string $type): Builder => $query->whereHas('scholarships.scholarshipProgramme', fn (Builder $scholarship): Builder => $scholarship->where('scholarship_type', $type)))
            ->when($get('scholarship_programme_id'), fn (Builder $query, int|string $id): Builder => $query->whereHas('scholarships', fn (Builder $scholarship): Builder => $scholarship->where('scholarship_programme_id', $id)))
            ->when(! (bool) $get('send_all'), function (Builder $query) use ($get): Builder {
                $ids = collect($get('selected_student_ids') ?? [])->filter()->all();

                return $query->whereKey($ids ?: [0]);
            })
            ->orderBy('student_id');
    }
}
