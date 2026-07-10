<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ChurchArea;
use App\Models\ChurchDistrict;
use App\Models\Level;
use App\Models\Programme;
use App\Models\ScholarshipProgramme;
use App\Models\Semester;
use App\Models\Sponsor;
use App\Models\Student;
use App\Models\StudentScholarship;
use App\Models\User;
use Illuminate\Database\Seeder;

class SampleScholarshipDataSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = AcademicYear::updateOrCreate(
            ['name' => '2025/2026'],
            [
                'start_date' => '2025-08-01',
                'end_date' => '2026-07-31',
                'status' => 'current',
            ],
        );

        $firstSemester = Semester::firstOrCreate(
            ['academic_year_id' => $academicYear->id, 'name' => 'First Semester'],
            ['status' => 'current'],
        );

        Semester::firstOrCreate(
            ['academic_year_id' => $academicYear->id, 'name' => 'Second Semester'],
            ['status' => 'upcoming'],
        );

        $puSponsor = Sponsor::updateOrCreate(
            ['name' => 'Pentecost University'],
            [
                'sponsor_type' => 'University',
                'contact_person' => 'Scholarship Office',
                'email' => 'scholarships@pentvars.edu.gh',
                'phone' => '0302417057',
                'address' => 'Pentecost University, Sowutuom, Accra',
                'notes' => 'Internal university bursary and scholarship support.',
                'status' => 'active',
            ],
        );

        $churchSponsor = Sponsor::updateOrCreate(
            ['name' => 'The Church of Pentecost - Kaneshie Area'],
            [
                'sponsor_type' => 'Church / Organization',
                'contact_person' => 'Apostle Daniel Mensah',
                'email' => 'kaneshie.area@thecophq.org',
                'phone' => '0244123456',
                'address' => 'Kaneshie Area Office, Accra',
                'notes' => 'Supports selected Pentecost University students under bursary and need-based awards.',
                'status' => 'active',
            ],
        );

        Sponsor::updateOrCreate(
            ['name' => 'Pentecost University Alumni Association'],
            [
                'sponsor_type' => 'Alumni / Association',
                'contact_person' => 'Mrs. Abigail Owusu',
                'email' => 'alumni.support@pentvars.edu.gh',
                'phone' => '0209876543',
                'address' => 'Pentecost University, Sowutuom, Accra',
                'notes' => 'Provides partial scholarships to continuing students with good academic performance.',
                'status' => 'active',
            ],
        );

        $kaneshieArea = ChurchArea::firstOrCreate(
            ['name' => 'Kaneshie Area'],
            ['status' => 'active'],
        );

        $kaneshiePiwc = ChurchDistrict::firstOrCreate(
            ['church_area_id' => $kaneshieArea->id, 'name' => 'Kaneshie PIWC'],
            ['status' => 'active'],
        );

        $puBursary = ScholarshipProgramme::updateOrCreate(
            ['code' => 'PU-BA-2025'],
            [
                'sponsor_id' => $puSponsor->id,
                'academic_year_id' => $academicYear->id,
                'name' => 'PU Bursary Award 2025/2026',
                'description' => 'Full tuition support excluding hostel/accommodation fees for selected students.',
                'eligibility_criteria' => 'Continuing students with financial need, good academic performance, and good conduct.',
                'coverage_type' => 'full',
                'default_coverage_percentage' => 100,
                'default_covers_accommodation' => false,
                'is_renewable' => true,
                'scholarship_type' => 'pu_bursary',
                'requires_church_area' => false,
                'requires_church_district' => false,
                'church_area_id' => null,
                'church_district_id' => null,
                'status' => 'active',
            ],
        );

        $areaScholarship = ScholarshipProgramme::updateOrCreate(
            ['code' => 'COP-AS-2025'],
            [
                'sponsor_id' => $churchSponsor->id,
                'academic_year_id' => $academicYear->id,
                'name' => 'Church of Pentecost Area Scholarship 2025/2026',
                'description' => 'Partial tuition support for students recommended by a Church of Pentecost Area, District, PIWC, or Local Assembly.',
                'eligibility_criteria' => 'Student must be recommended by the Church Area, PIWC, District, or Local Assembly and maintain good academic and moral standing.',
                'coverage_type' => 'partial',
                'default_coverage_percentage' => 50,
                'default_covers_accommodation' => false,
                'is_renewable' => true,
                'scholarship_type' => 'area',
                'requires_church_area' => true,
                'requires_church_district' => true,
                'church_area_id' => $kaneshieArea->id,
                'church_district_id' => $kaneshiePiwc->id,
                'status' => 'active',
            ],
        );

        $itProgramme = Programme::query()->where('code', 'BIT')->first() ?? Programme::query()->where('name', 'like', '%Information Technology%')->first();
        $iseProgramme = Programme::query()->where('code', 'BISE')->first() ?? $itProgramme;
        $level400 = Level::query()->where('numeric_value', 400)->first();
        $level200 = Level::query()->where('numeric_value', 200)->first() ?? $level400;

        $victor = Student::updateOrCreate(
            ['student_id' => 'PUIT/22110014'],
            [
                'first_name' => 'Victor',
                'middle_name' => 'Wugajah',
                'last_name' => 'Kichasu',
                'email' => 'vwkichasu@gmail.com',
                'phone' => '0244001100',
                'programme_id' => $itProgramme?->id,
                'level_id' => $level400?->id,
                'admission_year' => 2022,
                'student_status' => 'active',
                'student_batch' => '2022 Cohort',
                'graduation_year' => 2026,
                'alumni_status' => 'not_alumni',
                'alumni_badge' => null,
                'date_of_birth' => '2000-05-12',
                'home_town' => 'Sowutuom',
                'country' => 'Ghana',
                'region' => 'Greater Accra',
                'district' => 'Ablekuma North Municipal',
            ],
        );

        $ama = Student::updateOrCreate(
            ['student_id' => 'PUIT/23110452'],
            [
                'first_name' => 'Ama',
                'middle_name' => 'Serwaa',
                'last_name' => 'Boateng',
                'email' => 'ama.boateng@example.com',
                'phone' => '0204455667',
                'programme_id' => $iseProgramme?->id,
                'level_id' => $level200?->id,
                'admission_year' => 2023,
                'student_status' => 'active',
                'student_batch' => '2023 Cohort',
                'graduation_year' => 2027,
                'alumni_status' => 'not_alumni',
                'alumni_badge' => null,
                'date_of_birth' => '2002-09-20',
                'home_town' => 'Kaneshie',
                'country' => 'Ghana',
                'region' => 'Greater Accra',
                'district' => 'Okaikwei North Municipal',
            ],
        );

        $approver = User::query()->where('email', 'admin@pentvars.edu.gh')->first();

        $this->awardStudent($victor, $puBursary, $academicYear, $firstSemester, [
            'award_date' => '2025-11-28',
            'start_date' => '2025-11-28',
            'end_date' => '2026-08-31',
            'coverage_percentage' => 100,
            'covers_accommodation' => false,
            'covers_tuition' => true,
            'covers_stipend' => false,
            'coverage_notes' => '100% tuition excluding accommodation.',
            'amount_awarded' => null,
            'status' => 'active',
            'approved_by' => $approver?->id,
            'remarks' => 'Awarded 100% bursary support on tuition fees excluding hostel fees. Student is expected to maintain strong academic performance and reapply each academic year.',
        ]);

        $this->awardStudent($ama, $areaScholarship, $academicYear, $firstSemester, [
            'award_date' => '2025-11-28',
            'start_date' => '2025-11-28',
            'end_date' => '2026-08-31',
            'coverage_percentage' => 50,
            'covers_accommodation' => false,
            'covers_tuition' => true,
            'covers_stipend' => false,
            'coverage_notes' => '50% tuition only. Accommodation excluded.',
            'amount_awarded' => null,
            'status' => 'active',
            'approved_by' => $approver?->id,
            'remarks' => 'Recommended by Kaneshie Area. Beneficiary receives 50% tuition support and must maintain good academic and moral standing.',
        ]);
    }

    private function awardStudent(Student $student, ScholarshipProgramme $programme, AcademicYear $academicYear, Semester $semester, array $data): void
    {
        $award = StudentScholarship::firstOrNew([
            'student_id' => $student->id,
            'scholarship_programme_id' => $programme->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
        ]);

        $award->fill($data);

        if ($award->exists && blank($award->award_reference)) {
            $award->award_reference = StudentScholarship::generateAwardReference($award);
        }

        $award->save();
    }
}
