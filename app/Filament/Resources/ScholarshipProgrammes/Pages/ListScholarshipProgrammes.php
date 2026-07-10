<?php

namespace App\Filament\Resources\ScholarshipProgrammes\Pages;

use App\Filament\Resources\ScholarshipProgrammes\ScholarshipProgrammeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListScholarshipProgrammes extends ListRecords
{
    protected static string $resource = ScholarshipProgrammeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
