<?php

namespace App\Filament\Resources\StudentResults\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StudentResultsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.student_id')->label('Student ID')->searchable()->sortable(),
                TextColumn::make('student.full_name')->label('Student')->searchable(['students.first_name', 'students.middle_name', 'students.last_name']),
                TextColumn::make('academicYear.name')->label('Academic Year')->sortable(),
                TextColumn::make('score')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('gpa')->sortable(),
                TextColumn::make('data_source')->badge()->toggleable(),
                TextColumn::make('performance_status')->badge()->sortable(),
            ])
            ->filters([
                SelectFilter::make('academicYear')->relationship('academicYear', 'name')->searchable()->preload(),
                SelectFilter::make('performance_status')->options([
                    'excellent' => 'Excellent',
                    'satisfactory' => 'Satisfactory',
                    'watchlist' => 'Watchlist',
                    'probation' => 'Probation',
                    'at_risk' => 'At Risk',
                ]),
            ])
            ->recordActions([
                EditAction::make()->requiresConfirmation(),
                DeleteAction::make()->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()->requiresConfirmation()]),
            ]);
    }
}
