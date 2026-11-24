<?php

namespace App\Services;

use App\Models\Participant;
use Illuminate\Support\Facades\Http;

class SupabaseSync
{
    public function syncRequirements(Participant $participant): bool
    {
        $url = (string) config('services.supabase.url');
        $key = (string) config('services.supabase.key');

        if ($url === '' || $key === '') {
            return false;
        }

        $from = $participant->stay_from;
        $to = $participant->stay_to;

        // No dates chosen yet, so there is nothing to send
        if ($from === null || $to === null) {
            return true;
        }

        $rows = $participant->mealDays
            ->filter(fn ($m) => $m->service_date >= $from && $m->service_date <= $to)
            ->map(fn ($m) => [
                'participant_code' => $participant->participant_code,
                'participant_name' => $participant->name,
                'organization' => $participant->organization,
                'service_date' => $m->service_date,
                'lunch' => $m->lunch,
                'dinner' => $m->dinner,
                'accommodation' => $m->accommodation,
                'stay_from' => $from,
                'stay_to' => $to,
                'updated_at' => now()->toIso8601String(),
            ])
            ->values()
            ->all();

        $base = rtrim($url, '/').'/rest/v1/daily_requirements';

        $headers = array_filter([
            'apikey' => $key,
            'Authorization' => str_starts_with($key, 'eyJ') ? 'Bearer '.$key : null,
        ]);

        try {
            if ($rows !== []) {
                $saved = Http::connectTimeout(3)
                    ->timeout(8)
                    ->withHeaders($headers + ['Prefer' => 'resolution=merge-duplicates,return=minimal'])
                    ->post($base.'?on_conflict=participant_code,service_date', $rows);

                if (! $saved->successful()) {
                    logger()->warning('Supabase sync failed', ['status' => $saved->status(), 'body' => $saved->body()]);

                    return false;
                }
            }

            // Dates that are no longer in the range must not stay in Supabase
            $removed = Http::connectTimeout(3)
                ->timeout(8)
                ->withHeaders($headers)
                ->withQueryParameters([
                    'participant_code' => 'eq.'.$participant->participant_code,
                    'or' => "(service_date.lt.{$from},service_date.gt.{$to})",
                ])
                ->delete($base);

            if (! $removed->successful()) {
                logger()->warning('Supabase cleanup failed', ['status' => $removed->status(), 'body' => $removed->body()]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }
}
