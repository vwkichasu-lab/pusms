<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\Level;
use App\Models\Programme;
use App\Models\ScholarshipProgramme;
use App\Models\School;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentScholarship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentAndScholarshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_be_created_with_required_academic_relationships(): void
    {
        [$programme, $level] = $this->academicSetup();

        $student = Student::create([
            'student_id' => 'PU-2026-001',
            'first_name' => 'Ama',
            'last_name' => 'Mensah',
            'email' => 'ama.mensah@example.com',
            'phone' => '+233241234567',
            'programme_id' => $programme->id,
            'level_id' => $level->id,
            'admission_year' => 2026,
            'student_status' => 'active',
            'region' => 'Greater Accra',
        ]);

        $this->assertSame('Ama Mensah', $student->full_name);
        $this->assertDatabaseHas('students', [
            'student_id' => 'PU-2026-001',
            'programme_id' => $programme->id,
            'level_id' => $level->id,
        ]);
    }

    public function test_duplicate_student_id_is_rejected_by_database(): void
    {
        [$programme, $level] = $this->academicSetup();

        Student::create($this->studentPayload($programme->id, $level->id, 'PU-2026-001', 'first@example.com'));

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);

        Student::create($this->studentPayload($programme->id, $level->id, 'PU-2026-001', 'second@example.com'));
    }

    public function test_duplicate_scholarship_assignment_for_same_period_is_rejected(): void
    {
        [$programme, $level] = $this->academicSetup();
        [$academicYear, $semester] = $this->calendarSetup();
        $student = Student::create($this->studentPayload($programme->id, $level->id));
        $scholarshipProgramme = ScholarshipProgramme::create([
            'name' => 'Pentecost Excellence Scholarship',
            'code' => 'PES',
            'coverage_type' => 'partial',
            'status' => 'active',
        ]);

        $payload = [
            'student_id' => $student->id,
            'scholarship_programme_id' => $scholarshipProgramme->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'status' => 'active',
        ];

        StudentScholarship::create($payload);

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);

        StudentScholarship::create($payload);
    }

    /**
     * @return array{0: Programme, 1: Level}
     */
    private function academicSetup(): array
    {
        $school = School::create(['name' => 'School of Business', 'code' => 'SOB', 'status' => 'active']);
        $department = Department::create(['school_id' => $school->id, 'name' => 'Management', 'code' => 'MGT', 'status' => 'active']);
        $programme = Programme::create(['department_id' => $department->id, 'name' => 'BSc Business Administration', 'code' => 'BBA', 'status' => 'active']);
        $level = Level::create(['name' => 'Level 100', 'numeric_value' => 100]);

        return [$programme, $level];
    }

    /**
     * @return array{0: AcademicYear, 1: Semester}
     */
    private function calendarSetup(): array
    {
        $academicYear = AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-08-01',
            'end_date' => '2027-07-31',
            'status' => 'current',
        ]);
        $semester = Semester::create([
            'academic_year_id' => $academicYear->id,
            'name' => 'First Semester',
            'status' => 'current',
        ]);

        return [$academicYear, $semester];
    }

    /**
     * @return array<string, mixed>
     */
    private function studentPayload(int $programmeId, int $levelId, string $studentId = 'PU-2026-001', string $email = 'ama.mensah@example.com'): array
    {
        return [
            'student_id' => $studentId,
            'first_name' => 'Ama',
            'last_name' => 'Mensah',
            'email' => $email,
            'phone' => '+233241234567',
            'programme_id' => $programmeId,
            'level_id' => $levelId,
            'admission_year' => 2026,
            'student_status' => 'active',
        ];
    }
}
