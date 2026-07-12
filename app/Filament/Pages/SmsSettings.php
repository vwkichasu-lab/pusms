<?php

namespace App\Filament\Pages;

use App\Services\Notifications\Contracts\SmsSender;
use App\Services\Notifications\Data\SmsMessage;
use App\Services\Notifications\Support\PhoneNumber;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class SmsSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Messaging';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'SMS Settings';

    protected string $view = 'filament.pages.sms-settings';

    /**
     * @var array<string, mixed>
     */
    public array $data = [
        'to' => '0553865534',
        'message' => 'PUSMS SMS delivery test.',
    ];

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('Super Administrator') ?? false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Send Test SMS')
                    ->description('Use this to confirm that the configured SMS provider can deliver real SMS messages.')
                    ->schema([
                        TextInput::make('to')
                            ->label('Test Phone Number')
                            ->tel()
                            ->required()
                            ->helperText('Local Ghana numbers like 0553865534 are converted with country code +233.'),
                        Textarea::make('message')
                            ->label('Test Message')
                            ->required()
                            ->rows(3)
                            ->maxLength((int) config('notifications.sms.max_length', 918)),
                    ]),
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function getProviderStatusProperty(): array
    {
        $provider = strtolower((string) config('notifications.sms.provider', 'arkesel'));

        $checks = match ($provider) {
            'hubtel' => [
                'HUBTEL_CLIENT_ID' => filled(config('services.hubtel.client_id')),
                'HUBTEL_CLIENT_SECRET' => filled(config('services.hubtel.client_secret')),
                'HUBTEL_SENDER_ID' => filled(config('services.hubtel.sender_id')),
                'HUBTEL_BASE_URL' => filled(config('services.hubtel.base_url')),
            ],
            'fake' => [
                'Fake provider selected' => true,
            ],
            default => [
                'ARKESEL_SMS_API_KEY' => filled(config('services.arkesel.api_key')),
                'ARKESEL_SMS_SENDER_ID' => filled(config('services.arkesel.sender_id')),
                'ARKESEL_SMS_BASE_URL' => filled(config('services.arkesel.base_url')),
            ],
        };

        $checks['PUSMS_SMS_DEFAULT_COUNTRY_CODE'] = filled(config('notifications.sms.default_country_code'));

        return [
            'provider' => $provider,
            'country_code' => config('notifications.sms.default_country_code') ?: 'Not set',
            'ready' => ! in_array(false, $checks, true),
            'checks' => $checks,
        ];
    }

    public function sendTest(SmsSender $sms): void
    {
        $state = $this->form->getState();

        try {
            $normalized = PhoneNumber::normalize($state['to'], config('notifications.sms.default_country_code'));

            $result = $sms->send(new SmsMessage(
                to: $state['to'],
                message: $state['message'],
                idempotencyKey: 'sms-settings-test:'.Auth::id().':'.now()->timestamp,
            ));

            Notification::make()
                ->title('Test SMS sent')
                ->body("Provider: {$result->provider}. Sent to {$normalized}.")
                ->success()
                ->send();
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('Test SMS failed')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }
}
