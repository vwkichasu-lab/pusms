<?php

namespace App\Filament\Resources\StudentResults\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StudentResultForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Academic Performance')
                    ->schema([
                        Select::make('student_id')->relationship('student', 'student_id')->searchable()->preload()->required(),
                        Select::make('academic_year_id')->relationship('academicYear', 'name')->searchable()->preload()->required(),
                        TextInput::make('index_number')->label('Index Number')->maxLength(255),
                        TextInput::make('programme_snapshot')->label('Programme')->maxLength(255),
                        TextInput::make('level_snapshot')->label('Current Level')->maxLength(255),
                        TextInput::make('credit_hours')->numeric()->minValue(0),
                        TextInput::make('grade_point')->numeric()->minValue(0)->maxValue(4),
                        TextInput::make('score')->numeric()->minValue(0)->maxValue(100),
                        TextInput::make('gpa')->numeric()->required()->minValue(0)->maxValue(4),
                        TextInput::make('credits_attempted')->numeric()->minValue(0),
                        TextInput::make('credits_passed')->numeric()->minValue(0),
                        Select::make('performance_status')
                            ->required()
                            ->options([
                                'excellent' => 'Excellent',
                                'satisfactory' => 'Satisfactory',
                                'watchlist' => 'Watchlist',
                                'probation' => 'Probation',
                                'at_risk' => 'At Risk',
                            ])
                            ->default('satisfactory'),
                        Select::make('data_source')
                            ->required()
                            ->options([
                                'Manual Entry' => 'Manual Entry',
                                'Academic Department Import' => 'Academic Department Import',
                                'System Migration' => 'System Migration',
                            ])
                            ->default('Manual Entry'),
                        Textarea::make('remarks')->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ]),
            ]);
    }
}
