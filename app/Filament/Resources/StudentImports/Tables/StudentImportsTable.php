<?php

namespace App\Filament\Resources\StudentImports\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StudentImportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('original_filename')->searchable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('total_rows')->sortable(),
                TextColumn::make('successful_rows')->sortable(),
                TextColumn::make('failed_rows')->sortable(),
                TextColumn::make('uploader.name')->label('Uploaded By')->toggleable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'processing' => 'Processing',
                    'completed' => 'Completed',
                    'completed_with_errors' => 'Completed with Errors',
                    'failed' => 'Failed',
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
