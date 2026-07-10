<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'sponsor_type', 'contact_person', 'email', 'phone', 'address', 'notes', 'status'])]
class Sponsor extends Model
{
    use SoftDeletes;

    public function scholarshipProgrammes(): HasMany
    {
        return $this->hasMany(ScholarshipProgramme::class);
    }
}
