<?php

namespace App\Http\Controllers;

// use App\Models\Attendance;
// use App\Models\DayRequirement;
use App\Services\PendingSync;
use App\Services\SignatoryStore;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
// use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request, PendingSync $sync, SignatoryStore $signatories)
    {
        if ($request->header('X-Inertia')) {
            return Inertia::location(route('dashboard'));
        }

        return view('dashboard', [
            'pending' => $sync->counts(),
            'registration' => $this->registrationLink(),
            'dayCount' => (int) config('event.days'),
            'signatory' => $signatories->get(),
        ]);
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

        $writer = new Writer(new ImageRenderer(new RendererStyle(320, 2), new SvgImageBackEnd));
        $svg = preg_replace('/^<\?xml[^>]*\?>\s*/', '', $writer->writeString($url));

        return [
            'url' => $url,
            'qr' => $svg,
            'label' => Str::limit(preg_replace('#^https?://#', '', $url), 30),
        ];
    }
}
