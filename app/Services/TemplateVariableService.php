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

        $values = [
            '{{name}}' => $name,
            '{{recipient_name}}' => $name,
            '{{student_name}}' => $student?->full_name ?? $name,
            '{{first_name}}' => $student?->first_name ?? str($name)->before(' ')->toString(),
            '{{last_name}}' => $student?->last_name ?? str($name)->after(' ')->toString(),
            '{{student_id}}' => $student?->student_id ?? 'PU-2026-001',
            '{{phone}}' => $student?->phone ?? $sponsor?->phone ?? '',
            '{{email}}' => $student?->email ?? $sponsor?->email ?? '',
            '{{level}}' => $student?->level?->name ?? 'Level 100',
            '{{programme}}' => $student?->programme?->name ?? 'BSc Business Administration',
            '{{department}}' => $student?->programme?->department?->name ?? '',
            '{{faculty}}' => $student?->programme?->department?->school?->name ?? '',
            '{{academic_year}}' => $student?->scholarships()->latest()->first()?->academicYear?->name ?? '2026/2027',
            '{{scholarship_name}}' => $student?->scholarships()->latest()->first()?->scholarshipProgramme?->name ?? 'Pentecost Excellence Scholarship',
            '{{scholarship_type}}' => $student?->scholarships()->latest()->first()?->scholarshipProgramme?->scholarship_type ?? '',
            '{{scholarship_status}}' => $student?->scholarships()->latest()->first()?->status ?? '',
            '{{sponsor_name}}' => $sponsor?->name ?? '',
            '{{organization}}' => $sponsor?->name ?? '',
            '{{contact_person}}' => $sponsor?->contact_person ?? '',
            '{{position}}' => '',
            '{{meeting_date}}' => '',
            '{{meeting_time}}' => '',
            '{{venue}}' => '',
            '{{current_date}}' => now()->format('j F Y'),
        ];

        return preg_replace('/\{\{[^}]+\}\}/', '', strtr($message, $values)) ?? '';
    }

    /**
     * @return array<int, string>
     */
    public function placeholders(): array
    {
        return [
            'name',
            'first_name',
            'last_name',
            'student_name',
            'student_id',
            'phone',
            'email',
            'programme',
            'department',
            'faculty',
            'level',
            'scholarship_type',
            'scholarship_status',
            'sponsor_name',
            'organization',
            'position',
            'meeting_date',
            'meeting_time',
            'venue',
            'current_date',
        ];
    }
}
