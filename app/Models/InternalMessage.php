<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'sender_id',
    'recipient_id',
    'broadcast_group_id',
    'subject',
    'body',
    'attachment_path',
    'attachment_original_name',
    'read_at',
    'deleted_by_sender_at',
    'deleted_by_recipient_at',
])]
class InternalMessage extends Model
{
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'deleted_by_sender_at' => 'datetime',
            'deleted_by_recipient_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
