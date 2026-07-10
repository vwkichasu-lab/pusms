<?php

namespace App\Filament\Resources\Communications\Tables;

use App\Jobs\SendCommunicationRecipient;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CommunicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')->searchable()->sortable(),
                TextColumn::make('communication_type')->badge()->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('recipients_count')->counts('recipients')->label('Recipients')->sortable(),
                TextColumn::make('sent_at')->dateTime()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('communication_type')->options(['email' => 'Email', 'sms' => 'SMS', 'email_sms' => 'Email and SMS']),
                SelectFilter::make('status')->options([
                    'draft' => 'Draft',
                    'processing' => 'Processing',
                    'completed' => 'Completed',
                    'partially_failed' => 'Partially Failed',
                    'failed' => 'Failed',
                ]),
            ])
            ->recordActions([
                Action::make('retryFailed')
                    ->label('Retry Failed')
                    ->icon('heroicon-m-arrow-path')
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        $failed = $record->recipients()->where('delivery_status', 'failed')->get();

                        foreach ($failed as $recipient) {
                            $recipient->update(['delivery_status' => 'queued', 'failed_at' => null, 'failure_reason' => null]);
                            SendCommunicationRecipient::dispatch($recipient->id);
                        }

                        if ($failed->isNotEmpty()) {
                            $record->update(['status' => 'processing']);
                        }

                        Notification::make()
                            ->title("Queued {$failed->count()} failed recipient(s) for retry")
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
