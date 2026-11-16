<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DayRequirement extends Model
{
    protected $fillable = [
        'participant_id',
        'day',
        'lunch',
        'dinner',
        'accommodation',
        'synced_at',
    ];

    protected $casts = [
        'lunch' => 'boolean',
        'dinner' => 'boolean',
        'accommodation' => 'boolean',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }
}