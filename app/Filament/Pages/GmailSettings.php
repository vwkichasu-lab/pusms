<?php

namespace App\Filament\Pages;

use App\Models\GmailAccount;
use App\Services\GmailOAuthService;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class GmailSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Messaging';

    protected static ?int $navigationSort = 0;

    protected static ?string $title = 'Gmail Settings';

    protected string $view = 'filament.pages.gmail-settings';

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public static function canAccess(): bool
    {
        return Auth::user()?->can('send email') ?? false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Send Test Email')
                    ->description('Send a short test message from one connected Gmail account.')
                    ->schema([
                        Select::make('gmail_account_id')
                            ->label('Gmail Account')
                            ->options(fn (): array => GmailAccount::query()
                                ->where('user_id', Auth::id())
                                ->where('status', 'connected')
                                ->orderBy('email')
                                ->pluck('email', 'id')
                                ->all())
                            ->required()
                            ->searchable(),
                        TextInput::make('test_email')
                            ->label('Recipient Email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('test_name')
                            ->label('Recipient Name')
                            ->maxLength(255),
                    ])
                    ->columns(3),
            ]);
    }

    public function sendTestEmail(GmailOAuthService $gmail): void
    {
        $state = $this->form->getState();

        $account = GmailAccount::query()
            ->where('user_id', Auth::id())
            ->where('status', 'connected')
            ->find($state['gmail_account_id'] ?? null);

        if (! $account) {
            Notification::make()
                ->title('Connect Gmail first')
                ->danger()
                ->send();

            return;
        }

        try {
            $gmail->sendTestEmail($account, $state['test_email'], $state['test_name'] ?? null);
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('Test email failed')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Test email sent')
            ->body("Sent from {$account->email} to {$state['test_email']}.")
            ->success()
            ->send();
    }

    public function disconnect(int $gmailAccountId, GmailOAuthService $gmail): void
    {
        $account = GmailAccount::query()
            ->where('user_id', Auth::id())
            ->find($gmailAccountId);

        if (! $account) {
            return;
        }

        $email = $account->email;
        $gmail->disconnect($account);

        Notification::make()
            ->title('Gmail disconnected')
            ->body("Removed active sending access for {$email}.")
            ->success()
            ->send();
    }

    /**
     * @return array<int, GmailAccount>
     */
    public function getGmailAccountsProperty(): array
    {
        return GmailAccount::query()
            ->where('user_id', Auth::id())
            ->latest('connected_at')
            ->latest()
            ->get()
            ->all();
    }
}
