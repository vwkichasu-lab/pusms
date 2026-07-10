<?php

namespace App\Filament\Pages;

use Filament\Auth\Pages\EditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MyProfile extends EditProfile
{
    protected static ?string $title = 'My Profile';

    protected function getRedirectUrl(): ?string
    {
        return static::getUrl();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('profile_photo')
                    ->label('Profile Picture')
                    ->image()
                    ->disk('public')
                    ->directory('user-profile-photos')
                    ->visibility('public')
                    ->avatar()
                    ->imageEditor()
                    ->maxSize(2048),
                $this->getNameFormComponent()->label('User Name / Full Name'),
                TextInput::make('username')
                    ->label('Username')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getCurrentPasswordFormComponent(),
            ]);
    }
}
