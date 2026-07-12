<?php

namespace App\Filament\Widgets;

use App\Models\StudentScholarship;
use Filament\Widgets\ChartWidget;

class ScholarshipCoverageChart extends ChartWidget
{
    protected ?string $heading = 'Students by Scholarship Percentage';

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 4;

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $rows = StudentScholarship::query()
            ->selectRaw('coverage_percentage, count(*) as aggregate')
            ->groupBy('coverage_percentage')
            ->orderBy('coverage_percentage', 'desc')
            ->get();

        return [
            'datasets' => [[
                'label' => 'Students',
                'data' => $rows->pluck('aggregate')->all(),
                'backgroundColor' => ['#082f63', '#d69e2e', '#0f766e', '#475569', '#2563eb', '#9333ea'],
            ]],
            'labels' => $rows->map(fn ($row): string => number_format((float) $row->coverage_percentage, 0).'%')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
