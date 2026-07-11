<?php

namespace App\Filament\Pages;

use App\Models\Communication;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class MessageHistory extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|UnitEnum|null $navigationGroup = 'Messaging';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Message History';

    protected string $view = 'filament.pages.message-history';

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
            ->with(['creator', 'recipients.student', 'recipients.sponsor'])
            ->whereIn('communication_type', ['email', 'sms'])
            ->latest()
            ->limit(30)
            ->get()
            ->all();
    }
}
