<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('profile_photo')
                    ->label('Photo')
                    ->disk('public')
                    ->circular()
                    ->toggleable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('username')->searchable()->sortable()->toggleable(),
                TextColumn::make('email')->searchable()->sortable(),
                TextColumn::make('roles.name')->label('Roles')->badge()->separator(', '),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles')->relationship('roles', 'name')->multiple()->preload(),
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        true => 'Active',
                        false => 'Inactive',
                    ]),
            ])
            ->recordActions([
                Action::make('resetPassword')
                    ->label('Reset Password')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->form([
                        TextInput::make('password')
                            ->label('New Password')
                            ->password()
                            ->revealable()
                            ->required()
                            ->minLength(8)
                            ->maxLength(255)
                            ->confirmed(),
                        TextInput::make('password_confirmation')
                            ->label('Confirm Password')
                            ->password()
                            ->revealable()
                            ->required()
                            ->maxLength(255),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record): string => 'Reset password for '.$record->name)
                    ->action(function (User $record, array $data): void {
                        $record->forceFill([
                            'password' => $data['password'],
                        ])->save();

                        Notification::make()
                            ->title('Password reset')
                            ->body('Give the new password to '.$record->name.'. The old password cannot be viewed.')
                            ->success()
                            ->send();
                    }),
                Action::make('loginAs')
                    ->label('Login As')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record): string => 'Login as '.$record->name.'?')
                    ->modalDescription('You will temporarily enter this user account. A return link will appear at the top of the system.')
                    ->visible(fn (User $record): bool => Auth::id() !== $record->id)
                    ->action(function (User $record) {
                        if (! $record->is_active) {
                            Notification::make()
                                ->title('User is inactive')
                                ->body('Activate the user before logging into their account.')
                                ->danger()
                                ->send();

                            return null;
                        }

                        session()->put('impersonated_by_user_id', Auth::id());
                        Auth::login($record);
                        session()->regenerate();

                        return redirect('/admin');
                    }),
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
