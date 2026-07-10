<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use Filament\Widgets\ChartWidget;

class StudentsByRegionChart extends ChartWidget
{
    protected ?string $heading = 'Students by Region';

    protected int | string | array $columnSpan = 1;

    protected static ?int $sort = 7;

    protected ?string $pollingInterval = null;

    protected string $color = 'primary';

    protected function getData(): array
    {
        $regions = Student::query()
            ->selectRaw("coalesce(region, 'Not set') as region_name, count(*) as aggregate")
            ->groupBy('region_name')
            ->orderBy('region_name')
            ->get();

        return [
            'datasets' => [['label' => 'Students', 'data' => $regions->pluck('aggregate')->all()]],
            'labels' => $regions->pluck('region_name')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
