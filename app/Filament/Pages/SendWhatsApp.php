<?php

namespace App\Filament\Pages;

use App\Models\Communication;
use App\Models\Programme;
use App\Models\ScholarshipProgramme;
use App\Models\Sponsor;
use App\Models\Student;
use App\Services\Notifications\Support\PhoneNumber;
use App\Services\TemplateVariableService;
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

class SendWhatsApp extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Messaging';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Send WhatsApp';

    protected static ?string $slug = 'send-whatsapp';

    protected string $view = 'filament.pages.send-whatsapp';

    /**
     * @var array<string, mixed>
     */
    public array $data = [
        'recipient_group' => 'students',
        'send_all' => true,
        'send_all_sponsors' => true,
        'message' => 'Dear {{name}}, ',
    ];

    /**
     * @var array<int, array{name: string, phone: string, message: string, url: string}>
     */
    public array $preparedRecipients = [];

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return (bool) ($user?->can('send whatsapp') || $user?->can('send email') || $user?->can('use email and whatsapp pages'));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('WhatsApp Message')
                    ->description('PUSMS prepares each personalized message and opens WhatsApp for the selected phone number. Press Send inside WhatsApp to deliver it.')
                    ->schema([
                        Select::make('recipient_group')
                            ->label('Send To')
                            ->options([
                                'students' => 'Students',
                                'sponsors' => 'Sponsors',
                                'students_and_sponsors' => 'Students and Sponsors',
                            ])
                            ->default('students')
                            ->required()
                            ->live(),
                        Textarea::make('message')
                            ->required()
                            ->rows(6)
                            ->extraInputAttributes(['id' => 'pusms-message-field'])
                            ->placeholder('Dear {{student_name}}, ...')
                            ->helperText('Use {{student_name}} for students, {{contact_person}} for sponsor contacts, or {{name}} for either recipient type.')
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
                            ->label('Prepare for all matching students')
                            ->default(true)
                            ->live(),
                        CheckboxList::make('selected_student_ids')
                            ->label('Select Students')
                            ->options(fn (Get $get): array => $this->studentsForState($get, false)
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
                    ->columns(3)
                    ->visible(fn (Get $get): bool => in_array($get('recipient_group'), ['students', 'students_and_sponsors'], true)),
                Section::make('Sponsors')
                    ->description('Prepare WhatsApp messages for sponsor contact persons using the phone number saved on the sponsor record.')
                    ->schema([
                        Toggle::make('send_all_sponsors')
                            ->label('Prepare for all sponsors with phone numbers')
                            ->default(true)
                            ->live(),
                        CheckboxList::make('selected_sponsor_ids')
                            ->label('Select Sponsors')
                            ->options(fn (Get $get): array => $this->sponsorsForState($get, false)
                                ->limit(500)
                                ->get()
                                ->mapWithKeys(fn (Sponsor $sponsor): array => [
                                    $sponsor->id => "{$sponsor->name} | {$sponsor->contact_person} | {$sponsor->phone}",
                                ])
                                ->all())
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(1)
                            ->columnSpanFull()
                            ->visible(fn (Get $get): bool => ! (bool) $get('send_all_sponsors')),
                    ])
                    ->visible(fn (Get $get): bool => in_array($get('recipient_group'), ['sponsors', 'students_and_sponsors'], true)),
            ]);
    }

    public function prepare(TemplateVariableService $templates): void
    {
        $state = $this->form->getState();
        $recipientGroup = $state['recipient_group'] ?? 'students';
        $students = in_array($recipientGroup, ['students', 'students_and_sponsors'], true)
            ? $this->studentsForState($state)->get()
            : collect();
        $sponsors = in_array($recipientGroup, ['sponsors', 'students_and_sponsors'], true)
            ? $this->sponsorsForState($state)->get()
            : collect();

        if ($students->isEmpty() && $sponsors->isEmpty()) {
            Notification::make()
                ->title('No selected recipients have phone numbers.')
                ->danger()
                ->send();

            return;
        }

        $communication = Communication::create([
            'subject' => 'WhatsApp Message',
            'message' => $state['message'],
            'communication_type' => 'whatsapp',
            'created_by' => Auth::id(),
            'status' => 'prepared',
            'metadata' => [
                'source_page' => 'send_whatsapp',
                'student_count' => $students->count(),
                'sponsor_count' => $sponsors->count(),
            ],
        ]);

        $prepared = [];

        foreach ($students as $student) {
            $prepared[] = $this->prepareRecipient($communication, $templates, $state['message'], $student, null);
        }

        foreach ($sponsors as $sponsor) {
            $prepared[] = $this->prepareRecipient($communication, $templates, $state['message'], null, $sponsor);
        }

        $this->preparedRecipients = array_values(array_filter($prepared));

        Notification::make()
            ->title('WhatsApp messages prepared')
            ->body('Open each WhatsApp link and press Send inside WhatsApp.')
            ->success()
            ->send();
    }

    private function prepareRecipient(
        Communication $communication,
        TemplateVariableService $templates,
        string $messageTemplate,
        ?Student $student,
        ?Sponsor $sponsor,
    ): ?array {
        $phone = $student?->phone ?? $sponsor?->phone;

        if (! filled($phone)) {
            return null;
        }

        try {
            $normalized = PhoneNumber::normalize($phone, '233');
        } catch (\Throwable) {
            return null;
        }

        $message = $templates->render($messageTemplate, $student, $sponsor);
        $digits = ltrim($normalized, '+');
        $url = 'https://wa.me/'.$digits.'?'.http_build_query(['text' => $message]);

        $communication->recipients()->create([
            'student_id' => $student?->id,
            'sponsor_id' => $sponsor?->id,
            'channel' => 'whatsapp',
            'destination' => $normalized,
            'delivery_status' => 'prepared',
            'provider_response' => [
                'provider' => 'whatsapp_link',
                'url' => $url,
            ],
        ]);

        return [
            'name' => $student?->full_name ?? $sponsor?->contact_person ?? $sponsor?->name ?? 'Recipient',
            'phone' => $normalized,
            'message' => $message,
            'url' => $url,
        ];
    }

    /**
     * @param  array<string, mixed>|Get  $state
     */
    private function studentsForState(array|Get $state, bool $applySelection = true): Builder
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
            ->when($applySelection && ! (bool) $get('send_all'), function (Builder $query) use ($get): Builder {
                $ids = collect($get('selected_student_ids') ?? [])->filter()->all();

                return $query->whereKey($ids ?: [0]);
            })
            ->orderBy('student_id');
    }

    /**
     * @param  array<string, mixed>|Get  $state
     */
    private function sponsorsForState(array|Get $state, bool $applySelection = true): Builder
    {
        $get = fn (string $key): mixed => $state instanceof Get ? $state($key) : ($state[$key] ?? null);

        return Sponsor::query()
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->when($applySelection && ! (bool) $get('send_all_sponsors'), function (Builder $query) use ($get): Builder {
                $ids = collect($get('selected_sponsor_ids') ?? [])->filter()->all();

                return $query->whereKey($ids ?: [0]);
            })
            ->orderBy('name');
    }
}
