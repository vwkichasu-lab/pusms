<?php

namespace App\Filament\Resources\StudentImports\Pages;

use App\Filament\Resources\StudentImports\StudentImportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStudentImports extends ListRecords
{
    protected static string $resource = StudentImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
