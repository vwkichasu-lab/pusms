<?php

use App\Services\Notifications\Contracts\EmailSender;
use App\Services\Notifications\Contracts\SmsSender;
use App\Services\Notifications\Data\EmailMessage;
use App\Services\Notifications\Data\SmsMessage;
use App\Services\StudentCleanupService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('pusms:test-email {to} {--subject=Pentecost University test email}', function (EmailSender $email): int {
    if (app()->environment('production')) {
        $this->error('This command is disabled in production.');

        return 1;
    }

    $result = $email->send(new EmailMessage(
        to: $this->argument('to'),
        subject: $this->option('subject'),
        text: 'This is a safe PUSMS development email test.',
        html: '<p>This is a safe <strong>PUSMS</strong> development email test.</p>',
        idempotencyKey: 'dev-test-email:'.now()->timestamp,
    ));

    $this->info("Email test {$result->status} through {$result->provider}.");

    return 0;
})->purpose('Send a development-only test email through the configured notification provider');

Artisan::command('pusms:test-sms {to} {--message=PUSMS SMS delivery test.} {--force-production : Allow a real production SMS test}', function (SmsSender $sms): int {
    if (app()->environment('production') && ! $this->option('force-production')) {
        $this->error('This command is disabled in production unless --force-production is provided.');

        return 1;
    }

    try {
        $result = $sms->send(new SmsMessage(
            to: $this->argument('to'),
            message: $this->option('message'),
            idempotencyKey: 'dev-test-sms:'.now()->timestamp,
        ));
    } catch (Throwable $exception) {
        $this->error('SMS test failed: '.$exception->getMessage());

        return 1;
    }

    $this->info("SMS test {$result->status} through {$result->provider}.");

    return 0;
})->purpose('Send a development-only test SMS through the configured notification provider');

Artisan::command('pusms:clear-students {--force : Confirm destructive student cleanup}', function (StudentCleanupService $cleanup): int {
    if (! $this->option('force')) {
        $this->error('This command deletes all students and related student records. Re-run with --force to confirm.');

        return 1;
    }

    $counts = $cleanup->clearAllStudents();

    foreach ($counts as $table => $count) {
        $this->line("{$table}: {$count}");
    }

    $this->info('All students and related student records were cleared.');

    return 0;
})->purpose('Delete all students and student-related operational records');
