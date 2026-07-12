<?php

namespace App\Filament\Widgets;

use App\Models\AcademicYear;
use Filament\Widgets\ChartWidget;

class ScholarshipSpendChart extends ChartWidget
{
    protected ?string $heading = 'Scholarship Spend by Academic Year';

    protected ?string $description = 'Total amount awarded to scholarship students each academic year.';

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 2;

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $years = AcademicYear::query()
            ->withSum('studentScholarships as total_awarded', 'amount_awarded')
            ->orderBy('start_date')
            ->get();

        return [
            'datasets' => [[
                'label' => 'Amount Awarded',
                'data' => $years->map(fn (AcademicYear $year): float => (float) ($year->total_awarded ?? 0))->all(),
                'backgroundColor' => '#082f63',
                'borderColor' => '#082f63',
            ]],
            'labels' => $years->pluck('name')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
