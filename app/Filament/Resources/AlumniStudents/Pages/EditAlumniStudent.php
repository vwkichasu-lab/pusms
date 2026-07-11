<?php

namespace App\Filament\Resources\AlumniStudents\Pages;

use App\Filament\Resources\AlumniStudents\AlumniStudentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAlumniStudent extends EditRecord
{
    protected static string $resource = AlumniStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
