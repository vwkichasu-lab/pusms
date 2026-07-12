<?php

namespace App\Filament\Pages;

use App\Models\GmailAccount;
use App\Services\GmailOAuthService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class GmailInbox extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    protected static string|UnitEnum|null $navigationGroup = 'Messaging';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Inbox';

    protected string $view = 'filament.pages.gmail-inbox';

    public array $inbox = [
        'count' => 0,
        'messages' => [],
        'needs_reconnect' => false,
        'error' => null,
    ];

    public ?array $selectedMessage = null;

    /**
     * @var array<int, string>
     */
    public array $selectedMessageIds = [];

    public static function canAccess(): bool
    {
        return Auth::user()?->can('send email') ?? false;
    }

    public function mount(GmailOAuthService $gmail): void
    {
        $this->refreshInbox($gmail);
    }

    public function refreshInbox(GmailOAuthService $gmail): void
    {
        $account = GmailAccount::query()
            ->where('status', 'connected')
            ->whereNull('revoked_at')
            ->latest('last_used_at')
            ->latest()
            ->first();

        if (! $account) {
            $this->inbox = ['count' => 0, 'messages' => [], 'needs_reconnect' => true, 'error' => 'Connect Gmail first.'];

            return;
        }

        try {
            $this->inbox = $gmail->inboxPreview($account) + ['error' => null];
            $this->selectedMessageIds = [];
        } catch (\Throwable $exception) {
            $this->inbox = ['count' => 0, 'messages' => [], 'needs_reconnect' => true, 'error' => $exception->getMessage()];

            Notification::make()
                ->title('Inbox needs Gmail reconnect')
                ->body('Reconnect Gmail and approve the new read permission.')
                ->warning()
                ->send();
        }
    }

    public function selectAllVisible(): void
    {
        $this->selectedMessageIds = collect($this->inbox['messages'] ?? [])
            ->pluck('id')
            ->filter()
            ->values()
            ->all();
    }

    public function clearSelection(): void
    {
        $this->selectedMessageIds = [];
    }

    public function selectMessage(string $messageId, GmailOAuthService $gmail): void
    {
        $account = GmailAccount::query()
            ->where('status', 'connected')
            ->whereNull('revoked_at')
            ->latest('last_used_at')
            ->latest()
            ->first();

        if (! $account) {
            $this->selectedMessage = null;

            Notification::make()
                ->title('Connect Gmail first')
                ->danger()
                ->send();

            return;
        }

        try {
            $this->selectedMessage = $gmail->inboxMessage($account, $messageId);
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('Could not open inbox message')
                ->body('Reconnect Gmail and try again.')
                ->danger()
                ->send();
        }
    }

    public function deleteSelected(GmailOAuthService $gmail): void
    {
        $this->trashMessages($gmail, $this->selectedMessageIds, 'selected');
    }

    public function deleteAllVisible(GmailOAuthService $gmail): void
    {
        $messageIds = collect($this->inbox['messages'] ?? [])
            ->pluck('id')
            ->filter()
            ->values()
            ->all();

        $this->trashMessages($gmail, $messageIds, 'visible scholarship inbox');
    }

    /**
     * @param  array<int, string>  $messageIds
     */
    private function trashMessages(GmailOAuthService $gmail, array $messageIds, string $label): void
    {
        if ($messageIds === []) {
            Notification::make()
                ->title('No inbox messages selected')
                ->warning()
                ->send();

            return;
        }

        $account = GmailAccount::query()
            ->where('status', 'connected')
            ->whereNull('revoked_at')
            ->latest('last_used_at')
            ->latest()
            ->first();

        if (! $account) {
            Notification::make()
                ->title('Connect Gmail first')
                ->danger()
                ->send();

            return;
        }

        try {
            $count = $gmail->trashInboxMessages($account, $messageIds);
            $this->selectedMessageIds = [];
            $this->selectedMessage = null;
            $this->refreshInbox($gmail);

            Notification::make()
                ->title('Inbox messages moved to Gmail Trash')
                ->body("Deleted {$count} {$label} message(s).")
                ->success()
                ->send();
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('Could not delete inbox messages')
                ->body('Reconnect Gmail and approve inbox management permission.')
                ->danger()
                ->send();
        }
    }
}
