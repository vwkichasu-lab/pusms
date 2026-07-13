<?php

namespace App\Filament\Pages;

use App\Models\InternalMessage;
use App\Models\SystemSetting;
use App\Models\User;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use UnitEnum;

class TeamMessages extends Page
{
    use WithFileUploads;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;

    protected static string|UnitEnum|null $navigationGroup = 'Messaging';

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'Team Messages';

    protected string $view = 'filament.pages.team-messages';

    public string $selectedConversation = '';

    public string $messageText = '';

    public $attachment = null;

    public string $search = '';

    public string $newChatSearch = '';

    public function mount(): void
    {
        $this->selectedConversation = (string) ($this->contacts->first()['id'] ?? '');
        $this->markSelectedAsRead();
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('Super Administrator')) {
            return true;
        }

        return self::teamMessagesEnabled();
    }

    public static function getNavigationBadge(): ?string
    {
        $userId = Auth::id();

        if (! $userId || ! self::canAccess()) {
            return null;
        }

        $count = InternalMessage::query()
            ->where('recipient_id', $userId)
            ->whereNull('read_at')
            ->whereNull('deleted_by_recipient_at')
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public function refreshChat(): void
    {
        $this->markSelectedAsRead();
    }

    public function selectConversation(string $conversation): void
    {
        $this->selectedConversation = $conversation;
        $this->markSelectedAsRead();
    }

    public function startConversation(int $userId): void
    {
        if (! User::query()->whereKey($userId)->where('is_active', true)->exists() || $userId === Auth::id()) {
            Notification::make()->title('Select a valid user')->danger()->send();

            return;
        }

        $this->selectedConversation = (string) $userId;
        $this->newChatSearch = '';
        $this->markSelectedAsRead();
    }

    public function sendMessage(): void
    {
        if ($this->selectedConversation === '') {
            Notification::make()->title('Select a user or All Users first')->warning()->send();

            return;
        }

        $this->validate([
            'messageText' => ['required_without:attachment', 'nullable', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);

        $body = trim($this->messageText);
        $attachmentPath = null;
        $attachmentName = null;

        if ($this->attachment) {
            $attachmentName = $this->attachment->getClientOriginalName();
            $attachmentPath = $this->attachment->store('internal-message-attachments', 'public');
        }

        if ($this->selectedConversation === 'all') {
            if (! Auth::user()?->hasRole('Super Administrator')) {
                Notification::make()->title('Only Super Administrators can message all users')->danger()->send();

                return;
            }

            $groupId = (string) Str::uuid();
            $recipients = User::query()
                ->whereKeyNot(Auth::id())
                ->where('is_active', true)
                ->pluck('id');

            if ($recipients->isEmpty()) {
                Notification::make()->title('No active users to send to')->warning()->send();

                return;
            }

            foreach ($recipients as $recipientId) {
                $this->createMessage((int) $recipientId, $body, $attachmentPath, $attachmentName, $groupId);
            }
        } else {
            $recipientId = (int) $this->selectedConversation;

            if (! User::query()->whereKey($recipientId)->where('is_active', true)->exists()) {
                Notification::make()->title('Select a valid user')->danger()->send();

                return;
            }

            $this->createMessage($recipientId, $body, $attachmentPath, $attachmentName);
        }

        $this->reset(['messageText', 'attachment']);
    }

    public function deleteMessage(int $messageId): void
    {
        $message = InternalMessage::query()
            ->where(function (Builder $query): void {
                $query->where('sender_id', Auth::id())
                    ->orWhere('recipient_id', Auth::id());
            })
            ->find($messageId);

        if (! $message) {
            return;
        }

        if ($message->sender_id === Auth::id()) {
            if ($message->broadcast_group_id) {
                InternalMessage::query()
                    ->where('sender_id', Auth::id())
                    ->where('broadcast_group_id', $message->broadcast_group_id)
                    ->update(['deleted_by_sender_at' => now()]);
            } else {
                $message->update(['deleted_by_sender_at' => now()]);
            }
        }

        if ($message->recipient_id === Auth::id()) {
            $message->update(['deleted_by_recipient_at' => now()]);
        }

        Notification::make()->title('Message deleted')->success()->send();
    }

    public function clearChat(): void
    {
        if ($this->selectedConversation === '') {
            return;
        }

        if ($this->selectedConversation === 'all') {
            InternalMessage::query()
                ->whereNotNull('broadcast_group_id')
                ->where('sender_id', Auth::id())
                ->update(['deleted_by_sender_at' => now()]);

            InternalMessage::query()
                ->whereNotNull('broadcast_group_id')
                ->where('recipient_id', Auth::id())
                ->update(['deleted_by_recipient_at' => now()]);
        } else {
            $otherUserId = (int) $this->selectedConversation;

            InternalMessage::query()
                ->whereNull('broadcast_group_id')
                ->where('sender_id', Auth::id())
                ->where('recipient_id', $otherUserId)
                ->update(['deleted_by_sender_at' => now()]);

            InternalMessage::query()
                ->whereNull('broadcast_group_id')
                ->where('sender_id', $otherUserId)
                ->where('recipient_id', Auth::id())
                ->update(['deleted_by_recipient_at' => now()]);
        }

        Notification::make()->title('Chat cleared')->success()->send();
    }

    public function getContactsProperty(): Collection
    {
        $contactIds = InternalMessage::query()
            ->whereNull('broadcast_group_id')
            ->where(function (Builder $query): void {
                $query->where(function (Builder $sent): void {
                    $sent->where('sender_id', Auth::id())
                        ->whereNull('deleted_by_sender_at');
                })->orWhere(function (Builder $received): void {
                    $received->where('recipient_id', Auth::id())
                        ->whereNull('deleted_by_recipient_at');
                });
            })
            ->get(['sender_id', 'recipient_id'])
            ->flatMap(fn (InternalMessage $message): array => [
                (int) $message->sender_id,
                (int) $message->recipient_id,
            ])
            ->reject(fn (int $id): bool => $id === Auth::id())
            ->unique()
            ->values();

        $users = User::query()
            ->whereKey($contactIds)
            ->when($this->search, function (Builder $query, string $search): Builder {
                return $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->get()
            ->sortByDesc(fn (User $user): int => $this->lastMessageWithUser($user->id)?->getKey() ?? 0)
            ->values();

        $contacts = $users->map(fn (User $user): array => [
            'id' => (string) $user->id,
            'name' => $user->name,
            'subtitle' => $user->username ? '@'.$user->username : $user->email,
            'avatar' => $user->profile_photo_url,
            'initials' => $this->initials($user->name),
            'unread' => $this->unreadCountForUser($user->id),
            'last' => $this->lastMessageWithUser($user->id),
        ]);

        if (Auth::user()?->hasRole('Super Administrator') || $this->lastBroadcastMessage()) {
            $contacts->prepend([
                'id' => 'all',
                'name' => 'All Users',
                'subtitle' => 'Broadcast to every active user',
                'avatar' => null,
                'initials' => 'ALL',
                'unread' => $this->broadcastUnreadCount(),
                'last' => $this->lastBroadcastMessage(),
            ]);
        }

        return $contacts->values();
    }

    public function getNewChatResultsProperty(): Collection
    {
        if (filled($this->newChatSearch) === false) {
            return collect();
        }

        $search = trim($this->newChatSearch);

        return User::query()
            ->whereKeyNot(Auth::id())
            ->where('is_active', true)
            ->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(8)
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'subtitle' => $user->username ? '@'.$user->username : $user->email,
                'avatar' => $user->profile_photo_url,
                'initials' => $this->initials($user->name),
            ]);
    }

    public function getConversationMessagesProperty(): Collection
    {
        if ($this->selectedConversation === '') {
            return collect();
        }

        if ($this->selectedConversation === 'all') {
            return $this->broadcastMessages();
        }

        return $this->directMessages((int) $this->selectedConversation);
    }

    public function getSelectedContactProperty(): array
    {
        if ($this->selectedConversation !== '') {
            $existingContact = $this->contacts->firstWhere('id', $this->selectedConversation);

            if ($existingContact) {
                return $existingContact;
            }

            if ($this->selectedConversation !== 'all') {
                $user = User::query()->find((int) $this->selectedConversation);

                if ($user) {
                    return [
                        'id' => (string) $user->id,
                        'name' => $user->name,
                        'subtitle' => $user->username ? '@'.$user->username : $user->email,
                        'avatar' => $user->profile_photo_url,
                        'initials' => $this->initials($user->name),
                        'unread' => 0,
                        'last' => null,
                    ];
                }
            }
        }

        return ['id' => '', 'name' => 'Select a chat', 'subtitle' => 'Search a username to start messaging', 'avatar' => null, 'initials' => ''];
    }

    private function createMessage(int $recipientId, string $body, ?string $attachmentPath, ?string $attachmentName, ?string $broadcastGroupId = null): void
    {
        InternalMessage::query()->create([
            'sender_id' => Auth::id(),
            'recipient_id' => $recipientId,
            'broadcast_group_id' => $broadcastGroupId,
            'subject' => Str::limit($body ?: ($attachmentName ?: 'Team message'), 120, ''),
            'body' => $body ?: 'Attachment',
            'attachment_path' => $attachmentPath,
            'attachment_original_name' => $attachmentName,
        ]);
    }

    private function directMessages(int $otherUserId): Collection
    {
        return InternalMessage::query()
            ->with(['sender', 'recipient'])
            ->whereNull('broadcast_group_id')
            ->where(function (Builder $query) use ($otherUserId): void {
                $query->where(function (Builder $sent) use ($otherUserId): void {
                    $sent->where('sender_id', Auth::id())
                        ->where('recipient_id', $otherUserId)
                        ->whereNull('deleted_by_sender_at');
                })->orWhere(function (Builder $received) use ($otherUserId): void {
                    $received->where('sender_id', $otherUserId)
                        ->where('recipient_id', Auth::id())
                        ->whereNull('deleted_by_recipient_at');
                });
            })
            ->oldest()
            ->limit(300)
            ->get();
    }

    private function broadcastMessages(): Collection
    {
        return InternalMessage::query()
            ->with(['sender', 'recipient'])
            ->whereNotNull('broadcast_group_id')
            ->where(function (Builder $query): void {
                $query->where(function (Builder $sent): void {
                    $sent->where('sender_id', Auth::id())
                        ->whereNull('deleted_by_sender_at');
                })->orWhere(function (Builder $received): void {
                    $received->where('recipient_id', Auth::id())
                        ->whereNull('deleted_by_recipient_at');
                });
            })
            ->oldest()
            ->limit(500)
            ->get()
            ->unique(fn (InternalMessage $message): string => $message->broadcast_group_id && $message->sender_id === Auth::id()
                ? 'broadcast-'.$message->broadcast_group_id
                : 'message-'.$message->id)
            ->values();
    }

    private function unreadCountForUser(int $userId): int
    {
        return InternalMessage::query()
            ->whereNull('broadcast_group_id')
            ->where('sender_id', $userId)
            ->where('recipient_id', Auth::id())
            ->whereNull('read_at')
            ->whereNull('deleted_by_recipient_at')
            ->count();
    }

    private function broadcastUnreadCount(): int
    {
        return InternalMessage::query()
            ->whereNotNull('broadcast_group_id')
            ->where('recipient_id', Auth::id())
            ->whereNull('read_at')
            ->whereNull('deleted_by_recipient_at')
            ->count();
    }

    private function lastMessageWithUser(int $userId): ?InternalMessage
    {
        return InternalMessage::query()
            ->whereNull('broadcast_group_id')
            ->where(function (Builder $query) use ($userId): void {
                $query->where(function (Builder $sent) use ($userId): void {
                    $sent->where('sender_id', Auth::id())
                        ->where('recipient_id', $userId)
                        ->whereNull('deleted_by_sender_at');
                })->orWhere(function (Builder $received) use ($userId): void {
                    $received->where('sender_id', $userId)
                        ->where('recipient_id', Auth::id())
                        ->whereNull('deleted_by_recipient_at');
                });
            })
            ->latest()
            ->first();
    }

    private function lastBroadcastMessage(): ?InternalMessage
    {
        return InternalMessage::query()
            ->whereNotNull('broadcast_group_id')
            ->where(function (Builder $query): void {
                $query->where('sender_id', Auth::id())
                    ->orWhere('recipient_id', Auth::id());
            })
            ->latest()
            ->first();
    }

    private function markSelectedAsRead(): void
    {
        if (! Auth::id()) {
            return;
        }

        if ($this->selectedConversation === '') {
            return;
        }

        if ($this->selectedConversation === 'all') {
            InternalMessage::query()
                ->whereNotNull('broadcast_group_id')
                ->where('recipient_id', Auth::id())
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return;
        }

        InternalMessage::query()
            ->whereNull('broadcast_group_id')
            ->where('sender_id', (int) $this->selectedConversation)
            ->where('recipient_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    private function initials(string $name): string
    {
        return Str::of($name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => Str::upper(Str::substr($part, 0, 1)))
            ->join('');
    }

    public function attachmentUrl(InternalMessage $message): ?string
    {
        if (! $message->attachment_path) {
            return null;
        }

        return Storage::disk('public')->url($message->attachment_path);
    }

    private static function teamMessagesEnabled(): bool
    {
        $setting = SystemSetting::query()->where('key', 'allow_team_messages')->first();

        return $setting ? (bool) $setting->value : false;
    }
}
