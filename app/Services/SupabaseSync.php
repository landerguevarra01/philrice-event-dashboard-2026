<?php

namespace App\Services;

use App\Models\Participant;
use Illuminate\Support\Facades\Http;

class SupabaseSync
{
    public function syncRequirements(Participant $participant): bool
    {
        $url = config('services.supabase.url');
        $key = config('services.supabase.key');

        if (! $url || ! $key) {
            return false;
        }

        $rows = $participant->requirements->map(fn ($r) => [
            'participant_code' => $participant->participant_code,
            'participant_name' => $participant->name,
            'organization' => $participant->organization,
            'day' => $r->day,
            'lunch' => $r->lunch,
            'dinner' => $r->dinner,
            'accommodation' => $r->accommodation,
            'updated_at' => now()->toIso8601String(),
        ])->values()->all();

        if (empty($rows)) {
            return true;
        }

        try {
            $response = Http::connectTimeout(3)->timeout(8)
                ->withHeaders([
                    'apikey' => $key,
                    'Authorization' => 'Bearer '.$key,
                    // update the row if (participant_code, day) already exists
                    'Prefer' => 'resolution=merge-duplicates,return=minimal',
                ])
                ->post(rtrim($url, '/').'/rest/v1/day_requirements?on_conflict=participant_code,day', $rows);

            if (! $response->successful()) {
                logger()->warning('Supabase sync failed', ['status' => $response->status(), 'body' => $response->body()]);
            }

            return $response->successful();
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }
}
