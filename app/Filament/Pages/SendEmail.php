<?php

namespace App\Filament\Pages;

use App\Models\Communication;
use App\Models\GmailAccount;
use App\Models\Programme;
use App\Models\ScholarshipProgramme;
use App\Models\Sponsor;
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
use Illuminate\Http\RedirectResponse;
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
        'delivery_provider' => 'gmail',
        'recipient_group' => 'students',
        'send_all' => true,
        'send_all_sponsors' => true,
    ];

    public static function canAccess(): bool
    {
        return Auth::user()?->can('send email') ?? false;
    }

    public function mount(): void
    {
        $this->data['delivery_provider'] = 'gmail';
        $this->data['gmail_account_id'] = $this->defaultGmailAccountId();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Email Message')
                    ->description('Send from the connected scholarship Gmail account. The connection stays active until it is disconnected in Gmail Settings.')
                    ->schema([
                        Select::make('delivery_provider')
                            ->label('Send From')
                            ->options(fn (): array => [
                                'gmail' => 'Connected Scholarship Gmail',
                            ])
                            ->default('gmail')
                            ->required()
                            ->live(),
                        Select::make('gmail_account_id')
                            ->label('Gmail Account')
                            ->options(fn (): array => $this->connectedGmailAccounts()->pluck('email', 'id')->all())
                            ->default(fn (): ?int => $this->defaultGmailAccountId())
                            ->searchable()
                            ->helperText('If this field looks filled but validation complains, PUSMS will still use the connected scholarship Gmail automatically when you send.'),
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
                        TextInput::make('subject')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('reply_to')
                            ->label('Reply-To Email')
                            ->email()
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
                $this->sponsorFilterSection(),
            ]);
    }

    public function send(CommunicationService $communications): void
    {
        $state = $this->form->getState();
        $deliveryProvider = $state['delivery_provider'] ?? 'gmail';
        $gmailAccountId = $state['gmail_account_id'] ?? $this->defaultGmailAccountId();

        if (! $gmailAccountId || ! $this->connectedGmailAccounts()->whereKey($gmailAccountId)->exists()) {
            Notification::make()
                ->title('Connect Gmail first')
                ->body('Go to Gmail Settings and connect the scholarship Gmail account before sending email.')
                ->danger()
                ->send();

            return;
        }

        $deliveryProvider = 'gmail';

        $recipientGroup = $state['recipient_group'] ?? 'students';
        $students = in_array($recipientGroup, ['students', 'students_and_sponsors'], true)
            ? $this->studentsForState($state, 'email')->get()
            : collect();
        $sponsors = in_array($recipientGroup, ['sponsors', 'students_and_sponsors'], true)
            ? $this->sponsorsForState($state, 'email')->get()
            : collect();

        if ($students->isEmpty() && $sponsors->isEmpty()) {
            Notification::make()
                ->title('No selected recipients have email addresses.')
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
            'gmail_account_id' => $gmailAccountId,
            'status' => 'draft',
            'metadata' => [
                'source_page' => 'send_email',
                'delivery_provider' => $deliveryProvider,
                'reply_to' => $state['reply_to'] ?? null,
                'student_count' => $students->count(),
                'sponsor_count' => $sponsors->count(),
            ],
        ]);

        $communications->dispatch($communication, $students, $sponsors);
        $communication->refresh()->load('recipients');
        $sent = $communication->recipients->where('delivery_status', 'sent')->count();
        $failed = $communication->recipients->where('delivery_status', 'failed')->count();
        $queued = $communication->recipients->where('delivery_status', 'queued')->count();

        Notification::make()
            ->title('Email processed')
            ->body("Sent: {$sent}. Failed: {$failed}. Queued: {$queued}. Open Message History to see the reason for any failure.")
            ->status($failed > 0 ? 'warning' : 'success')
            ->send();

        $this->data['selected_student_ids'] = [];
        $this->data['selected_sponsor_ids'] = [];
    }

    public function openGmailCompose(): RedirectResponse
    {
        $state = $this->form->getState();
        $recipientGroup = $state['recipient_group'] ?? 'students';
        $students = in_array($recipientGroup, ['students', 'students_and_sponsors'], true)
            ? $this->studentsForState($state, 'email')->limit(100)->get()
            : collect();
        $sponsors = in_array($recipientGroup, ['sponsors', 'students_and_sponsors'], true)
            ? $this->sponsorsForState($state, 'email')->limit(100)->get()
            : collect();

        $emails = $students
            ->pluck('email')
            ->merge($sponsors->pluck('email'))
            ->filter()
            ->unique()
            ->values();

        if ($emails->isEmpty()) {
            Notification::make()
                ->title('No selected recipients have email addresses.')
                ->danger()
                ->send();

            return redirect()->route('filament.admin.pages.send-email');
        }

        if ($emails->count() > 100) {
            Notification::make()
                ->title('Only the first 100 recipients were added to Gmail compose.')
                ->warning()
                ->send();
        }

        $body = $state['message'] ?? '';

        return redirect()->away('https://mail.google.com/mail/?'.http_build_query([
            'view' => 'cm',
            'fs' => '1',
            'bcc' => $emails->implode(','),
            'su' => $state['subject'] ?? '',
            'body' => $body,
        ]));
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
                    ->options(fn (Get $get): array => $this->studentsForState($get, $channel, false)
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
            ->columns(3)
            ->visible(fn (Get $get): bool => in_array($get('recipient_group'), ['students', 'students_and_sponsors'], true));
    }

    private function sponsorFilterSection(): Section
    {
        return Section::make('Sponsors')
            ->description('Send to sponsor contact persons using the email address saved on the sponsor record.')
            ->schema([
                Toggle::make('send_all_sponsors')
                    ->label('Send to all sponsors with email addresses')
                    ->default(true)
                    ->live(),
                CheckboxList::make('selected_sponsor_ids')
                    ->label('Select Sponsors')
                    ->options(fn (Get $get): array => $this->sponsorsForState($get, 'email', false)
                        ->limit(500)
                        ->get()
                        ->mapWithKeys(fn (Sponsor $sponsor): array => [
                            $sponsor->id => "{$sponsor->name} | {$sponsor->contact_person} | {$sponsor->email}",
                        ])
                        ->all())
                    ->searchable()
                    ->bulkToggleable()
                    ->columns(1)
                    ->columnSpanFull()
                    ->visible(fn (Get $get): bool => ! (bool) $get('send_all_sponsors')),
            ])
            ->columns(1)
            ->visible(fn (Get $get): bool => in_array($get('recipient_group'), ['sponsors', 'students_and_sponsors'], true));
    }

    private function defaultGmailAccountId(): ?int
    {
        return $this->connectedGmailAccounts()
            ->latest('last_used_at')
            ->latest()
            ->value('id');
    }

    private function connectedGmailAccounts(): Builder
    {
        return GmailAccount::query()
            ->where('status', 'connected')
            ->whereNull('revoked_at');
    }

    /**
     * @param  array<string, mixed>|Get  $state
     */
    private function studentsForState(array|Get $state, string $channel, bool $applySelection = true): Builder
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
            ->when($applySelection && ! (bool) $get('send_all'), function (Builder $query) use ($get): Builder {
                $ids = collect($get('selected_student_ids') ?? [])->filter()->all();

                return $query->whereKey($ids ?: [0]);
            })
            ->orderBy('student_id');
    }

    /**
     * @param  array<string, mixed>|Get  $state
     */
    private function sponsorsForState(array|Get $state, string $channel, bool $applySelection = true): Builder
    {
        $get = fn (string $key): mixed => $state instanceof Get ? $state($key) : ($state[$key] ?? null);
        $destinationColumn = $channel === 'email' ? 'email' : 'phone';

        return Sponsor::query()
            ->whereNotNull($destinationColumn)
            ->where($destinationColumn, '!=', '')
            ->when($applySelection && ! (bool) $get('send_all_sponsors'), function (Builder $query) use ($get): Builder {
                $ids = collect($get('selected_sponsor_ids') ?? [])->filter()->all();

                return $query->whereKey($ids ?: [0]);
            })
            ->orderBy('name');
    }
}
