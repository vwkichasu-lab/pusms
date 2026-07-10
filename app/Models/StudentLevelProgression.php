<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentLevelProgression extends Model
{
    protected $fillable = ['student_id', 'previous_level_id', 'new_level_id', 'academic_year_id', 'updated_by', 'update_type', 'notes'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function previousLevel(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'previous_level_id');
    }

    public function newLevel(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'new_level_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
