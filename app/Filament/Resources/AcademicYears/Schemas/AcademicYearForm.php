<?php

namespace App\Filament\Resources\AcademicYears\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AcademicYearForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Academic Year Details')
                    ->schema([
                        TextInput::make('name')->required()->maxLength(20)->unique(ignoreRecord: true),
                        DatePicker::make('start_date')->required(),
                        DatePicker::make('end_date')->required()->after('start_date'),
                        Select::make('status')
                            ->required()
                            ->options([
                                'upcoming' => 'Upcoming',
                                'current' => 'Current',
                                'closed' => 'Closed',
                            ])
                            ->default('upcoming'),
                    ])
                    ->columns(2),
            ]);
    }
}
