<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\MealDay;
use App\Models\Participant;

class PendingSync
{
    public function __construct(private ParticipantApi $api, private SupabaseSync $supabase) {}

    /** @return array{checkins: int, meals: int} */
    public function counts(): array
    {
        return [
            'checkins' => Attendance::whereNull('synced_at')->count(),
            'meals' => MealDay::whereNull('synced_at')->count(),
        ];
    }

    /** @return array{checkins: int, meals: int} */
    public function run(): array
    {
        $sent = ['checkins' => 0, 'meals' => 0];

        // 1) Check-ins to the registration API, oldest first
        $pending = Attendance::with('participant')->whereNull('synced_at')->orderBy('checked_in_at')->get();

        foreach ($pending as $a) {
            $r = $this->api->checkIn($a->participant->participant_code);

            if ($r['offline']) {
                break;   // still offline, stop and try again later
            }

            if ($r['ok'] || $r['already']) {
                $a->update(['synced_at' => now(), 'sync_error' => null]);
                $sent['checkins']++;
            } else {
                $a->update(['sync_error' => $r['message']]);
            }
        }

        // 2) Meals and accommodation to Supabase
        $ids = MealDay::whereNull('synced_at')->pluck('participant_id')->unique();

        foreach (Participant::with('mealDays')->whereIn('id', $ids)->get() as $p) {
            if (! $this->supabase->syncRequirements($p)) {
                break;
            }

            MealDay::where('participant_id', $p->id)->whereNull('synced_at')->update(['synced_at' => now()]);
            $sent['meals']++;
        }

        return $sent;
    }
}
