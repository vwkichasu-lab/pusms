<?php

namespace App\Filament\Resources\ResultImports\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ResultImportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('original_filename')->searchable()->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('total_rows')->sortable(),
                TextColumn::make('valid_rows')->sortable(),
                TextColumn::make('invalid_rows')->sortable(),
                TextColumn::make('uploader.name')->label('Uploaded By')->toggleable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'uploaded' => 'Uploaded',
                    'invalid' => 'Invalid',
                    'preview_ready' => 'Preview Ready',
                    'preview_with_errors' => 'Preview With Errors',
                    'completed' => 'Completed',
                ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
