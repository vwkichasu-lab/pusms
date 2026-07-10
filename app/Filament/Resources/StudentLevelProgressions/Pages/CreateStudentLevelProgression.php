<?php

namespace App\Filament\Resources\StudentLevelProgressions\Pages;

use App\Filament\Resources\StudentLevelProgressions\StudentLevelProgressionResource;
use App\Models\Student;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateStudentLevelProgression extends CreateRecord
{
    protected static string $resource = StudentLevelProgressionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $student = Student::find($data['student_id']);

        $data['previous_level_id'] = $student?->level_id;
        $data['updated_by'] = Auth::id();
        $data['update_type'] = 'individual';

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->student?->update(['level_id' => $this->record->new_level_id]);
    }
}
