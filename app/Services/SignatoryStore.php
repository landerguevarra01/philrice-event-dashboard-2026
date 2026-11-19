<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SignatoryStore
{
    private const CACHE_KEY = 'signatory:last';

    /**
     * Live from Supabase. Falls back to the last saved copy, then the defaults,
     * so certificates still print without internet.
     *
     * @return array{name: string, role: string}
     */
    public function get(): array
    {
        $live = $this->fetch();

        if ($live !== null) {
            Cache::forever(self::CACHE_KEY, $live);

            return $live;
        }

        return $this->cached();
    }

    /**
     * No network: the last saved copy, or the defaults.
     *
     * @return array{name: string, role: string}
     */
    public function cached(): array
    {
        $last = Cache::get(self::CACHE_KEY);

        if (is_array($last) && isset($last['name'], $last['role'])) {
            return ['name' => (string) $last['name'], 'role' => (string) $last['role']];
        }

        return [
            'name' => (string) config('event.signatory.name'),
            'role' => (string) config('event.signatory.role'),
        ];
    }

    /**
     * Changes the existing row only. It never adds one.
     *
     * @return array{ok: bool, message: string}
     */
    public function update(string $name, string $role): array
    {
        $url = (string) config('services.supabase.url');
        $key = (string) config('services.supabase.key');

        if ($url === '' || $key === '') {
            return ['ok' => false, 'message' => 'Supabase is not set up. The signatory was not changed.'];
        }

        try {
            $response = Http::connectTimeout(3)
                ->timeout(8)
                ->withHeaders($this->headers($key) + ['Prefer' => 'return=representation'])
                ->patch(rtrim($url, '/').'/rest/v1/signatory?id=eq.1', [
                    'name' => $name,
                    'role' => $role,
                    'updated_at' => now()->toIso8601String(),
                ]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Could not reach Supabase. The signatory was not changed.'];
        }

        if (! $response->successful()) {
            logger()->warning('Signatory update failed', ['status' => $response->status(), 'body' => $response->body()]);

            return ['ok' => false, 'message' => 'Supabase refused the update. The signatory was not changed.'];
        }

        // An update only touches rows that exist, so an empty answer means row 1 is missing
        if ($response->json() === [] || $response->json() === null) {
            return ['ok' => false, 'message' => 'The signatory record was not found in Supabase. Run the SQL setup first.'];
        }

        Cache::forever(self::CACHE_KEY, ['name' => $name, 'role' => $role]);

        return ['ok' => true, 'message' => 'Signatory updated.'];
    }

    /** @return array{name: string, role: string}|null */
    private function fetch(): ?array
    {
        $url = (string) config('services.supabase.url');
        $key = (string) config('services.supabase.key');

        if ($url === '' || $key === '') {
            return null;
        }

        try {
            $response = Http::connectTimeout(3)
                ->timeout(6)
                ->withHeaders($this->headers($key))
                ->get(rtrim($url, '/').'/rest/v1/signatory', ['select' => 'name,role', 'id' => 'eq.1']);
        } catch (\Throwable $e) {
            return null;
        }

        $row = $response->successful() ? $response->json(0) : null;

        if (! is_array($row) || ! isset($row['name'], $row['role'])) {
            return null;
        }

        return ['name' => (string) $row['name'], 'role' => (string) $row['role']];
    }

    /** @return array<string, string> */
    private function headers(string $key): array
    {
        return array_filter([
            'apikey' => $key,
            'Authorization' => str_starts_with($key, 'eyJ') ? 'Bearer '.$key : null,
        ]);
    }
}
