<?php

namespace App\Filament\Resources\Semesters\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SemesterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Semester Details')
                    ->schema([
                        Select::make('academic_year_id')
                            ->relationship('academicYear', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')->required()->maxLength(255),
                        Select::make('status')
                            ->required()
                            ->options(['upcoming' => 'Upcoming', 'current' => 'Current', 'closed' => 'Closed'])
                            ->default('upcoming'),
                    ])
                    ->columns(3),
            ]);
    }
}
