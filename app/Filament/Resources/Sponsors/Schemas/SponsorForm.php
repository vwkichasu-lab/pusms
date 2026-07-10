<?php

namespace App\Filament\Resources\Sponsors\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SponsorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sponsor Details')
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('sponsor_type')
                            ->label('Sponsor Type')
                            ->placeholder('Church / Organization, Alumni / Association, Individual, Corporate')
                            ->maxLength(255),
                        TextInput::make('contact_person')->maxLength(255),
                        TextInput::make('email')->email()->maxLength(255),
                        TextInput::make('phone')->tel()->maxLength(30),
                        Select::make('status')
                            ->required()
                            ->options(['active' => 'Active', 'inactive' => 'Inactive'])
                            ->default('active'),
                        Textarea::make('address')->columnSpanFull(),
                        Textarea::make('notes')
                            ->placeholder('Example: Supports selected Pentecost University students under bursary and need-based awards.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
