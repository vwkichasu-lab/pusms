<?php

namespace App\Filament\Pages;

use App\Models\Communication;
use App\Models\Programme;
use App\Models\ScholarshipProgramme;
use App\Models\Student;
use App\Services\CommunicationService;
use BackedEnum;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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

class SendSms extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Messaging';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Send SMS';

    protected string $view = 'filament.pages.send-sms';

    /**
     * @var array<string, mixed>
     */
    public array $data = [
        'send_all' => true,
    ];

    public static function canAccess(): bool
    {
        return Auth::user()?->can('send sms') ?? false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('SMS Message')
                    ->description('SMS is sent through Hubtel to each selected student phone number.')
                    ->schema([
                        Textarea::make('message')
                            ->required()
                            ->rows(6)
                            ->maxLength((int) config('notifications.sms.max_length', 918))
                            ->placeholder('Dear {{student_name}}, ...')
                            ->columnSpanFull(),
                    ]),
                Section::make('Students')
                    ->description('Choose all matching students or select individual students. Only students with phone numbers are listed here.')
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
                            ->options(fn (Get $get): array => $this->studentsForState($get)
                                ->limit(500)
                                ->get()
                                ->mapWithKeys(fn (Student $student): array => [
                                    $student->id => "{$student->student_id} | {$student->full_name} | {$student->phone}",
                                ])
                                ->all())
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(1)
                            ->columnSpanFull()
                            ->visible(fn (Get $get): bool => ! (bool) $get('send_all')),
                    ])
                    ->columns(3),
            ]);
    }

    public function send(CommunicationService $communications): void
    {
        $state = $this->form->getState();
        $students = $this->studentsForState($state)->get();

        if ($students->isEmpty()) {
            Notification::make()
                ->title('No students with phone numbers matched your selection.')
                ->danger()
                ->send();

            return;
        }

        $communication = Communication::create([
            'subject' => 'SMS Message',
            'message' => $state['message'],
            'communication_type' => 'sms',
            'created_by' => Auth::id(),
            'status' => 'draft',
            'metadata' => [
                'source_page' => 'send_sms',
                'student_count' => $students->count(),
            ],
        ]);

        $communications->dispatch($communication, $students);

        Notification::make()
            ->title("SMS queued for {$students->count()} student(s)")
            ->body('Delivery status is saved in the database for audit and retry tracking.')
            ->success()
            ->send();

        $this->data['selected_student_ids'] = [];
    }

    /**
     * @param  array<string, mixed>|Get  $state
     */
    private function studentsForState(array|Get $state): Builder
    {
        $get = fn (string $key): mixed => $state instanceof Get ? $state($key) : ($state[$key] ?? null);

        return Student::query()
            ->with(['programme', 'level'])
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
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
