<?php

namespace App\Filament\Widgets;

use App\Models\Semester;
use Filament\Widgets\ChartWidget;

class ScholarshipSemesterSpendChart extends ChartWidget
{
    protected ?string $heading = 'Scholarship Spend by Semester';

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 3;

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $semesters = Semester::query()
            ->with('academicYear')
            ->withSum('studentScholarships as total_awarded', 'amount_awarded')
            ->get()
            ->sortBy(fn (Semester $semester): string => ($semester->academicYear?->name ?? '').' '.$semester->name)
            ->values();

        return [
            'datasets' => [[
                'label' => 'Amount Awarded',
                'data' => $semesters->map(fn (Semester $semester): float => (float) ($semester->total_awarded ?? 0))->all(),
                'backgroundColor' => '#d69e2e',
            ]],
            'labels' => $semesters->map(fn (Semester $semester): string => trim(($semester->academicYear?->name ?? '').' '.$semester->name))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
