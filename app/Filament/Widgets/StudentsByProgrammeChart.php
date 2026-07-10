<?php

namespace App\Filament\Widgets;

use App\Models\ScholarshipProgramme;
use Filament\Widgets\ChartWidget;

class StudentsByProgrammeChart extends ChartWidget
{
    protected ?string $heading = 'Students by Type Of Scholarship';

    protected int | string | array $columnSpan = 1;

    protected static ?int $sort = 4;

    protected ?string $pollingInterval = null;

    protected string $color = 'primary';

    protected function getData(): array
    {
        $programmes = ScholarshipProgramme::query()->withCount('studentScholarships')->orderBy('name')->get();

        return [
            'datasets' => [['label' => 'Students', 'data' => $programmes->pluck('student_scholarships_count')->all()]],
            'labels' => $programmes->pluck('name')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
