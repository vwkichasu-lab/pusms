<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable(['name', 'code', 'status'])]
class School extends Model
{
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function programmes(): HasManyThrough
    {
        return $this->hasManyThrough(Programme::class, Department::class);
    }
}
