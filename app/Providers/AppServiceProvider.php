<?php

namespace App\Providers;

use App\Services\Notifications\Contracts\EmailSender;
use App\Services\Notifications\Contracts\SmsSender;
use App\Services\Notifications\Senders\FakeEmailSender;
use App\Services\Notifications\Senders\FakeSmsSender;
use App\Services\Notifications\Senders\HubtelSmsSender;
use App\Services\Notifications\Senders\LaravelSmtpEmailSender;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(EmailSender::class, function ($app): EmailSender {
            return match (config('notifications.email.provider')) {
                'fake' => $app->make(FakeEmailSender::class),
                default => $app->make(LaravelSmtpEmailSender::class),
            };
        });

        $this->app->bind(SmsSender::class, function ($app): SmsSender {
            return match (config('notifications.sms.provider')) {
                'fake' => $app->make(FakeSmsSender::class),
                default => $app->make(HubtelSmsSender::class),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
