<x-app-layout>
    <style>
        .pf-hero { background: var(--paddy); color: #fff; padding: 1.5rem 0 2rem; }
        .pf-hero-inner { display: flex; flex-wrap: wrap; gap: 1.5rem 2rem; justify-content: space-between; align-items: flex-end; }
        .pf-back { display: inline-flex; align-items: center; gap: .35rem; margin-bottom: 1rem; color: #BFD3C6; text-decoration: none; font-size: .9375rem; }
        .pf-back:hover { color: #fff; text-decoration: underline; }
        .pf-back svg { width: 1rem; height: 1rem; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .pf-name { margin: 0; font-size: clamp(1.75rem, 4vw, 2.75rem); line-height: 1.1; font-weight: 700; letter-spacing: -.02em; overflow-wrap: anywhere; }
        .pf-org { margin: .5rem 0 0; font-size: 1.125rem; color: #E3EDE6; }
        .pf-id { margin: .15rem 0 0; color: #A9C2B1; font-variant-numeric: tabular-nums; letter-spacing: .02em; overflow-wrap: anywhere; }

        .pf-days { list-style: none; display: flex; flex-wrap: wrap; gap: .5rem; margin: 1.25rem 0 0; padding: 0; }
        .pf-days li { display: flex; align-items: center; gap: .5rem; border: 1px solid #3B6A52; border-radius: 999px; padding: .3rem .8rem; font-size: .875rem; color: #BFD3C6; }
        .pf-mark { width: .6rem; height: .6rem; border-radius: 50%; border: 2px solid #7FA58D; }
        .pf-days li.is-here { background: var(--paddy-soft); border-color: var(--grain); color: #fff; }
        .pf-days li.is-here .pf-mark { background: var(--grain); border-color: var(--grain); }

        .pf-action { width: 100%; }
        .pf-checkin { width: 100%; background: var(--grain); color: #2E2300; border: 0; border-radius: 12px; padding: 1rem 1.75rem; font: inherit; font-size: 1.125rem; font-weight: 700; cursor: pointer; }
        .pf-checkin:hover { background: #F0C23A; }
        .pf-checkin:disabled { opacity: .65; cursor: default; }
        .pf-status { display: flex; align-items: center; gap: .75rem; }
        .pf-status .tick { flex: none; display: grid; place-items: center; width: 2.5rem; height: 2.5rem; border-radius: 50%; background: var(--leaf); }
        .pf-status .tick svg { width: 1.25rem; height: 1.25rem; stroke: #fff; fill: none; stroke-width: 3; stroke-linecap: round; stroke-linejoin: round; }
        .pf-status b { display: block; font-size: 1.125rem; }
        .pf-status span { display: block; color: #BFD3C6; font-size: .9375rem; }
        .pf-status .pf-warn { color: #F6C778; }

        .pf-body { padding-top: 2rem; padding-bottom: 3rem; }
        .pf-h2 { margin: 0 0 .25rem; font-size: 1.125rem; font-weight: 650; }
        .pf-lede { margin: 0 0 1rem; color: var(--muted); font-size: .9375rem; }
        .pf-note, .pf-hint { margin: 1rem 0 0; padding: .7rem 1rem; border-radius: 10px; background: var(--grain-tint); border: 1px solid #EFD98A; color: #5C4300; font-size: .9375rem; }

        .pf-card { background: var(--paper); border: 1px solid var(--line); border-radius: 12px; padding: 1.25rem 1.5rem 1.5rem; margin-bottom: 2rem; }
        .pf-card .pf-lede { margin-bottom: 0; }
        .pf-dates { display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end; margin-top: 1rem; }
        .pf-field label { display: block; margin: 0 0 .35rem; font-size: .9375rem; font-weight: 600; }
        .pf-field input[type=date] { border: 1px solid #B9C6BB; border-radius: 10px; padding: .7rem .9rem; font: inherit; background: #fff; color: var(--ink); }
        .pf-err { margin: .35rem 0 0; font-size: .875rem; color: var(--danger); }

        .pf-cols { display: grid; grid-template-columns: repeat(auto-fit, minmax(14rem, 1fr)); gap: 1rem; }
        .pf-col { background: var(--paper); border: 1px solid var(--line); border-radius: 12px; padding: 1rem; }
        .pf-colhead { display: flex; align-items: baseline; flex-wrap: wrap; gap: .25rem .6rem; margin: 0 0 .85rem; font-size: 1.0625rem; font-weight: 650; }
        .pf-tag { font-size: .75rem; font-weight: 600; background: #E1F2E2; color: #1E5B22; border-radius: 999px; padding: .05rem .5rem; }

        .pf-opt { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .9rem 1rem; background: #fff;
                  border: 1px solid var(--line); border-radius: 10px; font-size: 1.0625rem; cursor: pointer; }
        .pf-opt + .pf-opt { margin-top: .6rem; }
        .pf-opt:hover { border-color: #B3C4B4; }
        .pf-opt:has(input:checked) { background: #E8F3E8; border-color: var(--leaf); }
        .pf-opt input { width: 1.5rem; height: 1.5rem; margin: 0; accent-color: var(--leaf); cursor: pointer; }

        .pf-save { margin-top: 1.25rem; }
        .pf-btn { display: inline-block; background: var(--paddy); color: #fff; border: 0; border-radius: 10px; padding: .85rem 1.4rem; font: inherit; font-weight: 600; text-decoration: none; cursor: pointer; }
        .pf-btn:hover { background: var(--paddy-soft); }
        .pf-btn:disabled { opacity: .65; cursor: default; }
        .pf-btn.line { background: transparent; color: var(--paddy); border: 2px solid var(--paddy); padding: .75rem 1.3rem; }
        .pf-btn.line:hover { background: #E8F3E8; }

        .pf-cert { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-top: 2.25rem; padding-top: 1.5rem; border-top: 1px solid var(--line); }
        .pf-cert .pf-lede { margin: 0; }

        @media (min-width: 40rem) { .pf-action, .pf-checkin { width: auto; } }
    </style>

    <section class="pf-hero">
        <div class="ev-wrap pf-hero-inner">
            <div>
                <a href="{{ route('dashboard') }}" class="pf-back">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                    Back to dashboard
                </a>
                <h1 class="pf-name">{{ $participant->name }}</h1>
                <p class="pf-org">{{ $participant->organization ?: 'No organization' }}</p>
                <p class="pf-id">ID {{ $participant->participant_code }}</p>

                @if ($days->isNotEmpty())
                    <ul class="pf-days" aria-label="Attendance by date">
                        @foreach ($days as $d)
                            <li @class(['is-here' => $d['attended']])>
                                <span class="pf-mark" aria-hidden="true"></span>
                                <span>{{ $d['date']->format('D, M j') }}</span>
                                <span class="ev-sr">{{ $d['attended'] ? 'attended' : 'not attended' }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="pf-action">
                @if ($todayAttendance)
                    <div class="pf-status">
                        <div class="tick"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></div>
                        <div>
                            <b>Checked in today</b>
                            <span>{{ $todayAttendance->checked_in_at->format('g:i A') }}</span>
                            @unless ($todayAttendance->synced_at)
                                <span class="pf-warn">Not uploaded to the registration system yet</span>
                            @endunless
                        </div>
                    </div>
                @else
                    <form method="POST" action="{{ route('participants.checkin', $participant) }}" data-once>
                        @csrf
                        <button class="pf-checkin" data-busy="Checking in...">Check in for today</button>
                    </form>
                @endif
            </div>
        </div>
    </section>

    <div class="ev-wrap pf-body">

        <section class="pf-card">
            <h2 class="pf-h2">Dates on the certificate</h2>
            <p class="pf-lede">Pick the first and last day (up to {{ $maxDays }} days). The certificate lists these dates and is issued on the last day.</p>

            <form method="POST" action="{{ route('participants.dates', $participant) }}" class="pf-dates" data-once>
                @csrf
                <div class="pf-field">
                    <label for="stay_from">First day</label>
                    <input id="stay_from" name="stay_from" type="date" required value="{{ old('stay_from', $range ? $range['from']->format('Y-m-d') : '') }}">
                    @error('stay_from')<p class="pf-err">{{ $message }}</p>@enderror
                </div>
                <div class="pf-field">
                    <label for="stay_to">Last day</label>
                    <input id="stay_to" name="stay_to" type="date" required value="{{ old('stay_to', $range ? $range['to']->format('Y-m-d') : '') }}">
                    @error('stay_to')<p class="pf-err">{{ $message }}</p>@enderror
                </div>
                <button class="pf-btn" data-busy="Saving...">Save dates</button>
            </form>

            @if ($range && ! $rangeSaved)
                <p class="pf-hint">These dates come from the days this person attended. Save them to keep them, or pick different ones.</p>
            @endif
        </section>

        <form method="POST" action="{{ route('participants.requirements', $participant) }}" data-once>
            @csrf
            <h2 class="pf-h2">Meals and accommodation</h2>
            <p class="pf-lede">Tick what this participant receives on each date.</p>

            @if ($days->isEmpty())
                <p class="pf-note">Select the first and last day above to list the dates.</p>
            @else
                <div class="pf-cols">
                    @foreach ($days as $d)
                        <div class="pf-col" role="group" aria-labelledby="d-{{ $d['key'] }}">
                            <h3 class="pf-colhead" id="d-{{ $d['key'] }}">
                                {{ $d['date']->format('D, M j') }}
                                @if ($d['attended'])<span class="pf-tag">Attended</span>@endif
                            </h3>

                            @foreach (['lunch' => 'Lunch', 'dinner' => 'Dinner', 'accommodation' => 'Accommodation'] as $item => $label)
                                <label class="pf-opt">
                                    <span>{{ $label }}</span>
                                    <input type="checkbox" name="days[{{ $d['key'] }}][{{ $item }}]" value="1" @checked($d['meal']?->$item)>
                                </label>
                            @endforeach
                        </div>
                    @endforeach
                </div>

                <div class="pf-save">
                    <button class="pf-btn" data-busy="Saving...">Save requirements</button>
                </div>
            @endif
        </form>

        <section class="pf-cert">
            <div>
                <h2 class="pf-h2">Certificate</h2>
                <p class="pf-lede">
                    @if ($issued)
                        Opens as a PDF in a new tab. Issued date: {{ $issued }}.
                    @else
                        Select the dates above to print a certificate.
                    @endif
                </p>
            </div>

            @if ($issued)
                <a href="{{ route('participants.certificate', $participant) }}" target="_blank" rel="noopener" class="pf-btn line">Print certificate</a>
            @endif
        </section>
    </div>

    <script>
        // One click only: stops a double tap from sending the request twice
        document.querySelectorAll('form[data-once]').forEach(form => {
            form.addEventListener('submit', () => {
                const btn = form.querySelector('button');
                if (btn) { btn.disabled = true; btn.textContent = btn.dataset.busy || 'Working...'; }
            });
        });
        // Coming back with the browser's Back button should not leave a disabled button
        window.addEventListener('pageshow', e => { if (e.persisted) location.reload(); });

        // Keep the last day within the allowed number of days after the first day
        (() => {
            const from = document.getElementById('stay_from');
            const to = document.getElementById('stay_to');
            const maxDays = @json($maxDays);
            const iso = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;

            function limit() {
                if (!from.value) { to.removeAttribute('min'); to.removeAttribute('max'); return; }
                const end = new Date(from.value + 'T00:00:00');
                end.setDate(end.getDate() + maxDays - 1);
                to.min = from.value;
                to.max = iso(end);
            }

            from.addEventListener('change', limit);
            limit();
        })();
    </script>
</x-app-layout>