<?php

namespace App\Support\Filament;

use Illuminate\Support\Facades\Auth;

trait RequiresSuperAdministrator
{
    public static function canAccess(): bool
    {
        return (bool) Auth::user()?->hasRole('Super Administrator');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
