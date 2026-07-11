<?php

namespace App\Filament\Pages;

use App\Models\Communication;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class MessageHistory extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|UnitEnum|null $navigationGroup = 'Messaging';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Message History';

    protected string $view = 'filament.pages.message-history';

    /**
     * @var array<int, bool>
     */
    public array $selectedMessages = [];

    public ?int $expandedMessageId = null;

    public static function canAccess(): bool
    {
        return Auth::user()?->can('use email and sms pages') ?? false;
    }

    /**
     * @return array<int, Communication>
     */
    public function getMessagesProperty(): array
    {
        return Communication::query()
            ->with(['creator', 'gmailAccount', 'recipients.student', 'recipients.sponsor'])
            ->whereIn('communication_type', ['email', 'sms'])
            ->latest()
            ->limit(30)
            ->get()
            ->all();
    }

    public function deleteMessage(int $messageId): void
    {
        $message = Communication::query()
            ->whereIn('communication_type', ['email', 'sms'])
            ->find($messageId);

        if (! $message) {
            return;
        }

        $message->delete();
        unset($this->selectedMessages[$messageId]);

        Notification::make()
            ->title('Message history deleted')
            ->success()
            ->send();
    }

    public function deleteSelected(): void
    {
        $ids = collect($this->selectedMessages)
            ->filter()
            ->keys()
            ->map(fn (int|string $id): int => (int) $id)
            ->all();

        if ($ids === []) {
            Notification::make()
                ->title('Select at least one message first')
                ->warning()
                ->send();

            return;
        }

        Communication::query()
            ->whereIn('communication_type', ['email', 'sms'])
            ->whereKey($ids)
            ->delete();

        $this->selectedMessages = [];

        Notification::make()
            ->title('Selected message history deleted')
            ->success()
            ->send();
    }

    public function toggleMessage(int $messageId): void
    {
        $this->expandedMessageId = $this->expandedMessageId === $messageId ? null : $messageId;
    }
}
