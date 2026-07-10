<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class StudentReportService
{
    public function query(array $filters = []): Builder
    {
        return Student::query()
            ->with([
                'level',
                'programme.department.school',
                'scholarships.scholarshipProgramme.sponsor',
                'scholarships.academicYear',
                'results' => fn ($query) => $query->latest(),
                'recipients.communication',
            ])
            ->when($filters['programme_id'] ?? null, fn (Builder $query, $id) => $query->where('programme_id', $id))
            ->when($filters['level_id'] ?? null, fn (Builder $query, $id) => $query->where('level_id', $id))
            ->when($filters['student_status'] ?? null, fn (Builder $query, $status) => $query->where('student_status', $status))
            ->when($filters['alumni_status'] ?? null, fn (Builder $query, $status) => $query->where('alumni_status', $status))
            ->when($filters['region'] ?? null, fn (Builder $query, $region) => $query->where('region', $region))
            ->when($filters['completion_year'] ?? null, fn (Builder $query, $year) => $query->where('graduation_year', $year))
            ->when($filters['scholarship_programme_id'] ?? null, function (Builder $query, $id): Builder {
                return $query->whereHas('scholarships', fn (Builder $scholarship): Builder => $scholarship->where('scholarship_programme_id', $id));
            })
            ->when($filters['q'] ?? null, function (Builder $query, string $search): Builder {
                return $query->where(function (Builder $nested) use ($search): void {
                    $nested
                        ->where('student_id', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function rows(array $filters = []): Collection
    {
        return $this->query($filters)->orderBy('last_name')->get()->map(function (Student $student): array {
            $search = strtolower((string) ($filters['q'] ?? ''));
            $currentScholarship = $student->scholarships->sortByDesc('created_at')->first();
            $latestResult = $student->results->first();
            [$foundIn, $actionUrl] = $this->searchContext($student, $search, $currentScholarship, $latestResult);

            return [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'name' => $student->full_name,
                'email' => $student->email,
                'phone' => $student->phone,
                'programme' => $student->programme?->name,
                'department' => $student->programme?->department?->name,
                'school' => $student->programme?->department?->school?->name,
                'level' => $student->level?->name,
                'status' => $student->student_status,
                'alumni_status' => $student->alumni_status,
                'alumni_badge' => $student->alumni_badge,
                'completion_year' => $student->graduation_year,
                'region' => $student->region,
                'district' => $student->district,
                'scholarship' => $currentScholarship?->scholarshipProgramme?->name,
                'sponsor' => $currentScholarship?->scholarshipProgramme?->sponsor?->name,
                'coverage_percentage' => $currentScholarship?->coverage_percentage,
                'accommodation' => $currentScholarship?->covers_accommodation ? 'Included' : ($currentScholarship ? 'Excluded' : null),
                'scholarship_year' => $currentScholarship?->academicYear?->name,
                'scholarship_status' => $currentScholarship?->status,
                'gpa' => $latestResult?->gpa,
                'cgpa' => $latestResult?->cgpa,
                'performance_status' => $latestResult?->performance_status,
                'found_in' => $foundIn,
                'action_url' => $actionUrl,
            ];
        });
    }

    private function searchContext(Student $student, string $search, mixed $scholarship, mixed $result): array
    {
        if ($search !== '') {
            if ($scholarship && str_contains(strtolower(implode(' ', [
                $scholarship->scholarshipProgramme?->name,
                $scholarship->award_reference,
                $scholarship->remarks,
            ])), $search)) {
                return ['Scholarship Record', route('filament.admin.resources.student-scholarships.edit', $scholarship)];
            }

            if ($result && str_contains(strtolower(implode(' ', [
                $result->course_code,
                $result->course_name,
                $result->grade,
                $result->performance_status,
            ])), $search)) {
                return ['Academic Result', route('filament.admin.resources.student-results.edit', $result)];
            }

            $communication = $student->recipients
                ->first(fn ($recipient): bool => str_contains(strtolower((string) $recipient->communication?->message), $search));

            if ($communication?->communication) {
                return ['Communication History', route('filament.admin.resources.communications.edit', $communication->communication)];
            }
        }

        if ($student->alumni_status === 'alumni') {
            return ['Alumni Record', route('filament.admin.resources.students.edit', $student)];
        }

        return ['Student Profile', route('filament.admin.resources.students.edit', $student)];
    }
}
