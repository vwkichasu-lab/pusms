<?php

namespace App\Filament\Pages;

use App\Models\Communication;
use App\Models\Department;
use App\Models\MessageCampaign;
use App\Models\MessageCampaignRecipient;
use App\Models\MessageTemplate;
use App\Models\Programme;
use App\Models\School;
use App\Models\ScholarshipProgramme;
use App\Models\Sponsor;
use App\Models\Student;
use App\Models\User;
use App\Services\TemplateVariableService;
use App\Services\WhatsAppPhoneNumberService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class CommunicationCentre extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Messaging';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Communication Centre';

    protected static ?string $slug = 'communication-centre';

    protected string $view = 'filament.pages.communication-centre';

    public string $activeTab = 'compose';

    public string $campaignName = '';

    public string $recipientType = 'students';

    public ?int $templateId = null;

    public string $subject = '';

    public string $messageBody = '';

    public bool $selectAllMatching = true;

    public array $selectedRecipientKeys = [];

    public array $filters = [
        'search' => '',
        'student_id' => '',
        'scholarship_type' => '',
        'scholarship_status' => '',
        'programme_id' => '',
        'faculty_id' => '',
        'department_id' => '',
        'level_id' => '',
        'academic_year' => '',
        'country' => '',
        'region' => '',
        'district' => '',
        'student_status' => '',
        'sponsor_type' => '',
        'email' => '',
        'phone' => '',
        'role' => '',
    ];

    public string $templateName = '';

    public string $templateRecipientType = 'all';

    public string $templateSubject = '';

    public string $templateBody = '';

    public ?int $editingTemplateId = null;

    public ?int $selectedCampaignId = null;

    public string $campaignStatusFilter = '';

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return (bool) ($user?->can('communication.view') || $user?->can('send whatsapp') || $user?->can('send email'));
    }

    public function mount(): void
    {
        $this->campaignName = 'WhatsApp Campaign '.now()->format('Y-m-d H:i');
        $this->messageBody = "Hello {{first_name}},\n\n";
        $this->seedDefaultTemplates();
    }

    public function updatedTemplateId(?int $value): void
    {
        if (! $value) {
            return;
        }

        $template = MessageTemplate::query()->find($value);

        if (! $template) {
            return;
        }

        $this->subject = (string) $template->subject;
        $this->messageBody = (string) $template->message;
    }

    public function createCampaign(WhatsAppPhoneNumberService $phones, TemplateVariableService $templates): void
    {
        if (! Auth::user()?->can('communication.create') && ! Auth::user()?->can('send whatsapp')) {
            abort(403);
        }

        $this->validate([
            'campaignName' => ['required', 'string', 'max:255'],
            'recipientType' => ['required', 'string'],
            'messageBody' => ['required', 'string', 'max:5000'],
        ]);

        $recipients = $this->selectedRecipients();

        if ($recipients->isEmpty()) {
            Notification::make()->title('Select at least one recipient')->danger()->send();

            return;
        }

        $campaign = MessageCampaign::create([
            'campaign_name' => $this->campaignName,
            'recipient_type' => $this->recipientType,
            'subject' => $this->subject,
            'message_body' => $this->messageBody,
            'channel' => 'whatsapp',
            'status' => 'Ready',
            'created_by' => Auth::id(),
            'filters' => $this->filters,
        ]);

        $valid = 0;
        $invalid = 0;

        foreach ($recipients as $recipient) {
            $phoneResult = $phones->normalizeGhana($recipient['phone'] ?? null);
            $message = $templates->render($this->messageBody, $recipient['student'] ?? null, $recipient['sponsor'] ?? null);
            $normalized = $phoneResult['normalized'] ?? null;
            $url = $normalized ? 'https://wa.me/'.$normalized.'?'.http_build_query(['text' => $message]) : null;
            $status = $phoneResult['valid'] ? 'Pending' : 'Invalid Number';

            MessageCampaignRecipient::create([
                'campaign_id' => $campaign->id,
                'recipient_type' => $recipient['type'],
                'recipient_id' => $recipient['id'],
                'recipient_name' => $recipient['name'],
                'phone_number' => $recipient['phone'],
                'normalized_phone' => $normalized,
                'personalized_message' => $message,
                'whatsapp_url' => $url,
                'status' => $status,
                'validation_error' => $phoneResult['error'] ?? null,
            ]);

            $phoneResult['valid'] ? $valid++ : $invalid++;
        }

        $campaign->update([
            'total_recipients' => $recipients->count(),
            'valid_recipients' => $valid,
            'invalid_recipients' => $invalid,
            'pending_count' => $valid,
        ]);

        Communication::create([
            'subject' => $this->subject ?: $this->campaignName,
            'message' => $this->messageBody,
            'communication_type' => 'whatsapp',
            'created_by' => Auth::id(),
            'status' => 'prepared',
            'metadata' => ['message_campaign_id' => $campaign->id],
        ]);

        $this->selectedCampaignId = $campaign->id;
        $this->activeTab = 'campaigns';

        Notification::make()
            ->title('WhatsApp campaign created')
            ->body('WhatsApp messages are not sent automatically. Open each recipient and press Send inside WhatsApp.')
            ->success()
            ->send();
    }

    public function saveDraft(): void
    {
        MessageCampaign::create([
            'campaign_name' => $this->campaignName ?: 'Draft WhatsApp Campaign',
            'recipient_type' => $this->recipientType,
            'subject' => $this->subject,
            'message_body' => $this->messageBody ?: 'Draft message',
            'channel' => 'whatsapp',
            'status' => 'Draft',
            'created_by' => Auth::id(),
            'filters' => $this->filters,
        ]);

        Notification::make()->title('Draft saved')->success()->send();
    }

    public function saveTemplate(): void
    {
        if (! Auth::user()?->can('communication.manage_templates')) {
            abort(403);
        }

        $this->validate([
            'templateName' => ['required', 'string', 'max:255'],
            'templateBody' => ['required', 'string'],
        ]);

        MessageTemplate::query()->updateOrCreate(
            ['id' => $this->editingTemplateId],
            [
                'name' => $this->templateName,
                'recipient_type' => $this->templateRecipientType,
                'subject' => $this->templateSubject,
                'message' => $this->templateBody,
                'channel' => 'whatsapp',
                'created_by' => Auth::id(),
                'status' => 'active',
                'is_active' => true,
            ],
        );

        $this->resetTemplateForm();
        Notification::make()->title('Template saved')->success()->send();
    }

    public function editTemplate(int $id): void
    {
        $template = MessageTemplate::query()->findOrFail($id);
        $this->editingTemplateId = $template->id;
        $this->templateName = $template->name;
        $this->templateRecipientType = $template->recipient_type ?? 'all';
        $this->templateSubject = (string) $template->subject;
        $this->templateBody = $template->message;
        $this->activeTab = 'templates';
    }

    public function duplicateTemplate(int $id): void
    {
        $template = MessageTemplate::query()->findOrFail($id);
        MessageTemplate::create([
            'name' => $template->name.' Copy',
            'recipient_type' => $template->recipient_type ?? 'all',
            'subject' => $template->subject,
            'message' => $template->message,
            'channel' => 'whatsapp',
            'created_by' => Auth::id(),
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    public function deleteTemplate(int $id): void
    {
        MessageTemplate::query()->whereKey($id)->delete();
    }

    public function openCampaign(int $id): void
    {
        $this->selectedCampaignId = $id;
        $this->activeTab = 'campaigns';
    }

    public function markOpened(int $recipientId): void
    {
        $recipient = MessageCampaignRecipient::query()->findOrFail($recipientId);

        if ($recipient->status === 'Pending') {
            $recipient->update(['status' => 'Opened', 'opened_at' => now()]);
            $this->refreshCampaignCounts($recipient->campaign);
        }
    }

    public function markSent(int $recipientId): void
    {
        $recipient = MessageCampaignRecipient::query()->findOrFail($recipientId);
        $recipient->update(['status' => 'Marked as Sent', 'marked_sent_at' => now()]);
        $this->refreshCampaignCounts($recipient->campaign);
    }

    public function skipRecipient(int $recipientId): void
    {
        $recipient = MessageCampaignRecipient::query()->findOrFail($recipientId);
        $recipient->update(['status' => 'Skipped', 'skipped_at' => now()]);
        $this->refreshCampaignCounts($recipient->campaign);
    }

    public function cancelCampaign(int $id): void
    {
        MessageCampaign::query()->whereKey($id)->update(['status' => 'Cancelled']);
    }

    public function getRecipientRowsProperty(): Collection
    {
        return $this->matchingRecipients()->take(200);
    }

    public function getSelectedCountProperty(): int
    {
        return $this->selectedRecipients()->count();
    }

    public function getPreviewMessageProperty(): string
    {
        $first = $this->selectedRecipients()->first();

        return app(TemplateVariableService::class)->render(
            $this->messageBody,
            $first['student'] ?? null,
            $first['sponsor'] ?? null,
        );
    }

    public function getTemplatesProperty(): Collection
    {
        return MessageTemplate::query()
            ->where('channel', 'whatsapp')
            ->where('is_active', true)
            ->latest()
            ->get();
    }

    public function getCampaignsProperty(): Collection
    {
        return MessageCampaign::query()
            ->with('creator')
            ->where('channel', 'whatsapp')
            ->when($this->campaignStatusFilter, fn (Builder $query): Builder => $query->where('status', $this->campaignStatusFilter))
            ->latest()
            ->limit(50)
            ->get();
    }

    public function getSelectedCampaignProperty(): ?MessageCampaign
    {
        if (! $this->selectedCampaignId) {
            return $this->campaigns->first();
        }

        return MessageCampaign::query()
            ->with('recipients')
            ->find($this->selectedCampaignId);
    }

    public function getPlaceholdersProperty(): array
    {
        return app(TemplateVariableService::class)->placeholders();
    }

    public function getSelectOptionsProperty(): array
    {
        return [
            'programmes' => Programme::query()->orderBy('name')->pluck('name', 'id')->all(),
            'departments' => Department::query()->orderBy('name')->pluck('name', 'id')->all(),
            'faculties' => School::query()->orderBy('name')->pluck('name', 'id')->all(),
            'scholarships' => ScholarshipProgramme::query()->orderBy('name')->pluck('name', 'id')->all(),
        ];
    }

    private function selectedRecipients(): Collection
    {
        $recipients = $this->matchingRecipients();

        if ($this->selectAllMatching) {
            return $recipients;
        }

        return $recipients->whereIn('key', $this->selectedRecipientKeys)->values();
    }

    private function matchingRecipients(): Collection
    {
        return match ($this->recipientType) {
            'students' => $this->studentRecipients(),
            'sponsors' => $this->sponsorRecipients(),
            'committee' => $this->userRecipients(['Committee Chairman', 'Committee Member'], 'committee'),
            'staff' => $this->userRecipients([], 'staff'),
            'all' => $this->studentRecipients()
                ->merge($this->sponsorRecipients())
                ->merge($this->userRecipients([], 'staff')),
            default => collect(),
        };
    }

    private function studentRecipients(): Collection
    {
        return Student::query()
            ->with(['programme.department.school', 'level', 'scholarships.scholarshipProgramme'])
            ->when($this->filters['search'], fn (Builder $query, string $search): Builder => $query->where(function (Builder $inner) use ($search): void {
                $inner->where('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            }))
            ->when($this->filters['student_id'], fn (Builder $query, string $value): Builder => $query->where('student_id', 'like', "%{$value}%"))
            ->when($this->filters['programme_id'], fn (Builder $query, string $id): Builder => $query->where('programme_id', $id))
            ->when($this->filters['department_id'], fn (Builder $query, string $id): Builder => $query->whereHas('programme', fn (Builder $programme): Builder => $programme->where('department_id', $id)))
            ->when($this->filters['faculty_id'], fn (Builder $query, string $id): Builder => $query->whereHas('programme.department', fn (Builder $department): Builder => $department->where('school_id', $id)))
            ->when($this->filters['level_id'], fn (Builder $query, string $id): Builder => $query->where('level_id', $id))
            ->when($this->filters['country'], fn (Builder $query, string $value): Builder => $query->where('country', 'like', "%{$value}%"))
            ->when($this->filters['region'], fn (Builder $query, string $value): Builder => $query->where('region', 'like', "%{$value}%"))
            ->when($this->filters['district'], fn (Builder $query, string $value): Builder => $query->where('district', 'like', "%{$value}%"))
            ->when($this->filters['student_status'], fn (Builder $query, string $value): Builder => $query->where('student_status', $value))
            ->when($this->filters['scholarship_type'], fn (Builder $query, string $value): Builder => $query->whereHas('scholarships.scholarshipProgramme', fn (Builder $scholarship): Builder => $scholarship->where('scholarship_type', $value)))
            ->when($this->filters['scholarship_status'], fn (Builder $query, string $value): Builder => $query->whereHas('scholarships', fn (Builder $scholarship): Builder => $scholarship->where('status', $value)))
            ->limit(500)
            ->get()
            ->map(fn (Student $student): array => [
                'key' => 'student-'.$student->id,
                'type' => 'student',
                'id' => $student->id,
                'name' => $student->full_name,
                'phone' => $student->phone,
                'email' => $student->email,
                'student' => $student,
                'sponsor' => null,
            ]);
    }

    private function sponsorRecipients(): Collection
    {
        return Sponsor::query()
            ->when($this->filters['search'], fn (Builder $query, string $search): Builder => $query->where(function (Builder $inner) use ($search): void {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%");
            }))
            ->when($this->filters['sponsor_type'], fn (Builder $query, string $value): Builder => $query->where('sponsor_type', 'like', "%{$value}%"))
            ->when($this->filters['email'], fn (Builder $query, string $value): Builder => $query->where('email', 'like', "%{$value}%"))
            ->when($this->filters['phone'], fn (Builder $query, string $value): Builder => $query->where('phone', 'like', "%{$value}%"))
            ->when($this->filters['student_status'], fn (Builder $query, string $value): Builder => $query->where('status', $value))
            ->limit(500)
            ->get()
            ->map(fn (Sponsor $sponsor): array => [
                'key' => 'sponsor-'.$sponsor->id,
                'type' => 'sponsor',
                'id' => $sponsor->id,
                'name' => $sponsor->contact_person ?: $sponsor->name,
                'phone' => $sponsor->phone,
                'email' => $sponsor->email,
                'student' => null,
                'sponsor' => $sponsor,
            ]);
    }

    private function userRecipients(array $roles, string $type): Collection
    {
        return User::query()
            ->when($roles !== [], fn (Builder $query): Builder => $query->role($roles))
            ->when($this->filters['search'], fn (Builder $query, string $search): Builder => $query->where('name', 'like', "%{$search}%"))
            ->when($this->filters['email'], fn (Builder $query, string $value): Builder => $query->where('email', 'like', "%{$value}%"))
            ->where('is_active', true)
            ->limit(500)
            ->get()
            ->map(fn (User $user): array => [
                'key' => $type.'-'.$user->id,
                'type' => $type,
                'id' => $user->id,
                'name' => $user->name,
                'phone' => '',
                'email' => $user->email,
                'student' => null,
                'sponsor' => null,
            ]);
    }

    private function refreshCampaignCounts(MessageCampaign $campaign): void
    {
        $campaign->load('recipients');
        $statuses = $campaign->recipients->pluck('status');
        $valid = $statuses->reject(fn (string $status): bool => $status === 'Invalid Number')->count();
        $sent = $statuses->where(fn (string $status): bool => $status === 'Marked as Sent')->count();

        $campaign->update([
            'valid_recipients' => $valid,
            'invalid_recipients' => $statuses->where(fn (string $status): bool => $status === 'Invalid Number')->count(),
            'pending_count' => $statuses->where(fn (string $status): bool => $status === 'Pending')->count(),
            'opened_count' => $statuses->where(fn (string $status): bool => $status === 'Opened')->count(),
            'sent_count' => $sent,
            'skipped_count' => $statuses->where(fn (string $status): bool => $status === 'Skipped')->count(),
            'status' => $sent > 0 && $sent === $valid ? 'Completed' : 'In Progress',
            'completed_at' => $sent > 0 && $sent === $valid ? now() : null,
        ]);
    }

    private function resetTemplateForm(): void
    {
        $this->editingTemplateId = null;
        $this->templateName = '';
        $this->templateRecipientType = 'all';
        $this->templateSubject = '';
        $this->templateBody = '';
    }

    private function seedDefaultTemplates(): void
    {
        $templates = [
            'Scholarship meeting' => "Hello {{first_name}},\n\nYou are invited to an important scholarship meeting.\n\nDate: {{meeting_date}}\nTime: {{meeting_time}}\nVenue: {{venue}}\n\nThank you.",
            'Scholarship renewal reminder' => "Dear {{student_name}},\n\nPlease remember to complete your scholarship renewal for {{academic_year}}.\n\nThank you.",
            'Document submission reminder' => "Dear {{name}},\n\nKindly submit the required scholarship documents to the Scholarship Office.",
            'Appreciation message' => "Dear {{name}},\n\nThank you for your support of Pentecost University scholarship students.",
            'Sponsor update' => "Dear {{contact_person}},\n\nWe are sharing an update on your sponsored scholarship beneficiaries.",
            'Interview invitation' => "Dear {{student_name}},\n\nYou are invited for a scholarship interview.\n\nDate: {{meeting_date}}\nTime: {{meeting_time}}\nVenue: {{venue}}",
            'Payment or allowance notification' => "Dear {{student_name}},\n\nYour scholarship payment or allowance update is ready. Please contact the Scholarship Office for details.",
        ];

        foreach ($templates as $name => $body) {
            MessageTemplate::query()->firstOrCreate(
                ['name' => $name, 'channel' => 'whatsapp'],
                [
                    'recipient_type' => 'all',
                    'subject' => $name,
                    'message' => $body,
                    'created_by' => Auth::id(),
                    'status' => 'active',
                    'is_active' => true,
                ],
            );
        }
    }
}
