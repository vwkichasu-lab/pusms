<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DashboardReadiness extends Widget
{
    protected string $view = 'filament.widgets.dashboard-readiness';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 0;
}
