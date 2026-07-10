<?php

namespace App\Filament\Widgets;

use App\Models\Programme;
use App\Models\Student;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;

class PusmsStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Scholarship Dashboard';

    protected ?string $description = 'Live operational counts from students, scholarships, academic setup, and communications.';

    protected ?string $pollingInterval = null;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $hasStudents = Schema::hasTable('students');
        $hasProgrammes = Schema::hasTable('programmes');

        return [
            Stat::make('Total Students', $hasStudents ? Student::query()->count() : 0)
                ->description('Central student records')
                ->descriptionIcon('heroicon-m-academic-cap', IconPosition::Before)
                ->color('primary'),
            Stat::make('Active Students', $hasStudents ? Student::query()->where('student_status', 'active')->count() : 0)
                ->description('Students marked active')
                ->descriptionIcon('heroicon-m-check-badge', IconPosition::Before)
                ->color('success'),
            Stat::make('Academic Programmes', $hasProgrammes ? Programme::query()->count() : 0)
                ->description('Academic programmes configured')
                ->descriptionIcon('heroicon-m-rectangle-stack', IconPosition::Before)
                ->color('warning'),
            Stat::make('Pending Renewals', 0)
                ->description('Scholarship records due for review')
                ->descriptionIcon('heroicon-m-clock', IconPosition::Before)
                ->color('warning'),
            Stat::make('Graduating Scholars', 0)
                ->description('Final-year scholarship beneficiaries')
                ->descriptionIcon('heroicon-m-flag', IconPosition::Before)
                ->color('primary'),
            Stat::make('Messages Sent', 0)
                ->description('Email and SMS delivery metrics')
                ->descriptionIcon('heroicon-m-paper-airplane', IconPosition::Before)
                ->color('primary'),
        ];
    }
}
