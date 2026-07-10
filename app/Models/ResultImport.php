<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultImport extends Model
{
    protected $fillable = ['original_filename', 'stored_path', 'uploaded_by', 'status', 'total_rows', 'valid_rows', 'invalid_rows', 'preview_rows', 'errors'];

    protected function casts(): array
    {
        return [
            'preview_rows' => 'array',
            'errors' => 'array',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
