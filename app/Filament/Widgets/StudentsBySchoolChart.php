<?php

namespace App\Filament\Widgets;

use App\Models\School;
use Filament\Widgets\ChartWidget;

class StudentsBySchoolChart extends ChartWidget
{
    protected ?string $heading = 'Students by School';

    protected int | string | array $columnSpan = 1;

    protected static ?int $sort = 6;

    protected ?string $pollingInterval = null;

    protected string $color = 'primary';

    protected function getData(): array
    {
        $schools = School::query()
            ->withCount(['departments as students_count' => fn ($query) => $query
                ->join('programmes', 'programmes.department_id', '=', 'departments.id')
                ->join('students', 'students.programme_id', '=', 'programmes.id')])
            ->orderBy('name')
            ->get();

        return [
            'datasets' => [['label' => 'Students', 'data' => $schools->pluck('students_count')->all()]],
            'labels' => $schools->pluck('name')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
