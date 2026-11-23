<x-app-layout>
    <style>
        .pf-hero {
            background: var(--paddy);
            color: #fff;
            padding: 1.5rem 0 2rem;
        }

        .pf-hero-inner {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem 2rem;
            justify-content: space-between;
            align-items: flex-end;
        }

        .pf-back {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            margin-bottom: 1rem;
            color: #BFD3C6;
            text-decoration: none;
            font-size: .9375rem;
        }

        .pf-back:hover {
            color: #fff;
            text-decoration: underline;
        }

        .pf-back svg {
            width: 1rem;
            height: 1rem;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .pf-name {
            margin: 0;
            font-size: clamp(1.75rem, 4vw, 2.75rem);
            line-height: 1.1;
            font-weight: 700;
            letter-spacing: -.02em;
            overflow-wrap: anywhere;
        }

        .pf-org {
            margin: .5rem 0 0;
            font-size: 1.125rem;
            color: #E3EDE6;
        }

        .pf-id {
            margin: .15rem 0 0;
            color: #A9C2B1;
            font-variant-numeric: tabular-nums;
            letter-spacing: .02em;
            overflow-wrap: anywhere;
        }

        .pf-days {
            list-style: none;
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin: 1.25rem 0 0;
            padding: 0;
        }

        .pf-days li {
            display: flex;
            align-items: center;
            gap: .5rem;
            border: 1px solid #3B6A52;
            border-radius: 999px;
            padding: .3rem .8rem;
            font-size: .875rem;
            color: #BFD3C6;
        }

        .pf-mark {
            width: .6rem;
            height: .6rem;
            border-radius: 50%;
            border: 2px solid #7FA58D;
        }

        .pf-days li.is-here {
            background: var(--paddy-soft);
            border-color: var(--grain);
            color: #fff;
        }

        .pf-days li.is-here .pf-mark {
            background: var(--grain);
            border-color: var(--grain);
        }

        .pf-action {
            width: 100%;
        }

        .pf-checkin {
            width: 100%;
            background: var(--grain);
            color: #2E2300;
            border: 0;
            border-radius: 12px;
            padding: 1rem 1.75rem;
            font: inherit;
            font-size: 1.125rem;
            font-weight: 700;
            cursor: pointer;
        }

        .pf-checkin:hover {
            background: #F0C23A;
        }

        .pf-checkin:disabled {
            opacity: .65;
            cursor: default;
        }

        .pf-status {
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .pf-status .tick {
            flex: none;
            display: grid;
            place-items: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background: var(--leaf);
        }

        .pf-status .tick svg {
            width: 1.25rem;
            height: 1.25rem;
            stroke: #fff;
            fill: none;
            stroke-width: 3;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .pf-status b {
            display: block;
            font-size: 1.125rem;
        }

        .pf-status span {
            display: block;
            color: #BFD3C6;
            font-size: .9375rem;
        }

        .pf-status .pf-warn {
            color: #F6C778;
        }

        .pf-body {
            padding-top: 2rem;
            padding-bottom: 3rem;
        }

        .pf-h2 {
            margin: 0 0 .25rem;
            font-size: 1.125rem;
            font-weight: 650;
        }

        .pf-lede {
            margin: 0 0 1rem;
            color: var(--muted);
            font-size: .9375rem;
        }

        .pf-cols {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .pf-col {
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 1rem;
        }

        .pf-col.is-today {
            background: var(--grain-tint);
            border-color: #EFD98A;
        }

        .pf-colhead {
            display: flex;
            align-items: baseline;
            flex-wrap: wrap;
            gap: .25rem .6rem;
            margin: 0 0 .85rem;
            font-size: 1.0625rem;
            font-weight: 650;
        }

        .pf-date {
            font-weight: 400;
            color: var(--muted);
            font-size: .9375rem;
        }

        .pf-today {
            font-size: .75rem;
            font-weight: 600;
            background: var(--grain);
            color: #3D2E00;
            border-radius: 999px;
            padding: .05rem .5rem;
        }

        .pf-opt {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: .9rem 1rem;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 10px;
            font-size: 1.0625rem;
            cursor: pointer;
        }

        .pf-opt+.pf-opt {
            margin-top: .6rem;
        }

        .pf-opt:hover {
            border-color: #B3C4B4;
        }

        .pf-opt:has(input:checked) {
            background: #E8F3E8;
            border-color: var(--leaf);
        }

        .pf-opt input {
            width: 1.5rem;
            height: 1.5rem;
            margin: 0;
            accent-color: var(--leaf);
            cursor: pointer;
        }

        .pf-save {
            margin-top: 1.25rem;
        }

        .pf-btn {
            display: inline-block;
            background: var(--paddy);
            color: #fff;
            border: 0;
            border-radius: 10px;
            padding: .85rem 1.4rem;
            font: inherit;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .pf-btn:hover {
            background: var(--paddy-soft);
        }

        .pf-btn:disabled {
            opacity: .65;
            cursor: default;
        }

        .pf-btn.line {
            background: transparent;
            color: var(--paddy);
            border: 2px solid var(--paddy);
            padding: .75rem 1.3rem;
        }

        .pf-btn.line:hover {
            background: #E8F3E8;
        }

        .pf-cert {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 2.25rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--line);
        }

        .pf-cert .pf-lede {
            margin: 0;
        }

        .pf-muted {
            margin: 0;
            color: var(--muted);
            font-size: .9375rem;
        }

        @media (min-width: 40rem) {
            .pf-action {
                width: auto;
            }

            .pf-checkin {
                width: auto;
            }
        }

        @media (max-width: 48rem) {
            .pf-cols {
                grid-template-columns: 1fr;
            }
        }

        .pf-col.is-locked {
            background: #F1F4F0;
            border-style: dashed;
        }

        .pf-col.is-locked .pf-colhead {
            color: var(--muted);
        }

        .pf-col.is-locked .pf-opt {
            background: #F7F9F6;
            color: #8A978F;
            cursor: not-allowed;
        }

        .pf-col.is-locked .pf-opt:hover {
            border-color: var(--line);
        }

        .pf-col.is-locked .pf-opt:has(input:checked) {
            background: #ECEFEB;
            border-color: #C5CEC6;
        }

        .pf-col.is-locked .pf-opt input {
            cursor: not-allowed;
        }

        .pf-locked {
            font-size: .75rem;
            font-weight: 600;
            background: #E3E8E2;
            color: var(--muted);
            border-radius: 999px;
            padding: .05rem .5rem;
        }

        .pf-note {
            margin: 0 0 1rem;
            padding: .7rem 1rem;
            border-radius: 10px;
            background: var(--grain-tint);
            border: 1px solid #EFD98A;
            color: #5C4300;
            font-size: .9375rem;
        }

        .pf-cert-actions { display: flex; flex-wrap: wrap; gap: .75rem; }
    </style>

    <section class="pf-hero">
        <div class="ev-wrap pf-hero-inner">
            <div>
                <a href="{{ route('dashboard') }}" class="pf-back">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M15 18l-6-6 6-6" />
                    </svg>
                    Back to dashboard
                </a>
                <h1 class="pf-name">{{ $participant->name }}</h1>
                <p class="pf-org">{{ $participant->organization ?: 'No organization' }}</p>
                <p class="pf-id">ID {{ $participant->participant_code }}</p>

                <ul class="pf-days" aria-label="Attendance by day">
                    @foreach ($days as $d)
                        <li @class(['is-here' => $d['attended']])>
                            <span class="pf-mark" aria-hidden="true"></span>
                            <span>Day {{ $d['number'] }}, {{ $d['date']->format('M j') }}</span>
                            <span class="ev-sr">{{ $d['attended'] ? 'attended' : 'not attended yet' }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="pf-action">
                @if ($todayAttendance)
                    <div class="pf-status">
                        <div class="tick"><svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M5 12.5l4.5 4.5L19 7.5" />
                            </svg></div>
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

        <form method="POST" action="{{ route('participants.requirements', $participant) }}" data-once>
            @csrf
            <h2 class="pf-h2">Meals and accommodation</h2>
            @php $anyAttended = $days->contains('attended', true); @endphp
            <p class="pf-lede">Tick what this participant receives. Only days they attended can be changed.</p>
            @unless ($anyAttended)
                <p class="pf-note">No attended day yet, so everything is locked. Check this participant in first.</p>
            @endunless

            <div class="pf-cols">
                @foreach ($days as $d)
                    @php $row = $requirements->get($d['number']); @endphp
                    <div @class(['pf-col', 'is-today' => $d['is_today'], 'is-locked' => !$d['attended']]) role="group"
                        aria-labelledby="day-{{ $d['number'] }}">
                        <h3 class="pf-colhead" id="day-{{ $d['number'] }}">
                            Day {{ $d['number'] }}
                            <span class="pf-date">{{ $d['date']->format('M j') }}</span>
                            @if ($d['is_today'])<span class="pf-today">Today</span>@endif
                            @unless ($d['attended'])<span class="pf-locked">Not attended</span>@endunless
                        </h3>

                        @foreach (['lunch' => 'Lunch', 'dinner' => 'Dinner', 'accommodation' => 'Accommodation'] as $item => $label)
                            <label class="pf-opt">
                                <span>{{ $label }}</span>
                                <input type="checkbox" name="days[{{ $d['number'] }}][{{ $item }}]" value="1"
                                    @checked($row?->$item) @disabled(!$d['attended'])>
                            </label>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <div class="pf-save">
                <button class="pf-btn" data-busy="Saving..." @disabled(!$anyAttended)>Save requirements</button>
            </div>
        </form>

        <section class="pf-cert">
    <div>
        <h2 class="pf-h2">Certificate</h2>
        @if ($issued)
            <p class="pf-lede">Opens as a PDF in a new tab. Issued date: {{ $issued }}.</p>
        @else
            <p class="pf-lede">Opens as a PDF in a new tab, ready to print.</p>
        @endif
    </div>

    @if ($issued)
        <div class="pf-cert-actions">
            <a href="{{ route('participants.certificate', $participant) }}" target="_blank" rel="noopener" class="pf-btn line">
                Print certificate
            </a>
            <a href="{{ route('participants.certificate', [$participant, 'plus' => 1]) }}" target="_blank" rel="noopener" class="pf-btn line"
               title="Optional: prints the same certificate with the issued date one day later">
                Issue on {{ $issuedPlus }} (+1 day)
            </a>
        </div>
    @else
        <p class="pf-muted">Check this participant in first to print a certificate.</p>
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
    </script>
</x-app-layout>