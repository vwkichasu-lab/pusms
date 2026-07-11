<?php

use App\Services\Notifications\Contracts\EmailSender;
use App\Services\Notifications\Contracts\SmsSender;
use App\Services\Notifications\Data\EmailMessage;
use App\Services\Notifications\Data\SmsMessage;
use Illuminate\Support\Facades\DB;
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

Artisan::command('pusms:test-sms {to}', function (SmsSender $sms): int {
    if (app()->environment('production')) {
        $this->error('This command is disabled in production.');

        return 1;
    }

    $result = $sms->send(new SmsMessage(
        to: $this->argument('to'),
        message: 'PUSMS development SMS test.',
        idempotencyKey: 'dev-test-sms:'.now()->timestamp,
    ));

    $this->info("SMS test {$result->status} through {$result->provider}.");

    return 0;
})->purpose('Send a development-only test SMS through the configured notification provider');

Artisan::command('pusms:clear-students {--force : Confirm destructive student cleanup}', function (): int {
    if (! $this->option('force')) {
        $this->error('This command deletes all students and related student records. Re-run with --force to confirm.');

        return 1;
    }

    $counts = DB::transaction(function (): array {
        $counts = [
            'communication_recipients' => DB::table('communication_recipients')->whereNotNull('student_id')->count(),
            'student_level_progressions' => DB::table('student_level_progressions')->count(),
            'student_results' => DB::table('student_results')->count(),
            'student_scholarships' => DB::table('student_scholarships')->count(),
            'students' => DB::table('students')->count(),
            'deleted_students' => DB::table('students')->whereNotNull('deleted_at')->count(),
        ];

        DB::table('communication_recipients')->whereNotNull('student_id')->delete();
        DB::table('student_level_progressions')->delete();
        DB::table('student_results')->delete();
        DB::table('student_scholarships')->delete();
        DB::table('students')->delete();

        return $counts;
    });

    foreach ($counts as $table => $count) {
        $this->line("{$table}: {$count}");
    }

    $this->info('All students and related student records were cleared.');

    return 0;
})->purpose('Delete all students and student-related operational records');
