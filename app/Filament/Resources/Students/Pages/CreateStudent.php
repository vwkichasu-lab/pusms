<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use App\Models\StudentScholarship;
use Filament\Resources\Pages\CreateRecord;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected array $scholarshipAward = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->scholarshipAward = $data['scholarship_award'] ?? [];
        unset($data['scholarship_award']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if (blank($this->scholarshipAward['scholarship_programme_id'] ?? null) || blank($this->scholarshipAward['academic_year_id'] ?? null)) {
            return;
        }

        StudentScholarship::create([
            'student_id' => $this->record->id,
            'scholarship_programme_id' => $this->scholarshipAward['scholarship_programme_id'],
            'academic_year_id' => $this->scholarshipAward['academic_year_id'],
            'award_date' => $this->scholarshipAward['award_date'] ?? now(),
            'coverage_percentage' => $this->scholarshipAward['coverage_percentage'] ?? null,
            'covers_tuition' => (bool) ($this->scholarshipAward['covers_tuition'] ?? true),
            'covers_accommodation' => (bool) ($this->scholarshipAward['covers_accommodation'] ?? false),
            'covers_stipend' => false,
            'status' => 'active',
            'remarks' => $this->scholarshipAward['remarks'] ?? null,
        ]);
    }
}
