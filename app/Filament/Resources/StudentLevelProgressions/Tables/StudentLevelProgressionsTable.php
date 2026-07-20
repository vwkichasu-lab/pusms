<?php

namespace App\Filament\Resources\StudentLevelProgressions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StudentLevelProgressionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.student_id')->label('Student ID')->searchable()->sortable(),
                TextColumn::make('student.full_name')->label('Student')->searchable(['students.first_name', 'students.middle_name', 'students.last_name']),
                TextColumn::make('previousLevel.name')->label('Previous Level')->sortable(),
                TextColumn::make('newLevel.name')->label('New Level')->sortable(),
                TextColumn::make('academicYear.name')->label('Academic Year')->sortable(),
                TextColumn::make('update_type')->badge()->sortable(),
                TextColumn::make('updater.name')->label('Updated By')->toggleable(),
                TextColumn::make('created_at')->label('Update Date')->dateTime()->sortable(),
                TextColumn::make('notes')->limit(80)->toggleable(),
            ])
            ->filters([
                SelectFilter::make('update_type')->options([
                    'individual' => 'Individual',
                    'bulk' => 'Bulk',
                ]),
            ])
            ->recordActions([
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
