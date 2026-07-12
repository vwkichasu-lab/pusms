<?php

use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\GmailOAuthController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScholarshipHistoryController;
use App\Http\Controllers\ScholarshipLetterController;
use App\Services\StudentCleanupService;
use App\Models\GmailAccount;
use App\Services\GmailOAuthService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/admin');
    }

    return view('auth.loading');
});

Route::redirect('/login', '/admin/login')->name('login');

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::get('/admin/reports/students', [ReportController::class, 'students'])->name('reports.students');
    Route::get('/admin/reports/students/export/{format}', [ReportController::class, 'exportStudents'])
        ->whereIn('format', ['csv', 'xlsx', 'pdf'])
        ->name('reports.students.export');
    Route::get('/admin/global-student-search/results', GlobalSearchController::class)->name('global.student-search');
    Route::get('/admin/students/{student}/scholarship-history', [ScholarshipHistoryController::class, 'show'])
        ->name('students.scholarship-history');
    Route::match(['get', 'post'], '/admin/student-scholarships/{award}/letter', [ScholarshipLetterController::class, 'show'])
        ->name('student-scholarships.letter');
    Route::get('/admin/gmail/connect', [GmailOAuthController::class, 'redirect'])->name('gmail.connect');
    Route::get('/admin/gmail/callback', [GmailOAuthController::class, 'callback'])->name('gmail.callback');
    Route::delete('/admin/gmail/{gmailAccount}', [GmailOAuthController::class, 'disconnect'])->name('gmail.disconnect');
    Route::get('/admin/impersonation/stop', function () {
        $superAdminId = session()->pull('impersonated_by_user_id');

        abort_if(! $superAdminId, 404);

        $superAdmin = User::query()->findOrFail($superAdminId);

        abort_if(! $superAdmin->hasRole('Super Administrator'), 403);

        Auth::login($superAdmin);
        session()->regenerate();

        return redirect('/admin/users');
    })->name('admin.impersonation.stop');
});

Route::post('/maintenance/clear-students', function (Request $request, StudentCleanupService $cleanup) {
    $token = config('notifications.maintenance_token');

    abort_if(blank($token) || ! hash_equals($token, (string) $request->bearerToken()), 404);

    return response()->json([
        'cleared' => true,
        'counts' => $cleanup->clearAllStudents(),
    ]);
});

Route::post('/maintenance/send-test-email', function (Request $request) {
    $token = config('notifications.maintenance_token');

    abort_if(blank($token) || ! hash_equals($token, (string) $request->bearerToken()), 404);

    $validated = $request->validate([
        'to' => ['required', 'email'],
        'subject' => ['nullable', 'string', 'max:255'],
        'message' => ['nullable', 'string', 'max:5000'],
    ]);

    try {
        Mail::raw(
            $validated['message'] ?? 'This is a PUSMS test email sent through the scholarship Gmail SMTP account.',
            function ($mail) use ($validated): void {
                $mail->to($validated['to'])
                    ->subject($validated['subject'] ?? 'PUSMS Test Email');
            },
        );
    } catch (Throwable $exception) {
        return response()->json([
            'sent' => false,
            'error' => $exception->getMessage(),
        ], 422);
    }

    return response()->json([
        'sent' => true,
        'to' => $validated['to'],
    ]);
});

Route::post('/maintenance/send-gmail-api-test', function (Request $request, GmailOAuthService $gmail) {
    $token = config('notifications.maintenance_token');

    abort_if(blank($token) || ! hash_equals($token, (string) $request->bearerToken()), 404);

    $validated = $request->validate([
        'to' => ['required', 'email'],
        'subject' => ['nullable', 'string', 'max:255'],
        'message' => ['nullable', 'string', 'max:5000'],
    ]);

    $account = GmailAccount::query()
        ->where('status', 'connected')
        ->latest('last_used_at')
        ->latest()
        ->first();

    if (! $account) {
        return response()->json([
            'sent' => false,
            'error' => 'No connected Gmail API account was found. Connect Gmail again in Gmail Settings.',
        ], 422);
    }

    try {
        $gmail->sendTestEmail($account, $validated['to']);
    } catch (Throwable $exception) {
        return response()->json([
            'sent' => false,
            'gmail_account' => $account->email,
            'error' => $exception->getMessage(),
        ], 422);
    }

    return response()->json([
        'sent' => true,
        'gmail_account' => $account->email,
        'to' => $validated['to'],
    ]);
});

Route::post('/maintenance/migrate', function (Request $request) {
    $token = config('notifications.maintenance_token');

    abort_if(blank($token) || ! hash_equals($token, (string) $request->bearerToken()), 404);

    try {
        Artisan::call('migrate', ['--force' => true]);
    } catch (Throwable $exception) {
        return response()->json([
            'migrated' => false,
            'error' => $exception->getMessage(),
        ], 422);
    }

    return response()->json([
        'migrated' => true,
        'output' => Artisan::output(),
    ]);
});

Route::post('/maintenance/last-error', function (Request $request) {
    $token = config('notifications.maintenance_token');

    abort_if(blank($token) || ! hash_equals($token, (string) $request->bearerToken()), 404);

    $path = storage_path('logs/laravel.log');

    if (! File::exists($path)) {
        return response()->json(['error' => 'No Laravel log file found.']);
    }

    return response()->json([
        'tail' => substr(File::get($path), -60000),
    ]);
});
