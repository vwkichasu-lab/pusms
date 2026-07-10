<?php

namespace App\Filament\Resources\StudentScholarships\Pages;

use App\Filament\Resources\StudentScholarships\StudentScholarshipResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStudentScholarship extends EditRecord
{
    protected static string $resource = StudentScholarshipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
