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
        if (! $gmail->configured()) {
            Notification::make()
                ->title('Gmail OAuth is not configured')
                ->body('Add GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in the server environment first.')
                ->danger()
                ->send();

            return redirect()->route('filament.admin.pages.send-email');
        }

        $state = $gmail->state();
        $request->session()->put('gmail_oauth_state', $state);

        return redirect()->away($gmail->authorizationUrl($state));
    }

    public function callback(Request $request, GmailOAuthService $gmail): RedirectResponse
    {
        abort_if(! hash_equals((string) $request->session()->pull('gmail_oauth_state'), (string) $request->query('state')), 403);

        if ($request->filled('error')) {
            Notification::make()
                ->title('Gmail connection cancelled')
                ->body((string) $request->query('error'))
                ->warning()
                ->send();

            return redirect()->route('filament.admin.pages.send-email');
        }

        try {
            $account = $gmail->connect(Auth::user(), (string) $request->query('code'));
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('Gmail connection failed')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return redirect()->route('filament.admin.pages.send-email');
        }

        Notification::make()
            ->title('Gmail connected')
            ->body("Emails can now be sent from {$account->email}.")
            ->success()
            ->send();

        return redirect()->route('filament.admin.pages.send-email');
    }

    public function disconnect(GmailAccount $gmailAccount): RedirectResponse
    {
        abort_unless($gmailAccount->user_id === Auth::id(), 403);

        $email = $gmailAccount->email;
        $gmailAccount->delete();

        Notification::make()
            ->title('Gmail disconnected')
            ->body("Removed {$email} from PUSMS.")
            ->success()
            ->send();

        return redirect()->route('filament.admin.pages.send-email');
    }
}
