<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use Filament\Widgets\ChartWidget;

class StudentMovementChart extends ChartWidget
{
    protected ?string $heading = 'Student Growth and Drops';

    protected ?string $description = 'Admissions by year compared with students marked dropped, deferred, withdrawn, or terminated.';

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 5;

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $years = Student::query()
            ->whereNotNull('admission_year')
            ->selectRaw('admission_year')
            ->distinct()
            ->orderBy('admission_year')
            ->pluck('admission_year');

        $dropStatuses = ['dropped', 'deferred', 'withdrawn', 'terminated', 'inactive'];

        return [
            'datasets' => [
                [
                    'label' => 'Students Added',
                    'data' => $years->map(fn (int $year): int => Student::query()->where('admission_year', $year)->count())->all(),
                    'borderColor' => '#082f63',
                    'backgroundColor' => 'rgba(8, 47, 99, .12)',
                ],
                [
                    'label' => 'Dropped / Inactive',
                    'data' => $years->map(fn (int $year): int => Student::query()
                        ->where('admission_year', $year)
                        ->whereIn('student_status', $dropStatuses)
                        ->count())->all(),
                    'borderColor' => '#dc2626',
                    'backgroundColor' => 'rgba(220, 38, 38, .12)',
                ],
            ],
            'labels' => $years->map(fn (int $year): string => (string) $year)->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
