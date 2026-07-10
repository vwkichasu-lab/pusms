<?php

namespace App\Filament\Resources\ScholarshipProgrammes\Pages;

use App\Filament\Resources\ScholarshipProgrammes\ScholarshipProgrammeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditScholarshipProgramme extends EditRecord
{
    protected static string $resource = ScholarshipProgrammeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
