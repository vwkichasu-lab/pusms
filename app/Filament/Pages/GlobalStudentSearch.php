<?php

namespace App\Filament\Pages;

use App\Support\Filament\RequiresSuperAdministrator;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class GlobalStudentSearch extends Page
{
    use RequiresSuperAdministrator;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static string|UnitEnum|null $navigationGroup = 'Students';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.global-student-search';
}
