<?php

namespace App\Filament\Widgets;

use App\Models\Level;
use Filament\Widgets\ChartWidget;

class StudentsByLevelChart extends ChartWidget
{
    protected ?string $heading = 'Students by Level';

    protected int | string | array $columnSpan = 1;

    protected static ?int $sort = 5;

    protected ?string $pollingInterval = null;

    protected string $color = 'primary';

    protected function getData(): array
    {
        $levels = Level::query()->withCount('students')->orderBy('numeric_value')->get();

        return [
            'datasets' => [['label' => 'Students', 'data' => $levels->pluck('students_count')->all()]],
            'labels' => $levels->pluck('name')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
