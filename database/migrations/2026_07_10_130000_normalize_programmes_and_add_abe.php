<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $fesac = DB::table('schools')->where('code', 'FESAC')->first();
        $itDepartment = $fesac
            ? DB::table('departments')->where('school_id', $fesac->id)->where('code', 'IT')->first()
            : null;

        if ($itDepartment) {
            $bise = DB::table('programmes')->where('code', 'BISE')->first();
            $bse = DB::table('programmes')->where('code', 'BSE')->first();

            if ($bise) {
                DB::table('programmes')->where('id', $bise->id)->update([
                    'department_id' => $itDepartment->id,
                    'name' => 'BSc. Industrial Software Engineering',
                    'status' => 'active',
                    'updated_at' => now(),
                ]);

                if ($bse) {
                    DB::table('students')->where('programme_id', $bse->id)->update(['programme_id' => $bise->id]);
                    DB::table('programmes')->where('id', $bse->id)->delete();
                }
            } elseif ($bse) {
                DB::table('programmes')->where('id', $bse->id)->update([
                    'department_id' => $itDepartment->id,
                    'name' => 'BSc. Industrial Software Engineering',
                    'code' => 'BISE',
                    'status' => 'active',
                    'updated_at' => now(),
                ]);
            }
        }

        $cofopsId = DB::table('schools')->updateOrInsert(
            ['code' => 'COFOPS'],
            [
                'name' => 'College of Foundation and Professional Studies',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        $cofops = DB::table('schools')->where('code', 'COFOPS')->first();

        if (! $cofops) {
            return;
        }

        DB::table('departments')->updateOrInsert(
            ['school_id' => $cofops->id, 'code' => 'PBP'],
            [
                'name' => 'Professional Business Programmes',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        $department = DB::table('departments')->where('school_id', $cofops->id)->where('code', 'PBP')->first();

        if (! $department) {
            return;
        }

        DB::table('programmes')->updateOrInsert(
            ['code' => 'ABE'],
            [
                'department_id' => $department->id,
                'name' => 'Association of Business Executives (ABE) - Diploma Levels 4, 5 and 6',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        DB::table('departments')->updateOrInsert(
            ['school_id' => $cofops->id, 'code' => 'PCP'],
            [
                'name' => 'Professional Computing Programmes',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        $computingDepartment = DB::table('departments')->where('school_id', $cofops->id)->where('code', 'PCP')->first();

        if ($computingDepartment) {
            DB::table('programmes')->updateOrInsert(
                ['code' => 'NCC'],
                [
                    'department_id' => $computingDepartment->id,
                    'name' => 'National Computing Centre Education (NCC Education)',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }

        $fhas = DB::table('schools')->where('code', 'FHAS')->first();

        if ($fhas) {
            $codeUpdates = [
                ['department' => 'HIM', 'code' => 'BHIM', 'old' => ['BHM', 'BSCHIM'], 'name' => 'BSc. Health Information Management'],
                ['department' => 'NUR', 'code' => 'BNS', 'old' => ['BNR', 'BSCNUR'], 'name' => 'BSc. Nursing'],
                ['department' => 'MID', 'code' => 'BMWF', 'old' => ['BMW', 'BSCMID'], 'name' => 'BSc. Midwifery'],
            ];

            foreach ($codeUpdates as $item) {
                $department = DB::table('departments')->where('school_id', $fhas->id)->where('code', $item['department'])->first();

                if (! $department) {
                    continue;
                }

                $target = DB::table('programmes')->where('code', $item['code'])->first();
                $old = DB::table('programmes')->whereIn('code', $item['old'])->first();

                if ($target) {
                    DB::table('programmes')->where('id', $target->id)->update([
                        'department_id' => $department->id,
                        'name' => $item['name'],
                        'updated_at' => now(),
                    ]);

                    if ($old && $old->id !== $target->id) {
                        DB::table('students')->where('programme_id', $old->id)->update(['programme_id' => $target->id]);
                        DB::table('programmes')->where('id', $old->id)->delete();
                    }
                } elseif ($old) {
                    DB::table('programmes')->where('id', $old->id)->update([
                        'department_id' => $department->id,
                        'name' => $item['name'],
                        'code' => $item['code'],
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::table('departments')->updateOrInsert(
                ['school_id' => $fhas->id, 'code' => 'HM'],
                [
                    'name' => 'Herbal Medicine',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );

            $herbalDepartment = DB::table('departments')->where('school_id', $fhas->id)->where('code', 'HM')->first();

            if ($herbalDepartment) {
                DB::table('programmes')->updateOrInsert(
                    ['code' => 'BDHM'],
                    [
                        'department_id' => $herbalDepartment->id,
                        'name' => 'Doctor of Herbal Medicine',
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            }
        }
    }

    public function down(): void
    {
        DB::table('programmes')->where('code', 'ABE')->delete();
        DB::table('programmes')->whereIn('code', ['NCC', 'BDHM'])->delete();
    }
};
