<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Participant;
use App\Services\AttendanceApi;
use App\Services\ParticipantApi;
use App\Services\SignatoryStore;
use App\Services\SupabaseSync;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ParticipantController extends Controller
{
    private const MAX_DAYS = 7;

    // Called by the dashboard search box (JSON)
    public function search(Request $request, ParticipantApi $api): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json(['results' => [], 'offline' => false]);
        }

        try {
            $results = $api->search($term);
            $this->cacheResults($results);   // remember them for offline use
            $offline = false;
        } catch (\Throwable $e) {
            $results = $this->searchLocal($term);
            $offline = true;
        }

        $checkedIn = Participant::whereIn('participant_code', collect($results)->pluck('code'))
            ->whereHas('attendances', fn ($q) => $q->whereDate('attended_on', today()))
            ->pluck('participant_code')
            ->all();

        $results = collect($results)
            ->map(fn ($r) => Arr::only($r, ['code', 'name', 'organization'])
                + ['checked_in' => in_array($r['code'], $checkedIn)])
            ->all();

        return response()->json(['results' => $results, 'offline' => $offline]);
    }

    private function cacheResults(array $results): void
    {
        foreach ($results as $r) {
            Participant::remember($r);
        }
    }

    private function searchLocal(string $term): array
    {
        $query = Participant::query();

        foreach (preg_split('/\s+/', $term) as $word) {
            $query->search($word);   // each word must match, in any order
        }

        return $query->orderBy('name')->limit(20)->get()->map(fn ($p) => [
            'code' => $p->participant_code,
            'name' => $p->name,
            'organization' => $p->organization,
        ])->all();
    }

    // Save (or refresh) the selected participant locally, then open the profile
    public function open(Request $request, AttendanceApi $attendance)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
        ]);

        $participant = Participant::updateOrCreate(
            ['participant_code' => $data['code']],
            ['name' => $data['name'], 'organization' => $data['organization'] ?? null]
        );

        $this->importAttendance($participant, $attendance);

        return redirect()->route('participants.show', $participant);
    }

    /**
     * People the registration system lists as attended can print their certificate here,
     * even if they were checked in on another device.
     */
    private function importAttendance(Participant $participant, AttendanceApi $attendance): void
    {
        $record = collect($attendance->get()['records'])->firstWhere('code', $participant->participant_code);

        if (! $record) {
            return;
        }

        $first = null;

        foreach ($record['days'] as $day) {
            if ($day['date'] === null) {
                continue;   // did not attend that day
            }

            if ($day['at'] > 0) {
                $when = Carbon::createFromTimestamp($day['at'], config('app.timezone'));

                $exists = $participant->attendances()->whereDate('attended_on', $when->toDateString())->exists();

                if (! $exists) {
                    $participant->attendances()->create([
                        'attended_on' => $when->toDateString(),
                        'checked_in_at' => $when,
                        'synced_at' => now(),   // already in the registration system, nothing to upload
                    ]);
                }
            } else {
                $when = now();   // attended, but the time could not be read
            }

            $first = ($first === null || $when->lessThan($first)) ? $when : $first;
        }

        if ($first !== null && $participant->checked_in_at === null) {
            $participant->update(['checked_in_at' => $first]);
        }
    }

    public function show(Participant $participant, AttendanceApi $attendance)
    {
        $attended = $this->attendedDates($participant, $attendance);
        $range = $this->stayRange($participant, $attended);

        $attendedKeys = $attended->map(fn ($d) => $d->toDateString())->all();
        $meals = $participant->mealDays->keyBy('service_date');

        $days = $range === null
            ? collect()
            : $this->rangeDates($range)->map(fn ($date) => [
                'date' => $date,
                'key' => $date->toDateString(),
                'attended' => in_array($date->toDateString(), $attendedKeys, true),
                'meal' => $meals->get($date->toDateString()),
            ]);

        $todayAttendance = $participant->attendances->first(fn ($a) => $a->attended_on->isToday());

        return view('participants.show', [
            'participant' => $participant,
            'days' => $days,
            'range' => $range,
            'rangeSaved' => $participant->stay_from !== null && $participant->stay_to !== null,
            'maxDays' => self::MAX_DAYS,
            'todayAttendance' => $todayAttendance,
            'issued' => $range === null ? null : $range['to']->format('F j, Y'),
        ]);
    }

    public function saveRequirements(Request $request, Participant $participant, SupabaseSync $supabase, AttendanceApi $attendance): RedirectResponse
    {
        $range = $this->stayRange($participant, $this->attendedDates($participant, $attendance));

        if ($range === null) {
            return back()->with('error', 'Select the first and last day first.');
        }

        // Keep the dates that were on screen (they may have come from attendance)
        $participant->update([
            'stay_from' => $range['from']->toDateString(),
            'stay_to' => $range['to']->toDateString(),
        ]);

        foreach ($this->rangeDates($range) as $date) {
            $key = $date->toDateString();

            $participant->mealDays()->updateOrCreate(
                ['service_date' => $key],
                [
                    'lunch' => $request->boolean("days.$key.lunch"),
                    'dinner' => $request->boolean("days.$key.dinner"),
                    'accommodation' => $request->boolean("days.$key.accommodation"),
                    'synced_at' => null,
                ]
            );
        }

        $participant->load('mealDays');

        if ($supabase->syncRequirements($participant)) {
            $participant->mealDays()->update(['synced_at' => now()]);

            return back()->with('status', 'Requirements saved.');
        }

        return back()->with('status', 'Saved on this device. It will upload to Supabase automatically when the connection returns.');
    }

    public function certificate(Participant $participant, AttendanceApi $attendance, SignatoryStore $signatory)
    {
        ini_set('memory_limit', '512M');   // PDF generation can need more than the 128M default

        $range = $this->stayRange($participant, $this->attendedDates($participant, $attendance));

        abort_unless($range !== null, 403, 'Select the first and last day for this participant first.');

        return Pdf::loadView('certificates.participant', [
            'participant' => $participant,
            'meals' => $participant->mealDays->keyBy('service_date'),
            'dates' => $this->rangeDates($range),
            'attendedText' => $this->rangeText($range['from'], $range['to']),
            'issued' => $range['to']->format('F j, Y'),   // the last selected day
            'signatory' => $signatory->get(),
        ])
            ->setPaper('a4', 'landscape')
            ->stream("certificate-{$participant->participant_code}.pdf");
    }

    public function checkIn(Participant $participant, ParticipantApi $api, AttendanceApi $attendance)
    {
        $attendance->forget();   // so the profile shows today's attendance right away
        if ($participant->checkedInToday()) {
            return back()->with('status', 'Already checked in today.');
        }

        $result = $api->checkIn($participant->participant_code);
        $now = now();

        // A real rejection (invalid QR code, etc.): save nothing
        if (! $result['ok'] && ! $result['already'] && ! $result['offline']) {
            return back()->with('error', $result['message']);
        }

        $synced = $result['ok'] || $result['already'];

        $participant->attendances()->firstOrCreate(
            ['attended_on' => $now->toDateString()],
            ['checked_in_at' => $now, 'synced_at' => $synced ? $now : null]
        );

        $participant->update(['checked_in_at' => $participant->checked_in_at ?? $now]);

        if (! $synced) {
            return back()->with('status', 'Checked in offline. It will be sent to the registration system when the connection returns.');
        }

        return back()->with('status', $result['already']
            ? 'Already logged in the registration system. Marked as checked in.'
            : $result['message']);
    }

    /**
     * The days this person attended: the registration system's records plus any check-in made on this device.
     *
     * @return Collection<int, CarbonImmutable>
     */
    private function attendedDates(Participant $participant, AttendanceApi $attendance): Collection
    {
        $dates = collect();
        $record = collect($attendance->get()['records'])->firstWhere('code', $participant->participant_code);

        foreach ($record['days'] ?? [] as $day) {
            if (($day['at'] ?? 0) > 0) {
                $dates->push(CarbonImmutable::createFromTimestamp($day['at'], config('app.timezone'))->startOfDay());
            }
        }

        foreach ($this->localAttendances($participant, $record) as $a) {
            $dates->push(CarbonImmutable::parse($a->attended_on)->startOfDay());
        }

        return $dates
            ->unique(fn ($d) => $d->toDateString())
            ->sortBy(fn ($d) => $d->getTimestamp())
            ->values();
    }

    /**
     * Local check-ins that count. When the registration system has a record for this person, it is
     * the truth, so only check-ins that have not been uploaded yet are added to it. Without a record,
     * every local check-in counts.
     *
     * @param  array<string, mixed>|null  $record
     * @return Collection<int, Attendance>
     */
    private function localAttendances(Participant $participant, ?array $record): Collection
    {
        return $record === null
            ? $participant->attendances
            : $participant->attendances->whereNull('synced_at');
    }

    /**
     * The dates on the certificate and in the meals list: the range staff selected,
     * or else the first to the last day this person attended.
     *
     * @param  Collection<int, CarbonImmutable>  $attended
     * @return array{from: CarbonImmutable, to: CarbonImmutable}|null
     */
    private function stayRange(Participant $participant, Collection $attended): ?array
    {
        if ($participant->stay_from !== null && $participant->stay_to !== null) {
            return [
                'from' => CarbonImmutable::parse($participant->stay_from)->startOfDay(),
                'to' => CarbonImmutable::parse($participant->stay_to)->startOfDay(),
            ];
        }

        if ($attended->isEmpty()) {
            return null;
        }

        $from = $attended->first();
        $to = $attended->last();
        $limit = $from->addDays(self::MAX_DAYS - 1);

        return ['from' => $from, 'to' => $to->greaterThan($limit) ? $limit : $to];
    }

    /**
     * @param  array{from: CarbonImmutable, to: CarbonImmutable}  $range
     * @return Collection<int, CarbonImmutable>
     */
    private function rangeDates(array $range): Collection
    {
        $dates = collect();

        for ($day = $range['from']; $day->lessThanOrEqualTo($range['to']) && $dates->count() < self::MAX_DAYS; $day = $day->addDay()) {
            $dates->push($day);
        }

        return $dates;
    }

    private function rangeText(CarbonImmutable $from, CarbonImmutable $to): string
    {
        if ($from->isSameDay($to)) {
            return $from->format('F j, Y');
        }

        if ($from->format('Y-m') === $to->format('Y-m')) {
            return $from->format('F j').' to '.$to->format('j, Y');
        }

        return $from->year === $to->year
            ? $from->format('F j').' to '.$to->format('F j, Y')
            : $from->format('F j, Y').' to '.$to->format('F j, Y');
    }

    public function saveDates(Request $request, Participant $participant, SupabaseSync $supabase): RedirectResponse
    {
        $data = $request->validate([
            'stay_from' => ['required', 'date_format:Y-m-d'],
            'stay_to' => ['required', 'date_format:Y-m-d', 'after_or_equal:stay_from'],
        ], [
            'stay_to.after_or_equal' => 'The last day cannot be before the first day.',
        ], [
            'stay_from' => 'first day',
            'stay_to' => 'last day',
        ]);

        $from = CarbonImmutable::parse($data['stay_from']);
        $to = CarbonImmutable::parse($data['stay_to']);

        if ((int) round($from->diffInDays($to)) > self::MAX_DAYS - 1) {
            return back()->withInput()->with('error', 'Choose at most '.self::MAX_DAYS.' days.');
        }

        $participant->update(['stay_from' => $data['stay_from'], 'stay_to' => $data['stay_to']]);

        // Send the new dates to Supabase and remove dates that are no longer in the range there
        $participant->load('mealDays');
        $participant->mealDays()->update(['synced_at' => $supabase->syncRequirements($participant) ? now() : null]);

        return back()->with('status', 'Dates saved.');
    }
}
