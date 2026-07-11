<?php

namespace App\Http\Controllers;

use App\Models\GmailAccount;
use App\Services\GmailOAuthService;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GmailOAuthController extends Controller
{
    public function redirect(Request $request, GmailOAuthService $gmail): RedirectResponse
    {
        abort_unless(Auth::user()?->can('send email'), 403);

        try {
            $gmail->assertConfigured();
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('Gmail OAuth is not configured')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return redirect()->route('filament.admin.pages.gmail-settings');
        }

        $state = $gmail->state();
        $request->session()->put('gmail_oauth_state', $state);

        return redirect()->away($gmail->getAuthorizationUrl($state));
    }

    public function callback(Request $request, GmailOAuthService $gmail): RedirectResponse
    {
        abort_unless(Auth::user()?->can('send email'), 403);
        abort_if(! hash_equals((string) $request->session()->pull('gmail_oauth_state'), (string) $request->query('state')), 403);

        if ($request->filled('error')) {
            Notification::make()
                ->title('Gmail connection cancelled')
                ->body('Google did not authorize Gmail sending access.')
                ->warning()
                ->send();

            return redirect()->route('filament.admin.pages.gmail-settings');
        }

        try {
            if (! $request->filled('code')) {
                throw new \RuntimeException('Google did not return an authorization code. Try connecting Gmail again.');
            }

            $account = $gmail->handleCallback(Auth::user(), (string) $request->query('code'));
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('Gmail connection failed')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return redirect()->route('filament.admin.pages.gmail-settings');
        }

        Notification::make()
            ->title('Gmail connected')
            ->body("Emails can now be sent from {$account->email}.")
            ->success()
            ->send();

        return redirect()->route('filament.admin.pages.gmail-settings');
    }

    public function disconnect(GmailAccount $gmailAccount, GmailOAuthService $gmail): RedirectResponse
    {
        abort_unless(Auth::user()?->can('send email'), 403);
        abort_unless($gmailAccount->user_id === Auth::id(), 403);

        $email = $gmailAccount->email;
        $gmail->disconnect($gmailAccount);

        Notification::make()
            ->title('Gmail disconnected')
            ->body("Removed {$email} from PUSMS.")
            ->success()
            ->send();

        return redirect()->route('filament.admin.pages.gmail-settings');
    }
}
