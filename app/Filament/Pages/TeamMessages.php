<?php

namespace App\Filament\Pages;

use App\Models\InternalMessage;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class TeamMessages extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;

    protected static string|UnitEnum|null $navigationGroup = 'Messaging';

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'Team Messages';

    protected string $view = 'filament.pages.team-messages';

    public array $data = [];

    public ?int $openMessageId = null;

    public static function getNavigationBadge(): ?string
    {
        $userId = Auth::id();

        if (! $userId) {
            return null;
        }

        $count = InternalMessage::query()
            ->where('recipient_id', $userId)
            ->whereNull('read_at')
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Send Message')
                    ->description('Send a message or resource to another PUSMS user.')
                    ->schema([
                        Select::make('recipient_id')
                            ->label('Send To')
                            ->options(fn (): array => User::query()
                                ->whereKeyNot(Auth::id())
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->required()
                            ->searchable(),
                        TextInput::make('subject')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('body')
                            ->label('Message')
                            ->required()
                            ->rows(5)
                            ->maxLength(5000)
                            ->columnSpanFull(),
                        FileUpload::make('attachment_path')
                            ->label('Attach Resource')
                            ->disk('public')
                            ->directory('internal-message-attachments')
                            ->visibility('public')
                            ->downloadable()
                            ->openable(),
                    ])
                    ->columns(2),
            ]);
    }

    public function send(): void
    {
        $state = $this->form->getState();

        InternalMessage::query()->create([
            'sender_id' => Auth::id(),
            'recipient_id' => $state['recipient_id'],
            'subject' => $state['subject'],
            'body' => $state['body'],
            'attachment_path' => $state['attachment_path'] ?? null,
            'attachment_original_name' => $state['attachment_path'] ? basename((string) $state['attachment_path']) : null,
        ]);

        $this->form->fill();

        Notification::make()
            ->title('Message sent')
            ->success()
            ->send();
    }

    public function openMessage(int $messageId): void
    {
        $this->openMessageId = $this->openMessageId === $messageId ? null : $messageId;

        $message = InternalMessage::query()
            ->where('recipient_id', Auth::id())
            ->whereKey($messageId)
            ->whereNull('read_at')
            ->first();

        if ($message) {
            $message->update(['read_at' => now()]);
        }
    }

    public function getInboxProperty()
    {
        return InternalMessage::query()
            ->with('sender')
            ->where('recipient_id', Auth::id())
            ->latest()
            ->limit(30)
            ->get();
    }

    public function getSentProperty()
    {
        return InternalMessage::query()
            ->with('recipient')
            ->where('sender_id', Auth::id())
            ->latest()
            ->limit(20)
            ->get();
    }
}
