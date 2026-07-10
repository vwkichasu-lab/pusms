<?php

namespace App\Filament\Resources\StudentImports\Pages;

use App\Filament\Resources\StudentImports\StudentImportResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStudentImport extends EditRecord
{
    protected static string $resource = StudentImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
