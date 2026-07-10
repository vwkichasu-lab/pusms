<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChurchDistrict extends Model
{
    protected $fillable = ['church_area_id', 'name', 'status'];

    public function area(): BelongsTo
    {
        return $this->belongsTo(ChurchArea::class, 'church_area_id');
    }
}
