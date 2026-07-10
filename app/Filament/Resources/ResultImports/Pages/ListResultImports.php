<?php

namespace App\Filament\Resources\ResultImports\Pages;

use App\Filament\Resources\ResultImports\ResultImportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListResultImports extends ListRecords
{
    protected static string $resource = ResultImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
