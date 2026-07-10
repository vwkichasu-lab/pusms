<?php

namespace App\Filament\Resources\StudentResults\Pages;

use App\Filament\Resources\StudentResults\StudentResultResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStudentResult extends EditRecord
{
    protected static string $resource = StudentResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
