<?php

namespace App\Filament\Resources\StudentImports\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StudentImportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Import Log')
                    ->schema([
                        TextInput::make('original_filename')->disabled(),
                        TextInput::make('status')->disabled(),
                        TextInput::make('total_rows')->disabled(),
                        TextInput::make('successful_rows')->disabled(),
                        TextInput::make('failed_rows')->disabled(),
                        Textarea::make('errors')
                            ->formatStateUsing(fn ($state): string => is_array($state) ? implode(PHP_EOL, $state) : (string) $state)
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }
}
