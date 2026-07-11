<?php

namespace App\Filament\Pages;

use BackedEnum;
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

    public static function canAccess(): bool
    {
        return Auth::user()?->can('send email') ?? false;
    }
}
