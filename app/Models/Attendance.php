<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = ['participant_id', 'attended_on', 'checked_in_at', 'synced_at', 'sync_error'];

    protected $casts = [
        'attended_on' => 'date',
        'checked_in_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }
}
