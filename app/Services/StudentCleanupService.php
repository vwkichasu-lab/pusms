<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class StudentCleanupService
{
    /**
     * @return array<string, int>
     */
    public function clearAllStudents(): array
    {
        return DB::transaction(function (): array {
            $counts = [
                'communication_recipients' => DB::table('communication_recipients')->whereNotNull('student_id')->count(),
                'student_level_progressions' => DB::table('student_level_progressions')->count(),
                'student_results' => DB::table('student_results')->count(),
                'student_scholarships' => DB::table('student_scholarships')->count(),
                'students' => DB::table('students')->count(),
                'deleted_students' => DB::table('students')->whereNotNull('deleted_at')->count(),
            ];

            DB::table('communication_recipients')->whereNotNull('student_id')->delete();
            DB::table('student_level_progressions')->delete();
            DB::table('student_results')->delete();
            DB::table('student_scholarships')->delete();
            DB::table('students')->delete();

            return $counts;
        });
    }
}
