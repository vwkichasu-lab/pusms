<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable([
    'student_id',
    'scholarship_programme_id',
    'academic_year_id',
    'semester_id',
    'award_date',
    'start_date',
    'end_date',
    'coverage_percentage',
    'covers_accommodation',
    'covers_tuition',
    'covers_stipend',
    'coverage_notes',
    'amount_awarded',
    'status',
    'scholarship_stage',
    'award_reference',
    'approved_by',
    'remarks',
])]
class StudentScholarship extends Model
{
    public const STAGE_NEW_AWARD = 'new_award';

    public const STAGE_EXISTING_BENEFICIARY = 'existing_beneficiary';

    /**
     * @return array<string, string>
     */
    public static function stageOptions(): array
    {
        return [
            self::STAGE_NEW_AWARD => 'Newly Awarded',
            self::STAGE_EXISTING_BENEFICIARY => 'Existing Beneficiary',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (StudentScholarship $award): void {
            if (! filled($award->scholarship_stage)) {
                $award->scholarship_stage = self::STAGE_NEW_AWARD;
            }

            if (! filled($award->award_reference)) {
                $award->award_reference = self::generateAwardReference($award);
            }
        });
    }

    public static function generateAwardReference(self $award): string
    {
        $award->loadMissing('scholarshipProgramme');

        $code = $award->scholarshipProgramme?->code ?: 'PU-SCH';
        $parts = collect(preg_split('/[^A-Za-z]+/', $code))
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => strtoupper($part));

        $prefix = $parts->isNotEmpty() ? $parts->join('/') : 'PU/SCH';
        $date = $award->award_date ? Carbon::parse($award->award_date) : now();
        $baseNumber = max((int) self::query()->max('id') + 1, 1);

        do {
            $reference = sprintf('%s/%03d/%s', $prefix, $baseNumber, $date->format('m/y'));
            $baseNumber++;
        } while (self::query()->where('award_reference', $reference)->exists());

        return $reference;
    }

    public function getScholarshipStageLabelAttribute(): string
    {
        return self::stageOptions()[$this->scholarship_stage] ?? ucfirst(str_replace('_', ' ', (string) $this->scholarship_stage));
    }

    protected function casts(): array
    {
        return [
            'award_date' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
            'coverage_percentage' => 'decimal:2',
            'covers_accommodation' => 'boolean',
            'covers_tuition' => 'boolean',
            'covers_stipend' => 'boolean',
            'amount_awarded' => 'decimal:2',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function scholarshipProgramme(): BelongsTo
    {
        return $this->belongsTo(ScholarshipProgramme::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
