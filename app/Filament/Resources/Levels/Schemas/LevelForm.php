<?php

namespace App\Filament\Resources\Levels\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LevelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Level Details')
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255)->unique(ignoreRecord: true),
                        TextInput::make('numeric_value')->required()->numeric()->minValue(1)->unique(ignoreRecord: true),
                    ])
                    ->columns(2),
            ]);
    }
}
