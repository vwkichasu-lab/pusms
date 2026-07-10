<?php

use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScholarshipHistoryController;
use App\Http\Controllers\ScholarshipLetterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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
});
