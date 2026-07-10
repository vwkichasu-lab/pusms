<?php

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Department Details')
                    ->schema([
                        Select::make('school_id')
                            ->relationship('school', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('code')->required()->maxLength(50)->unique(ignoreRecord: true),
                        Select::make('status')
                            ->required()
                            ->options(['active' => 'Active', 'inactive' => 'Inactive'])
                            ->default('active'),
                    ])
                    ->columns(2),
            ]);
    }
}
