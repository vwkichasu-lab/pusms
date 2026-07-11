<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['student_scholarship_id', 'student_id', 'generated_by', 'reference', 'letter_date', 'signatory_name', 'signatory_title', 'body', 'generated_at'])]
class GeneratedScholarshipLetter extends Model
{
    protected function casts(): array
    {
        return [
            'letter_date' => 'date',
            'generated_at' => 'datetime',
        ];
    }

    public function award(): BelongsTo
    {
        return $this->belongsTo(StudentScholarship::class, 'student_scholarship_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
