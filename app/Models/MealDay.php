<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealDay extends Model
{
    protected $fillable = ['participant_id', 'service_date', 'lunch', 'dinner', 'accommodation', 'synced_at'];

    protected $casts = [
        'lunch' => 'boolean',
        'dinner' => 'boolean',
        'accommodation' => 'boolean',
        'synced_at' => 'datetime',
    ];

    /** @return BelongsTo<Participant, $this> */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }
}
