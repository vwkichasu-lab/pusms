<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->fixHealthCodes();
        $this->addProfessionalProgrammes();
    }

    public function down(): void
    {
        DB::table('programmes')->whereIn('code', ['NCC', 'BDHM'])->delete();
    }

    private function fixHealthCodes(): void
    {
        $fhas = DB::table('schools')->where('code', 'FHAS')->first();

        if (! $fhas) {
            return;
        }

        $updates = [
            ['department' => 'HIM', 'code' => 'BHIM', 'old' => ['BHM', 'BSCHIM'], 'name' => 'BSc. Health Information Management'],
            ['department' => 'NUR', 'code' => 'BNS', 'old' => ['BNR', 'BSCNUR'], 'name' => 'BSc. Nursing'],
            ['department' => 'MID', 'code' => 'BMWF', 'old' => ['BMW', 'BSCMID'], 'name' => 'BSc. Midwifery'],
        ];

        foreach ($updates as $item) {
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

                continue;
            }

            if ($old) {
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
            ['name' => 'Herbal Medicine', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        );

        $department = DB::table('departments')->where('school_id', $fhas->id)->where('code', 'HM')->first();

        if ($department) {
            DB::table('programmes')->updateOrInsert(
                ['code' => 'BDHM'],
                ['department_id' => $department->id, 'name' => 'Doctor of Herbal Medicine', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            );
        }
    }

    private function addProfessionalProgrammes(): void
    {
        DB::table('schools')->updateOrInsert(
            ['code' => 'COFOPS'],
            ['name' => 'College of Foundation and Professional Studies', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        );

        $cofops = DB::table('schools')->where('code', 'COFOPS')->first();

        if (! $cofops) {
            return;
        }

        DB::table('departments')->updateOrInsert(
            ['school_id' => $cofops->id, 'code' => 'PCP'],
            ['name' => 'Professional Computing Programmes', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        );

        $department = DB::table('departments')->where('school_id', $cofops->id)->where('code', 'PCP')->first();

        if ($department) {
            DB::table('programmes')->updateOrInsert(
                ['code' => 'NCC'],
                ['department_id' => $department->id, 'name' => 'National Computing Centre Education (NCC Education)', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            );
        }
    }
};
