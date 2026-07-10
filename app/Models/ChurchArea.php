<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChurchArea extends Model
{
    protected $fillable = ['name', 'status'];

    public function districts(): HasMany
    {
        return $this->hasMany(ChurchDistrict::class);
    }
}
