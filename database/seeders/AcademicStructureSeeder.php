<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\Level;
use App\Models\Programme;
use App\Models\School;
use App\Models\Semester;
use Illuminate\Database\Seeder;

class AcademicStructureSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([100, 200, 300, 400] as $level) {
            Level::firstOrCreate(
                ['numeric_value' => $level],
                ['name' => "Level {$level}"],
            );
        }

        $academicYear = AcademicYear::firstOrCreate(
            ['name' => '2026/2027'],
            [
                'start_date' => '2026-08-01',
                'end_date' => '2027-07-31',
                'status' => 'current',
            ],
        );

        foreach (['First Semester', 'Second Semester'] as $semester) {
            Semester::firstOrCreate(
                [
                    'academic_year_id' => $academicYear->id,
                    'name' => $semester,
                ],
                ['status' => 'upcoming'],
            );
        }

        $this->seedPentecostProgrammes();
    }

    private function seedPentecostProgrammes(): void
    {
        School::query()->where('name', 'Faculty of Engineering Science and Computing')
            ->update(['name' => 'Faculty of Engineering, Science and Computing', 'code' => 'FESAC']);
        School::query()->where('code', 'FOBA')->update(['code' => 'FBA']);
        School::query()->where('code', 'FOESAC')->update(['code' => 'FESAC']);
        School::query()->where('code', 'SOTMAL')->update(['code' => 'STML']);

        $groups = [
            ['name' => 'Faculty of Business Administration', 'code' => 'FBA', 'departments' => [
                ['name' => 'Communication Studies', 'code' => 'COM', 'programmes' => [
                    ['BSc. Communication Studies', 'BCS', ['BACOMM']],
                ]],
                ['name' => 'Accounting', 'code' => 'ACC', 'programmes' => [
                    ['BSc. Accounting', 'BAC', ['BSCBAACC']],
                    ['BCom. Accounting with Computing', 'BACW', ['BCOMAC']],
                ]],
                ['name' => 'Banking and Finance', 'code' => 'BF', 'programmes' => [
                    ['BSc. Banking and Finance', 'BBF', ['BSCBABF']],
                ]],
                ['name' => 'Corporate and Business Development', 'code' => 'CBD', 'programmes' => [
                    ['BSc. Corporate and Business Development', 'BCD', ['BSCBACBD']],
                ]],
                ['name' => 'Human Resource Management', 'code' => 'HRM', 'programmes' => [
                    ['BSc. Human Resource Management', 'BHR', ['BSCBAHRM']],
                ]],
                ['name' => 'Insurance and Actuarial Studies', 'code' => 'IAS', 'programmes' => [
                    ['BSc. Insurance with Actuarial Science', 'BIA', ['BSCBAIAS']],
                ]],
                ['name' => 'Logistics and Supply Chain Management', 'code' => 'LSC', 'programmes' => [
                    ['BSc. Logistics and Supply Chain Management', 'BLS', ['BSCBALS']],
                ]],
                ['name' => 'Marketing', 'code' => 'MKT', 'programmes' => [
                    ['BSc. Marketing', 'BMK', ['BSCBAMKT']],
                ]],
            ]],
            ['name' => 'Faculty of Engineering, Science and Computing', 'code' => 'FESAC', 'departments' => [
                ['name' => 'Actuarial Science', 'code' => 'AS', 'programmes' => [
                    ['BSc. Actuarial Science', 'BAS', ['BSCAS']],
                ]],
                ['name' => 'Construction Technology and Engineering Management', 'code' => 'CTEM', 'programmes' => [
                    ['BSc. Construction Technology and Engineering Management', 'BCT', ['BSCCTEM']],
                ]],
                ['name' => 'Information Technology', 'code' => 'IT', 'programmes' => [
                    ['BSc. Information Technology', 'BIT', ['BSCIT']],
                    ['BSc. Industrial Software Engineering', 'BISE', ['BSCISE', 'BSE']],
                ]],
                ['name' => 'Quantity Surveying and Building Economics', 'code' => 'QSBE', 'programmes' => [
                    ['BSc. Quantity Surveying and Building Economics', 'BQS', ['BSCQSBE']],
                ]],
            ]],
            ['name' => 'Faculty of Health and Allied Sciences', 'code' => 'FHAS', 'departments' => [
                ['name' => 'Health Information Management', 'code' => 'HIM', 'programmes' => [
                    ['BSc. Health Information Management', 'BHIM', ['BSCHIM', 'BHM']],
                ]],
                ['name' => 'Midwifery', 'code' => 'MID', 'programmes' => [
                    ['BSc. Midwifery', 'BMWF', ['BSCMID', 'BMW']],
                ]],
                ['name' => 'Nursing', 'code' => 'NUR', 'programmes' => [
                    ['BSc. Nursing', 'BNS', ['BSCNUR', 'BNR']],
                ]],
                ['name' => 'Physician Assistant Studies', 'code' => 'PAS', 'programmes' => [
                    ['BSc. Physician Assistant Studies', 'BPA', ['BSCPAS']],
                ]],
                ['name' => 'Herbal Medicine', 'code' => 'HM', 'programmes' => [
                    ['Doctor of Herbal Medicine', 'BDHM', []],
                ]],
            ]],
            ['name' => 'Faculty of Law', 'code' => 'FLaw', 'departments' => [
                ['name' => 'Law', 'code' => 'LAW', 'programmes' => [
                    ['Bachelor of Laws (LL.B)', 'LLB', []],
                ]],
            ]],
            ['name' => 'School of Theology, Mission and Leadership', 'code' => 'STML', 'departments' => [
                ['name' => 'Theology and Mission', 'code' => 'TM', 'programmes' => [
                    ['BA. Theology and Mission', 'BTM', ['BATM']],
                ]],
            ]],
            ['name' => 'College of Foundation and Professional Studies', 'code' => 'COFOPS', 'departments' => [
                ['name' => 'Professional Business Programmes', 'code' => 'PBP', 'programmes' => [
                    ['Association of Business Executives (ABE) - Diploma Levels 4, 5 and 6', 'ABE', []],
                ]],
                ['name' => 'Professional Computing Programmes', 'code' => 'PCP', 'programmes' => [
                    ['National Computing Centre Education (NCC Education)', 'NCC', []],
                ]],
            ]],
        ];

        foreach ($groups as $schoolData) {
            $school = School::updateOrCreate(
                ['name' => $schoolData['name']],
                ['code' => $schoolData['code'], 'status' => 'active'],
            );

            foreach ($schoolData['departments'] as $departmentData) {
                $department = Department::updateOrCreate(
                    ['school_id' => $school->id, 'name' => $departmentData['name']],
                    ['code' => $departmentData['code'], 'status' => 'active'],
                );

                foreach ($departmentData['programmes'] as [$name, $code, $oldCodes]) {
                    $programme = Programme::query()
                        ->where('code', $code)
                        ->first()
                        ?? Programme::query()
                            ->whereIn('code', $oldCodes)
                            ->orWhere('name', $name)
                            ->first();

                    if ($programme) {
                        $programme->update([
                            'department_id' => $department->id,
                            'name' => $name,
                            'code' => $code,
                            'status' => 'active',
                        ]);

                        continue;
                    }

                    Programme::create([
                        'department_id' => $department->id,
                        'name' => $name,
                        'code' => $code,
                        'status' => 'active',
                    ]);
                }
            }
        }
    }
}
