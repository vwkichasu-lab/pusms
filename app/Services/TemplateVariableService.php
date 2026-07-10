<?php

namespace App\Services;

use App\Models\Student;

class TemplateVariableService
{
    public function render(string $message, ?Student $student = null): string
    {
        return strtr($message, [
            '{{student_name}}' => $student?->full_name ?? 'Ama Mensah',
            '{{student_id}}' => $student?->student_id ?? 'PU-2026-001',
            '{{level}}' => $student?->level?->name ?? 'Level 100',
            '{{programme}}' => $student?->programme?->name ?? 'BSc Business Administration',
            '{{academic_year}}' => $student?->scholarships()->latest()->first()?->academicYear?->name ?? '2026/2027',
            '{{scholarship_name}}' => $student?->scholarships()->latest()->first()?->scholarshipProgramme?->name ?? 'Pentecost Excellence Scholarship',
        ]);
    }
}
