<?php

namespace App\Filament\Widgets;

use App\Models\AcademicYear;
use Filament\Widgets\ChartWidget;

class ScholarshipGrowthChart extends ChartWidget
{
    protected ?string $heading = 'Scholarship Beneficiary Growth';

    protected ?string $description = 'Counts scholarship assignment records grouped by academic year.';

    protected int | string | array $columnSpan = 1;

    protected ?string $pollingInterval = null;

    protected static ?int $sort = 3;

    protected string $color = 'primary';

    protected function getData(): array
    {
        $years = AcademicYear::query()
            ->withCount('studentScholarships')
            ->orderBy('start_date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Beneficiaries',
                    'data' => $years->pluck('student_scholarships_count')->all(),
                    'borderColor' => '#082f63',
                    'backgroundColor' => 'rgba(8, 47, 99, 0.08)',
                ],
            ],
            'labels' => $years->pluck('name')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
