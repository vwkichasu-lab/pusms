<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE INDEX IF NOT EXISTS idx_students_programme_level_status ON students (programme_id, level_id, student_status)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_students_name_search ON students (last_name, first_name, student_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_student_scholarships_student_status_dates ON student_scholarships (student_id, status, start_date, end_date)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_student_scholarships_programme_year ON student_scholarships (scholarship_programme_id, academic_year_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_student_results_student_year ON student_results (student_id, academic_year_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_communication_recipients_delivery ON communication_recipients (delivery_status, channel)');

        DB::statement(<<<'SQL'
            CREATE VIEW IF NOT EXISTS view_student_scholarship_summary AS
            SELECT
                students.id AS student_id,
                students.student_id AS student_number,
                students.first_name || ' ' || COALESCE(students.middle_name || ' ', '') || students.last_name AS student_name,
                programmes.name AS programme,
                levels.name AS level,
                scholarship_programmes.name AS scholarship_name,
                scholarship_programmes.scholarship_type AS scholarship_type,
                academic_years.name AS academic_year,
                student_scholarships.award_reference,
                student_scholarships.coverage_percentage,
                student_scholarships.status AS award_status,
                student_scholarships.start_date,
                student_scholarships.end_date
            FROM student_scholarships
            JOIN students ON students.id = student_scholarships.student_id
            LEFT JOIN programmes ON programmes.id = students.programme_id
            LEFT JOIN levels ON levels.id = students.level_id
            JOIN scholarship_programmes ON scholarship_programmes.id = student_scholarships.scholarship_programme_id
            LEFT JOIN academic_years ON academic_years.id = student_scholarships.academic_year_id
        SQL);

        DB::statement(<<<'SQL'
            CREATE VIEW IF NOT EXISTS view_faculty_programme_counts AS
            SELECT
                schools.id AS faculty_id,
                schools.name AS faculty,
                COUNT(DISTINCT departments.id) AS department_count,
                COUNT(programmes.id) AS programme_count
            FROM schools
            LEFT JOIN departments ON departments.school_id = schools.id
            LEFT JOIN programmes ON programmes.department_id = departments.id
            GROUP BY schools.id, schools.name
        SQL);

        DB::statement(<<<'SQL'
            CREATE VIEW IF NOT EXISTS view_student_latest_results AS
            SELECT
                students.id AS student_id,
                students.student_id AS student_number,
                students.first_name || ' ' || COALESCE(students.middle_name || ' ', '') || students.last_name AS student_name,
                academic_years.name AS academic_year,
                student_results.gpa,
                student_results.performance_status,
                student_results.created_at
            FROM student_results
            JOIN students ON students.id = student_results.student_id
            LEFT JOIN academic_years ON academic_years.id = student_results.academic_year_id
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS view_student_latest_results');
        DB::statement('DROP VIEW IF EXISTS view_faculty_programme_counts');
        DB::statement('DROP VIEW IF EXISTS view_student_scholarship_summary');
    }
};
