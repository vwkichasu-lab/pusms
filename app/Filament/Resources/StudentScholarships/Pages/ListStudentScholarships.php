<?php

namespace App\Filament\Resources\StudentScholarships\Pages;

use App\Filament\Resources\StudentScholarships\StudentScholarshipResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStudentScholarships extends ListRecords
{
    protected static string $resource = StudentScholarshipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
