<?php

namespace App\Filament\Resources\AlumniStudents\Pages;

use App\Filament\Resources\AlumniStudents\AlumniStudentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAlumniStudents extends ListRecords
{
    protected static string $resource = AlumniStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('New Alumni'),
        ];
    }
}
