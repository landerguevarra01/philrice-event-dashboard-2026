<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\DayRequirement;
use App\Services\PendingSync;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request, PendingSync $sync)
    {
        if ($request->header('X-Inertia')) {
            return Inertia::location(route('dashboard'));
        }

        $start = Carbon::parse(config('event.start_date'));

        $attendanceByDate = Attendance::selectRaw('DATE(attended_on) as d, COUNT(*) as total')
            ->groupBy('d')
            ->pluck('total', 'd');

        $requirementTotals = DayRequirement::selectRaw(
                'day, SUM(lunch) as lunch, SUM(dinner) as dinner, SUM(accommodation) as accommodation'
            )
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $days = collect(range(1, (int) config('event.days')))->map(function ($n) use ($start, $attendanceByDate, $requirementTotals) {
            $date = $start->copy()->addDays($n - 1);
            $req = $requirementTotals->get($n);

            return [
                'number'        => $n,
                'date'          => $date,
                'is_today'      => $date->isToday(),
                'checked_in'    => (int) ($attendanceByDate[$date->toDateString()] ?? 0),
                'lunch'         => (int) ($req?->lunch ?? 0),
                'dinner'        => (int) ($req?->dinner ?? 0),
                'accommodation' => (int) ($req?->accommodation ?? 0),
            ];
        });

        $stats = [
            'attended'      => Attendance::distinct()->count('participant_id'),
            'today'         => Attendance::whereDate('attended_on', today())->count(),
            'accommodation' => DayRequirement::where('accommodation', true)->count(),
        ];

        $recent = Attendance::with('participant')->latest('checked_in_at')->take(8)->get();
        $pending = $sync->counts();

        $registration = $this->registrationLink();


        return view('dashboard', compact('days', 'stats', 'recent', 'pending', 'registration'));
    }

    public function sync(PendingSync $sync)
    {
        $sync->run();
        $left = $sync->counts();
        $remaining = $left['checkins'] + $left['meals'];

        return back()->with(
            $remaining ? 'error' : 'status',
            $remaining ? "$remaining item(s) are still waiting. Check the connection and try again." : 'Everything is uploaded.'
        );
    }

    private function registrationLink(): array
    {
        $url = trim((string) config('event.registration_url'));

        // Only web links are accepted
        if (! str_starts_with($url, 'https://') && ! str_starts_with($url, 'http://')) {
            return ['url' => null, 'qr' => null, 'label' => null];
        }

        $writer = new Writer(new ImageRenderer(new RendererStyle(320, 2), new SvgImageBackEnd()));
        $svg = preg_replace('/^<\?xml[^>]*\?>\s*/', '', $writer->writeString($url));

        return [
            'url'   => $url,
            'qr'    => $svg,
            'label' => Str::limit(preg_replace('#^https?://#', '', $url), 30),
        ];
    }
}