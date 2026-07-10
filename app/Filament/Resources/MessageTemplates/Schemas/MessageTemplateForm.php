<?php

namespace App\Filament\Resources\MessageTemplates\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MessageTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Template')
                    ->description('Supported variables: {{student_name}}, {{student_id}}, {{level}}, {{programme}}.')
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('subject')->maxLength(255),
                        Select::make('channel')
                            ->required()
                            ->options(['email' => 'Email', 'sms' => 'SMS', 'email_sms' => 'Email and SMS'])
                            ->default('email'),
                        Select::make('status')
                            ->required()
                            ->options(['active' => 'Active', 'inactive' => 'Inactive'])
                            ->default('active'),
                        Textarea::make('message')->required()->rows(8)->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
