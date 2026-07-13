<?php

namespace App\Filament\Resources\StudentScholarships\Schemas;

use App\Models\Student;
use App\Models\ScholarshipProgramme;
use App\Models\StudentScholarship;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class StudentScholarshipForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Scholarship Assignment')
                    ->schema([
                        Select::make('student_id')
                            ->label('Student ID')
                            ->options(fn (): array => Student::query()
                                ->orderBy('student_id')
                                ->pluck('student_id', 'id')
                                ->all())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required(),
                        Placeholder::make('student_name')
                            ->label('Student Name')
                            ->content(fn (Get $get): string => Student::find($get('student_id'))?->full_name ?: 'Select a student ID first.'),
                        Select::make('scholarship_programme_id')
                            ->label('Type Of Scholarship')
                            ->relationship('scholarshipProgramme', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (?int $state, Set $set): void {
                                $programme = $state ? ScholarshipProgramme::find($state) : null;

                                if (! $programme) {
                                    return;
                                }

                                if ($programme->academic_year_id) {
                                    $set('academic_year_id', $programme->academic_year_id);
                                }

                                if ($programme->default_coverage_percentage !== null) {
                                    $set('coverage_percentage', $programme->default_coverage_percentage);
                                }

                                $set('covers_accommodation', $programme->default_covers_accommodation ? 1 : 0);
                                $set('covers_tuition', 1);
                                $percentage = $programme->default_coverage_percentage !== null
                                    ? number_format((float) $programme->default_coverage_percentage, 0).'%'
                                    : 'Scholarship';

                                $set('coverage_notes', "{$percentage} tuition ".($programme->default_covers_accommodation ? 'including' : 'excluding').' accommodation.');
                            })
                            ->required(),
                        Select::make('academic_year_id')
                            ->relationship('academicYear', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('semester_id')
                            ->relationship('semester', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('status')
                            ->label('Award Status')
                            ->required()
                            ->options([
                                'pending' => 'Pending',
                                'active' => 'Active',
                                'renewed' => 'Renewed',
                                'suspended' => 'Suspended',
                                'expired' => 'Expired',
                                'terminated' => 'Terminated',
                                'completed' => 'Completed',
                            ])
                            ->default('pending'),
                        Select::make('scholarship_stage')
                            ->label('Scholarship Stage')
                            ->required()
                            ->options(StudentScholarship::stageOptions())
                            ->default(StudentScholarship::STAGE_NEW_AWARD)
                            ->helperText('Use Newly Awarded for students just granted scholarship, or Existing Beneficiary for students already on it.'),
                        TextInput::make('award_reference')
                            ->label('Award Reference')
                            ->placeholder('Auto-generated after saving')
                            ->disabled()
                            ->dehydrated(false)
                            ->maxLength(255),
                    ])
                    ->columns(3),
                Section::make('Award Details')
                    ->schema([
                        DatePicker::make('award_date'),
                        DatePicker::make('start_date'),
                        DatePicker::make('end_date')->after('start_date'),
                        TextInput::make('coverage_percentage')->numeric()->minValue(0)->maxValue(100)->suffix('%'),
                        Select::make('covers_tuition')
                            ->label('Covers Tuition')
                            ->required()
                            ->options([1 => 'Yes', 0 => 'No'])
                            ->default(1),
                        Select::make('covers_accommodation')
                            ->label('Covers Accommodation')
                            ->required()
                            ->options([1 => 'Yes', 0 => 'No'])
                            ->default(0),
                        Select::make('covers_stipend')
                            ->label('Covers Stipend')
                            ->required()
                            ->options([1 => 'Yes', 0 => 'No'])
                            ->default(0),
                        TextInput::make('amount_awarded')->numeric()->prefix('GHS'),
                        Select::make('approved_by')
                            ->relationship('approver', 'name')
                            ->searchable()
                            ->preload(),
                        Textarea::make('coverage_notes')
                            ->placeholder('Example: 100% tuition excluding accommodation, or 25% tuition only.')
                            ->columnSpanFull(),
                        Textarea::make('remarks')->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }
}
