<?php

namespace App\Filament\Resources\StudentLevelProgressions\Schemas;

use App\Models\Level;
use App\Models\Student;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class StudentLevelProgressionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Level Migration')
                    ->description('Select a student and the new level. Saving this form updates the student level and records the migration history.')
                    ->schema([
                        Select::make('student_id')
                            ->label('Student')
                            ->options(fn (): array => Student::query()
                                ->orderBy('student_id')
                                ->get()
                                ->mapWithKeys(fn (Student $student): array => [
                                    $student->id => "{$student->student_id} - {$student->full_name} ({$student->level?->name})",
                                ])
                                ->all())
                            ->searchable()
                            ->live()
                            ->required(),
                        Placeholder::make('previous_level')
                            ->label('Current Level')
                            ->content(fn (Get $get): string => Student::with('level')->find($get('student_id'))?->level?->name ?? 'Select a student first.'),
                        Select::make('new_level_id')
                            ->label('New Level')
                            ->options(fn (): array => Level::query()->orderBy('numeric_value')->pluck('name', 'id')->all())
                            ->searchable()
                            ->required(),
                        Select::make('academic_year_id')
                            ->label('Academic Year')
                            ->relationship('academicYear', 'name')
                            ->searchable()
                            ->preload(),
                        Textarea::make('notes')->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
