<?php

namespace App\Filament\Resources\StudentLevelProgressions\Pages;

use App\Filament\Resources\StudentLevelProgressions\StudentLevelProgressionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStudentLevelProgression extends EditRecord
{
    protected static string $resource = StudentLevelProgressionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
