<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class ParticipantApi
{
    // Fields your API accepts for searching
    private const FIELDS = ['first_name', 'last_name'];

    public function search(string $term): array
    {
        $words = collect(preg_split('/\s+/u', trim($term)))
            ->filter()->unique()->take(3)->values();

        if ($words->isEmpty()) {
            return [];
        }

        // One request per word, per field, per letter-case variant ("for" and "For")
        $bodies = [];
        foreach ($words as $word) {
            $variants = array_unique([$word, mb_convert_case($word, MB_CASE_TITLE)]);
            foreach (self::FIELDS as $field) {
                foreach ($variants as $variant) {
                    $bodies[] = [$field => $variant];
                }
            }
        }

        $url = config('services.participants.url');
        $key = config('services.participants.key');

        // Sent at the same time, so it is no slower than a single request
        $responses = Http::pool(fn ($pool) => collect($bodies)->map(
            fn ($body, $i) => $pool->as("r{$i}")
                ->acceptJson()
                ->connectTimeout(3)
                ->timeout(8)
                ->withHeaders(['X-API-KEY' => $key])
                ->post($url, $body)
        )->all());

        $rows = [];
        $anyOk = false;
        $hadError = false;

        foreach ($responses as $response) {
            if (! $response instanceof Response) {   // connection error or timeout
                $hadError = true;
                continue;
            }

            if ($response->successful()) {
                $anyOk = true;
                array_push($rows, ...$this->extractRows($response->json()));
            } elseif (! in_array($response->status(), [400, 404])) {  // 401, 5xx, etc.
                $hadError = true;
            }
        }

        // Every request failed: let the dashboard show "search unavailable"
        if (! $anyOk && $hadError) {
            throw new \RuntimeException('Participant API request failed.');
        }

        return collect($rows)
            ->filter(fn ($row) => is_array($row))
            ->map(fn ($row) => $this->normalize($row))
            ->filter(fn ($row) => $row['code'] !== '' && $row['name'] !== '')
            ->unique('code')
            // keep only people whose full name contains every word typed
            ->filter(fn ($p) => $words->every(
                fn ($w) => str_contains(mb_strtolower($p['name']), mb_strtolower($w))
            ))
            ->values()
            ->all();
    }

    public function checkIn(string $qrdata): array
    {
        try {
            $response = Http::acceptJson()
                ->connectTimeout(3)
                ->timeout(8)
                ->withHeaders(['X-API-KEY' => config('services.participants.key')])
                ->post(config('services.participants.checkin_url'), ['qrcode' => $qrdata]);
        } catch (ConnectionException $e) {
            return ['ok' => false, 'already' => false, 'offline' => true,
                    'message' => 'No connection to the registration system.', 'data' => null];
        }

        $message = $response->json('message') ?: 'Check-in failed.';
        $ok = $response->successful() && $response->json('success') === true;

        return [
            'ok'      => $ok,
            'already' => ! $ok && str_contains(strtolower($message), 'already'),
            'offline' => $response->serverError(),   // 5xx: the server is down, try again later
            'message' => $message,
            'data'    => $response->json('data'),
        ];
    }

    private function extractRows($json): array
    {
        if (! is_array($json)) {
            return [];
        }

        foreach (['data', 'results', 'participants'] as $key) {
            if (isset($json[$key]) && is_array($json[$key])) {
                $json = $json[$key];
                break;
            }
        }

        if (isset($json['id']) || isset($json['qrdata'])) {
            return [$json];
        }

        return $json;
    }

    private function clean($value, bool $capitalize = false): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return $capitalize ? ucfirst(strtolower($value)) : $value;
    }

    private function normalize(array $row): array
    {
        $name = collect([
            $row['firstName'] ?? '',
            $row['midName'] ?? '',
            $row['lastName'] ?? '',
            $row['extName'] ?? '',
        ])->map(fn ($part) => trim((string) $part))->filter()->implode(' ');

        $external = strtolower(trim((string) ($row['isPhilriceEmp'] ?? ''))) === 'external';

        $organization = $external
            ? ($row['affiliationName'] ?? null)
            : collect([$row['philriceName'] ?? null, $row['philriceStation'] ?? null])
                ->filter()->implode(' - ');

        return [
            'code'         => trim((string) ($row['qrdata'] ?? '')),
            'name'         => $name,
            'organization' => $organization ?: null,
            'sex'          => $this->clean($row['sex'] ?? null, true),
            'age_bracket'  => $this->clean($row['ageBracket'] ?? null),
            'region'       => $this->clean($row['affiliationRegion'] ?? null),
        ];
    }

    public function register(array $payload): array
    {
        try {
            $response = Http::acceptJson()
                ->asMultipart()   // matches the form-data body in Postman. If the API reports missing fields, try removing this line to send JSON.
                ->connectTimeout(3)
                ->timeout(15)
                ->withHeaders(['X-API-KEY' => config('services.participants.register_key')])
                ->post(config('services.participants.register_url'), $payload);
        } catch (ConnectionException $e) {
            return ['ok' => false, 'status' => 0, 'errors' => null, 'message' => 'Could not reach the registration system. Check the connection and try again.', 'data' => null];
        }

        $ok = $response->successful() && $response->json('success') === true;

        return [
            'ok'      => $ok,
            'status'  => $response->status(),
            'errors'  => $response->json('errors'),
            'message' => $response->json('message') ?: ($ok ? 'Participant registered.' : 'Registration failed.'),
            'data'    => $response->json('data'),
        ];
    }
}