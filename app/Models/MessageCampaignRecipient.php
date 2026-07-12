<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'campaign_id',
    'recipient_type',
    'recipient_id',
    'recipient_name',
    'phone_number',
    'normalized_phone',
    'personalized_message',
    'whatsapp_url',
    'status',
    'validation_error',
    'opened_at',
    'marked_sent_at',
    'skipped_at',
])]
class MessageCampaignRecipient extends Model
{
    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'marked_sent_at' => 'datetime',
            'skipped_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MessageCampaign::class, 'campaign_id');
    }
}
