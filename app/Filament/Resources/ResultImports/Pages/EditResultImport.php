<?php

namespace App\Filament\Resources\ResultImports\Pages;

use App\Filament\Resources\ResultImports\ResultImportResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditResultImport extends EditRecord
{
    protected static string $resource = ResultImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
