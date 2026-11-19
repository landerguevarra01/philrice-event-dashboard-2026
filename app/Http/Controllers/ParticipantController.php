<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Services\AttendanceApi;
use App\Services\ParticipantApi;
use App\Services\SignatoryStore;
use App\Services\SupabaseSync;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ParticipantController extends Controller
{
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
        $requirements = $participant->requirements->keyBy('day');
        $attendedDays = $this->attendedDays($participant, $attendance);

        $start = Carbon::parse(config('event.start_date'));

        $days = collect(range(1, (int) config('event.days')))->map(function ($n) use ($start, $attendedDays) {
            $date = $start->copy()->addDays($n - 1);

            return [
                'number' => $n,
                'date' => $date,
                'is_today' => $date->isToday(),
                'attended' => in_array($n, $attendedDays, true),
            ];
        });

        $todayAttendance = $participant->attendances->first(fn ($a) => $a->attended_on->isToday());

        return view('participants.show', compact('participant', 'requirements', 'days', 'todayAttendance'));
    }

    /**
     * Event day numbers (1, 2, 3) this person attended: the registration system's records,
     * plus any check-in made on this device on an event day.
     *
     * @return array<int, int>
     */
    private function attendedDays(Participant $participant, AttendanceApi $attendance): array
    {
        $days = [];

        $record = collect($attendance->get()['records'])->firstWhere('code', $participant->participant_code);

        foreach ($record['days'] ?? [] as $index => $day) {
            if (($day['date'] ?? null) !== null) {
                $days[] = $index + 1;
            }
        }

        $start = CarbonImmutable::parse(config('event.start_date'))->startOfDay();
        $eventDays = (int) config('event.days');

        foreach ($participant->attendances as $a) {
            $n = (int) round($start->diffInDays(CarbonImmutable::parse($a->attended_on)->startOfDay(), false)) + 1;

            if ($n >= 1 && $n <= $eventDays) {
                $days[] = $n;
            }
        }

        return array_values(array_unique($days));
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

    public function saveRequirements(Request $request, Participant $participant, SupabaseSync $supabase, AttendanceApi $attendance)
    {
        $allowed = $this->attendedDays($participant, $attendance);

        if ($allowed === []) {
            return back()->with('error', 'Meals and accommodation can only be saved for days this participant attended.');
        }

        // Only the days they attended. Locked days are left exactly as they are.
        foreach ($allowed as $day) {
            $participant->requirements()->updateOrCreate(
                ['day' => $day],
                [
                    'lunch' => $request->boolean("days.$day.lunch"),
                    'dinner' => $request->boolean("days.$day.dinner"),
                    'accommodation' => $request->boolean("days.$day.accommodation"),
                    'synced_at' => null,
                ]
            );
        }

        $participant->load('requirements');

        if ($supabase->syncRequirements($participant)) {
            $participant->requirements()->update(['synced_at' => now()]);

            return back()->with('status', 'Requirements saved.');
        }

        return back()->with('status', 'Saved on this device. It will upload to Supabase automatically when the connection returns.');
    }

    public function certificate(Participant $participant, AttendanceApi $attendance, SignatoryStore $signatory)
    {
        ini_set('memory_limit', '512M');   // PDF generation can need more than the 128M default
        $attended = $this->attendedDates($participant, $attendance);

        abort_unless($attended->isNotEmpty(), 403, 'This participant has not attended any day yet.');

        $requirements = $participant->requirements->keyBy('day');

        // Event days, for the Nov-23 / Nov-24 / Nov-25 columns of the meals table
        $start = Carbon::parse(config('event.start_date'));
        $dates = collect(range(1, (int) config('event.days')))
            ->mapWithKeys(fn ($day) => [$day => $start->copy()->addDays($day - 1)]);

        return Pdf::loadView('certificates.participant', [
            'participant' => $participant,
            'requirements' => $requirements,
            'dates' => $dates,
            'attendedText' => $this->datesText($attended),
            'issued' => $attended->last()->format('F j, Y'),
            'signatory' => $signatory->get(),
        ])
            ->setPaper('a4', 'landscape')
            ->stream("certificate-{$participant->participant_code}.pdf");
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

        foreach ($participant->attendances as $a) {
            $dates->push(CarbonImmutable::parse($a->attended_on)->startOfDay());
        }

        return $dates
            ->unique(fn ($d) => $d->toDateString())
            ->sortBy(fn ($d) => $d->getTimestamp())
            ->values();
    }

    /**
     * "November 16, 2026" / "November 23 and 25, 2026" / "November 23 to 25, 2026"
     *
     * @param  Collection<int, CarbonImmutable>  $dates
     */
    private function datesText(Collection $dates): string
    {
        $first = $dates->first();
        $last = $dates->last();

        if ($dates->count() === 1) {
            return $first->format('F j, Y');
        }

        $sameMonth = $first->format('Y-m') === $last->format('Y-m');
        $sameYear = $first->year === $last->year;
        $consecutive = (int) $first->diffInDays($last) === $dates->count() - 1;

        // Three or more days in a row become a range
        if ($consecutive && $dates->count() >= 3) {
            if ($sameMonth) {
                return $first->format('F j').' to '.$last->format('j, Y');
            }

            return $sameYear
                ? $first->format('F j').' to '.$last->format('F j, Y')
                : $first->format('F j, Y').' to '.$last->format('F j, Y');
        }

        $labels = $dates->map(fn ($d) => $sameMonth ? $d->format('j') : ($sameYear ? $d->format('F j') : $d->format('F j, Y')));

        $joined = $labels->count() === 2
            ? $labels->implode(' and ')
            : $labels->slice(0, -1)->implode(', ').' and '.$labels->last();

        return ($sameMonth ? $first->format('F').' ' : '').$joined.($sameYear ? ', '.$last->format('Y') : '');
    }
}
