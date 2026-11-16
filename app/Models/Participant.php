<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_code',
        'name',
        'organization',
        'checked_in_at',
        'sex',
        'age_bracket',
        'region',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
    ];

    public function requirements(): HasMany
    {
        return $this->hasMany(DayRequirement::class);
    }

    // Used by the search page: Participant::search($term)->get()
    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('participant_code', 'like', "%{$term}%")
                ->orWhere('organization', 'like', "%{$term}%");
        });
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function checkedInToday(): bool
    {
        return $this->attendances()->whereDate('attended_on', today())->exists();
    }

    public static function remember(array $r): self
    {
        return static::updateOrCreate(
            ['participant_code' => $r['code']],
            [
                'name' => $r['name'],
                'organization' => $r['organization'],
                'sex' => $r['sex'] ?? null,
                'age_bracket' => $r['age_bracket'] ?? null,
                'region' => $r['region'] ?? null,
            ]
        );
    }
}
