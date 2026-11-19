<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AttendanceApi
{
    private const CACHE_KEY = 'attendance-records';

    private const LAST_GOOD_KEY = 'attendance-records:last';

    /**
     * @return array{records: array<int, array<string, mixed>>, updated_at: string|null, stale: bool, error: string|null}
     */
    public function get(bool $fresh = false): array
    {
        if (! $fresh) {
            $cached = Cache::get(self::CACHE_KEY);

            if (is_array($cached)) {
                return $cached;
            }
        }

        try {
            $records = $this->fetch();
        } catch (\Throwable $e) {
            logger()->warning('Attendance API failed: '.$e->getMessage());

            // Show the last good copy instead of an empty dashboard
            $last = Cache::get(self::LAST_GOOD_KEY);

            return [
                'records' => is_array($last) ? $last['records'] : [],
                'updated_at' => is_array($last) ? $last['updated_at'] : null,
                'stale' => true,
                'error' => 'Could not reach the attendance service.',
            ];
        }

        $payload = [
            'records' => $records,
            'updated_at' => now()->toIso8601String(),
            'stale' => false,
            'error' => null,
        ];

        Cache::put(self::CACHE_KEY, $payload, 20);   // protects the API when several screens poll
        Cache::forever(self::LAST_GOOD_KEY, $payload);

        return $payload;
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /** @return array<int, array<string, mixed>> */
    private function fetch(): array
    {
        $url = (string) config('services.participants.attendance_url');

        if ($url === '') {
            throw new \RuntimeException('PARTICIPANT_ATTENDANCE_URL is not set.');
        }

        $make = fn () => Http::acceptJson()
            ->connectTimeout(3)
            ->timeout(12)
            ->withHeaders(['X-API-KEY' => (string) config('services.participants.attendance_key')]);

        $response = $make()->get($url);

        // Some of your endpoints only accept POST, so fall back to it if GET is refused
        if ($response->status() === 405) {
            $response = $make()->post($url);
        }

        $json = $response->throw()->json();

        if (! is_array($json)) {
            return [];
        }

        $rows = isset($json['data']) && is_array($json['data']) ? $json['data'] : $json;
        $records = [];

        foreach ($rows as $key => $row) {
            if (is_array($row)) {
                $records[] = $this->normalize((string) ($row['qrdata'] ?? $key), $row);
            }
        }

        return $records;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function normalize(string $code, array $row): array
    {
        $days = [];

        foreach (range(1, (int) config('event.days')) as $n) {
            $days[] = $this->day($row['day'.$n] ?? null);
        }

        $attended = count(array_filter($days, fn (array $d) => $d['date'] !== null));
        $type = strtolower(trim((string) ($row['participantType'] ?? '')));
        $name = trim((string) ($row['name'] ?? ''));

        return [
            'code' => $code,
            'name' => $name !== '' ? $name : 'Unnamed',
            'type' => $type !== '' ? $type : 'other',
            'affiliation' => trim((string) ($row['affiliation'] ?? '')),
            'days' => $days,
            'count' => (int) ($row['attendance_count'] ?? $attended),
        ];
    }

    /** @return array{at: int, date: string|null, time: string|null} */
    private function day(mixed $value): array
    {
        $text = trim(is_scalar($value) ? (string) $value : '');

        if ($text === '') {
            return ['at' => 0, 'date' => null, 'time' => null];
        }

        try {
            $when = Carbon::createFromFormat('n/j/Y, g:i:s A', $text);
        } catch (\Throwable) {
            $when = null;
        }

        if (! $when) {
            return ['at' => 0, 'date' => $text, 'time' => null];   // unknown format: show it as it came
        }

        return [
            'at' => $when->getTimestamp(),
            'date' => $when->format('M j'),
            'time' => $when->format('g:i A'),
        ];
    }
}
