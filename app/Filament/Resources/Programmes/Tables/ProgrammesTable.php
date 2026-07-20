<?php

namespace App\Filament\Resources\Programmes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProgrammesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('code')->searchable()->sortable(),
                TextColumn::make('department.name')->label('Department')->searchable()->sortable(),
                TextColumn::make('department.school.name')->label('Faculty')->searchable()->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('students_count')->counts('students')->label('Students')->sortable(),
            ])
            ->filters([
                SelectFilter::make('department')->relationship('department', 'name')->searchable()->preload(),
                SelectFilter::make('status')->options(['active' => 'Active', 'inactive' => 'Inactive']),
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
