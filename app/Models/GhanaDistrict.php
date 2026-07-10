<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GhanaDistrict extends Model
{
    protected $fillable = ['ghana_region_id', 'name', 'category', 'capital'];

    public function region(): BelongsTo
    {
        return $this->belongsTo(GhanaRegion::class, 'ghana_region_id');
    }
}
