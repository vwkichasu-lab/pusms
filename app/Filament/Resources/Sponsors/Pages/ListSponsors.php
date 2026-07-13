<?php

namespace App\Filament\Resources\Sponsors\Pages;

use App\Filament\Resources\Sponsors\SponsorResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSponsors extends ListRecords
{
    protected static string $resource = SponsorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Export CSV')
                ->icon('heroicon-m-arrow-down-tray')
                ->url(fn (): string => route('reports.sponsors.export', ['format' => 'csv']))
                ->openUrlInNewTab(),
            Action::make('exportXlsx')
                ->label('Export Excel')
                ->icon('heroicon-m-table-cells')
                ->url(fn (): string => route('reports.sponsors.export', ['format' => 'xlsx']))
                ->openUrlInNewTab(),
            CreateAction::make(),
        ];
    }
}
