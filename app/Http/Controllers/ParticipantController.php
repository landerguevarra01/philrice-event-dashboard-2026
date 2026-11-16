<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Services\ParticipantApi;
use App\Services\SupabaseSync;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

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
    public function open(Request $request)
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

        return redirect()->route('participants.show', $participant);
    }

    public function show(Participant $participant)
    {
        $requirements = $participant->requirements->keyBy('day');
        $attendedDates = $participant->attendances->map(fn ($a) => $a->attended_on->toDateString())->all();

        $start = Carbon::parse(config('event.start_date'));

        $days = collect(range(1, (int) config('event.days')))->map(function ($n) use ($start, $attendedDates) {
            $date = $start->copy()->addDays($n - 1);

            return [
                'number' => $n,
                'date' => $date,
                'is_today' => $date->isToday(),
                'attended' => in_array($date->toDateString(), $attendedDates),
            ];
        });

        $todayAttendance = $participant->attendances->first(fn ($a) => $a->attended_on->isToday());

        return view('participants.show', compact('participant', 'requirements', 'days', 'todayAttendance'));
    }

    public function checkIn(Participant $participant, ParticipantApi $api)
    {
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

    public function saveRequirements(Request $request, Participant $participant, SupabaseSync $supabase)
    {
        foreach ([1, 2, 3] as $day) {
            $participant->requirements()->updateOrCreate(
                ['day' => $day],
                [
                    'lunch' => $request->boolean("days.$day.lunch"),
                    'dinner' => $request->boolean("days.$day.dinner"),
                    'accommodation' => $request->boolean("days.$day.accommodation"),
                    'synced_at' => null,   // changed, so it needs uploading
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

    public function certificate(Participant $participant)
    {
        abort_unless($participant->checked_in_at, 403, 'This participant has not checked in.');

        $requirements = $participant->requirements->keyBy('day');

        $start = Carbon::parse(config('event.start_date'));
        $dates = collect(range(1, config('event.days')))
            ->mapWithKeys(fn ($day) => [$day => $start->copy()->addDays($day - 1)]);

        return Pdf::loadView('certificates.participant', compact('participant', 'requirements', 'dates'))
            ->setPaper('a4', 'landscape')
            ->stream("certificate-{$participant->participant_code}.pdf");
    }
}
