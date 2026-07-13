<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Export CSV')
                ->icon('heroicon-m-arrow-down-tray')
                ->url(fn (): string => route('reports.students.export', ['format' => 'csv']))
                ->openUrlInNewTab(),
            Action::make('exportXlsx')
                ->label('Export Excel')
                ->icon('heroicon-m-table-cells')
                ->url(fn (): string => route('reports.students.export', ['format' => 'xlsx']))
                ->openUrlInNewTab(),
            CreateAction::make(),
        ];
    }
}
