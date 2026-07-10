<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['original_filename', 'stored_path', 'total_rows', 'successful_rows', 'failed_rows', 'errors', 'uploaded_by', 'status'])]
class StudentImport extends Model
{
    protected function casts(): array
    {
        return [
            'errors' => 'array',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
