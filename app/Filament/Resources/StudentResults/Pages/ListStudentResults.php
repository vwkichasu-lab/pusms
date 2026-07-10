<?php

namespace App\Filament\Resources\StudentResults\Pages;

use App\Filament\Resources\StudentResults\StudentResultResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStudentResults extends ListRecords
{
    protected static string $resource = StudentResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
