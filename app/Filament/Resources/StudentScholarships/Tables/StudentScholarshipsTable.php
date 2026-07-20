<?php

namespace App\Filament\Resources\StudentScholarships\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Models\StudentScholarship;

class StudentScholarshipsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.student_id')->label('Student ID')->searchable()->sortable(),
                TextColumn::make('student.full_name')->label('Student')->searchable(['students.first_name', 'students.middle_name', 'students.last_name']),
                TextColumn::make('award_reference')->label('Award Reference')->searchable()->sortable()->toggleable(),
                TextColumn::make('scholarshipProgramme.name')->label('Scholarship')->searchable()->sortable(),
                TextColumn::make('academicYear.name')->label('Academic Year')->sortable(),
                TextColumn::make('semester.name')->label('Semester')->toggleable(),
                TextColumn::make('coverage_percentage')->suffix('%')->sortable()->toggleable(),
                TextColumn::make('covers_accommodation')->label('Accommodation')->badge()->formatStateUsing(fn ($state): string => $state ? 'Included' : 'Excluded')->toggleable(),
                TextColumn::make('covers_tuition')->label('Tuition')->badge()->formatStateUsing(fn ($state): string => $state ? 'Included' : 'Excluded')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('covers_stipend')->label('Stipend')->badge()->formatStateUsing(fn ($state): string => $state ? 'Included' : 'Excluded')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('amount_awarded')->money('GHS')->sortable()->toggleable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('scholarship_stage')
                    ->label('Stage')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => StudentScholarship::stageOptions()[$state] ?? '-')
                    ->sortable(),
                TextColumn::make('award_date')->date()->sortable()->toggleable(),
                TextColumn::make('start_date')->label('Start')->date()->sortable()->toggleable(),
                TextColumn::make('end_date')->label('End / Terminated')->date()->sortable()->toggleable(),
                TextColumn::make('remarks')->label('Notes')->limit(60)->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('scholarshipProgramme')->relationship('scholarshipProgramme', 'name')->searchable()->preload(),
                SelectFilter::make('academicYear')->relationship('academicYear', 'name')->searchable()->preload(),
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'renewed' => 'Renewed',
                        'suspended' => 'Suspended',
                        'expired' => 'Expired',
                        'terminated' => 'Terminated',
                        'completed' => 'Completed',
                    ]),
                SelectFilter::make('scholarship_stage')
                    ->label('Scholarship Stage')
                    ->options(StudentScholarship::stageOptions()),
            ])
            ->recordActions([
                Action::make('letter')
                    ->label('Letter')
                    ->icon('heroicon-m-document-text')
                    ->url(fn ($record): string => route('student-scholarships.letter', $record))
                    ->openUrlInNewTab()
                    ->visible(fn ($record): bool => $record->scholarshipProgramme?->scholarship_type === 'pu_bursary'),
                EditAction::make()->requiresConfirmation(),
                DeleteAction::make()->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }
}
