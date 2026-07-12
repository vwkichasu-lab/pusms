<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'campaign_name',
    'recipient_type',
    'subject',
    'message_body',
    'channel',
    'total_recipients',
    'valid_recipients',
    'invalid_recipients',
    'pending_count',
    'opened_count',
    'sent_count',
    'skipped_count',
    'failed_count',
    'status',
    'created_by',
    'completed_at',
    'filters',
])]
class MessageCampaign extends Model
{
    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(MessageCampaignRecipient::class, 'campaign_id');
    }
}
