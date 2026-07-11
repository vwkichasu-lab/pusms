<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Sponsor;

class TemplateVariableService
{
    public function render(string $message, ?Student $student = null, ?Sponsor $sponsor = null): string
    {
        $name = $student?->full_name
            ?? $sponsor?->contact_person
            ?? $sponsor?->name
            ?? 'Recipient';

        return strtr($message, [
            '{{name}}' => $name,
            '{{recipient_name}}' => $name,
            '{{student_name}}' => $student?->full_name ?? $name,
            '{{first_name}}' => $student?->first_name ?? str($name)->before(' ')->toString(),
            '{{student_id}}' => $student?->student_id ?? 'PU-2026-001',
            '{{level}}' => $student?->level?->name ?? 'Level 100',
            '{{programme}}' => $student?->programme?->name ?? 'BSc Business Administration',
            '{{academic_year}}' => $student?->scholarships()->latest()->first()?->academicYear?->name ?? '2026/2027',
            '{{scholarship_name}}' => $student?->scholarships()->latest()->first()?->scholarshipProgramme?->name ?? 'Pentecost Excellence Scholarship',
            '{{sponsor_name}}' => $sponsor?->name ?? '',
            '{{contact_person}}' => $sponsor?->contact_person ?? '',
        ]);
    }
}
