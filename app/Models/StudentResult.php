<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'student_id',
    'index_number',
    'academic_year_id',
    'semester_id',
    'programme_snapshot',
    'level_snapshot',
    'course_code',
    'course_name',
    'credit_hours',
    'grade',
    'grade_point',
    'score',
    'gpa',
    'cgpa',
    'credits_attempted',
    'credits_passed',
    'performance_status',
    'data_source',
    'created_or_imported_at',
    'remarks',
    'recorded_by',
])]
class StudentResult extends Model
{
    protected function casts(): array
    {
        return [
            'gpa' => 'decimal:2',
            'cgpa' => 'decimal:2',
            'grade_point' => 'decimal:2',
            'score' => 'decimal:2',
            'credit_hours' => 'integer',
            'credits_attempted' => 'integer',
            'credits_passed' => 'integer',
            'created_or_imported_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
