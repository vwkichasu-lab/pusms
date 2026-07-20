<?php

namespace App\Filament\Resources\Departments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DepartmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('school.name')->label('School')->searchable()->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('programmes_count')->counts('programmes')->label('Programmes')->sortable(),
            ])
            ->filters([
                SelectFilter::make('school')->relationship('school', 'name')->searchable()->preload(),
                SelectFilter::make('status')->options(['active' => 'Active', 'inactive' => 'Inactive']),
            ])
            ->recordActions([
                Action::make('details')
                    ->label('Details')
                    ->icon('heroicon-m-eye')
                    ->modalHeading(fn ($record): string => $record->name)
                    ->modalContent(fn ($record) => view('filament.modals.department-details', ['department' => $record->load(['school', 'programmes'])])),
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
