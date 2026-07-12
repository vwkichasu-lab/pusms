<?php

namespace App\Filament\Pages;

use App\Models\SystemSetting;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class GeneralSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 0;

    protected static ?string $title = 'General Settings';

    protected string $view = 'filament.pages.general-settings';

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('Super Administrator') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'system_name' => $this->setting('system_name', 'Pentecost University Scholarship Management System'),
            'system_short_name' => $this->setting('system_short_name', 'PUSMS'),
            'support_email' => $this->setting('support_email', 'pentvarsscholarship@gmail.com'),
            'office_phone' => $this->setting('office_phone', ''),
            'default_academic_year' => $this->setting('default_academic_year', '2025/2026'),
            'allow_gmail_sending' => (bool) $this->setting('allow_gmail_sending', true),
            'allow_sms_sending' => (bool) $this->setting('allow_sms_sending', true),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('System Identity')
                    ->schema([
                        TextInput::make('system_name')
                            ->label('System Name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('system_short_name')
                            ->label('Short Name')
                            ->required()
                            ->maxLength(50),
                        TextInput::make('default_academic_year')
                            ->label('Default Academic Year')
                            ->placeholder('2025/2026')
                            ->maxLength(20),
                    ])
                    ->columns(3),
                Section::make('Contact and Communication')
                    ->schema([
                        TextInput::make('support_email')
                            ->label('Scholarship Email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('office_phone')
                            ->label('Scholarship Office Phone')
                            ->tel()
                            ->maxLength(50),
                        Toggle::make('allow_gmail_sending')
                            ->label('Allow Gmail Sending')
                            ->default(true),
                        Toggle::make('allow_sms_sending')
                            ->label('Allow SMS Sending')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            SystemSetting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => 'general',
                    'type' => is_bool($value) ? 'boolean' : 'string',
                    'updated_by' => Auth::id(),
                ],
            );
        }

        Notification::make()
            ->title('General settings saved')
            ->success()
            ->send();
    }

    private function setting(string $key, mixed $default): mixed
    {
        return SystemSetting::query()->where('key', $key)->first()?->value ?? $default;
    }
}
