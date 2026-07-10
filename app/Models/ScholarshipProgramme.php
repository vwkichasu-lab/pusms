<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['sponsor_id', 'academic_year_id', 'name', 'code', 'description', 'eligibility_criteria', 'coverage_type', 'default_coverage_percentage', 'default_covers_accommodation', 'is_renewable', 'scholarship_type', 'requires_church_area', 'requires_church_district', 'church_area_id', 'church_district_id', 'status'])]
class ScholarshipProgramme extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'requires_church_area' => 'boolean',
            'requires_church_district' => 'boolean',
            'default_covers_accommodation' => 'boolean',
            'is_renewable' => 'boolean',
            'default_coverage_percentage' => 'decimal:2',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(Sponsor::class);
    }

    public function churchArea(): BelongsTo
    {
        return $this->belongsTo(ChurchArea::class);
    }

    public function churchDistrict(): BelongsTo
    {
        return $this->belongsTo(ChurchDistrict::class);
    }

    public function studentScholarships(): HasMany
    {
        return $this->hasMany(StudentScholarship::class);
    }
}
