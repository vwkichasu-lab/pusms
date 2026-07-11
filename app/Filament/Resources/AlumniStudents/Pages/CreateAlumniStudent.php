<?php

namespace App\Filament\Resources\AlumniStudents\Pages;

use App\Filament\Resources\AlumniStudents\AlumniStudentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAlumniStudent extends CreateRecord
{
    protected static string $resource = AlumniStudentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['alumni_status'] = 'alumni';

        return $data;
    }
}
