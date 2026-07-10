<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'student_id',
    'first_name',
    'middle_name',
    'last_name',
    'email',
    'phone',
    'programme_id',
    'level_id',
    'admission_year',
    'student_status',
    'student_batch',
    'graduation_year',
    'alumni_status',
    'alumni_badge',
    'date_of_birth',
    'home_town',
    'country',
    'district',
    'region',
    'profile_photo',
])]
class Student extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'admission_year' => 'integer',
            'graduation_year' => 'integer',
        ];
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function scholarships(): HasMany
    {
        return $this->hasMany(StudentScholarship::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(StudentResult::class);
    }

    public function levelProgressions(): HasMany
    {
        return $this->hasMany(StudentLevelProgression::class);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(CommunicationRecipient::class);
    }

    public function getFullNameAttribute(): string
    {
        return collect([$this->first_name, $this->middle_name, $this->last_name])
            ->filter()
            ->join(' ');
    }
}
