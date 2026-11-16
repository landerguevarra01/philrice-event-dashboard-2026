@php
    $first = $days->first()['date'];
    $last = $days->last()['date'];
    $rangeText = $first->format('F j') . ' to ' . $last->format('j, Y');
    $pendingTotal = $pending['checkins'] + $pending['meals'];
@endphp

<x-app-layout>
    <style>
        .ev-hero {
            background: var(--paddy);
            color: #fff;
            padding: 2.25rem 0 2.5rem;
        }

        .ev-title {
            margin: 0;
            font-size: clamp(1.6rem, 3.2vw, 2.5rem);
            line-height: 1.1;
            font-weight: 700;
            letter-spacing: -.02em;
            max-width: 24ch;
        }

        .ev-sub {
            margin: .6rem 0 1.75rem;
            color: #BFD3C6;
        }

        .ev-search {
            position: relative;
            max-width: 46rem;
        }

        .ev-search svg {
            position: absolute;
            left: 1.1rem;
            top: 50%;
            transform: translateY(-50%);
            width: 1.4rem;
            height: 1.4rem;
            stroke: var(--muted);
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
        }

        .ev-search input {
            width: 100%;
            box-sizing: border-box;
            border: 0;
            border-radius: 14px;
            background: #fff;
            color: var(--ink);
            padding: 1.1rem 1.25rem 1.1rem 3.2rem;
            font: inherit;
            font-size: 1.25rem;
        }

        .ev-search input::placeholder {
            color: #7C8B82;
        }

        .ev-help {
            margin: .6rem 0 0;
            font-size: .875rem;
            color: #A9C2B1;
        }

        .ev-results {
            max-width: 46rem;
            margin-top: .75rem;
            background: #fff;
            color: var(--ink);
            border-radius: 12px;
            overflow: hidden;
        }

        .ev-results:empty {
            display: none;
        }

        .ev-result {
            display: flex;
            width: 100%;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: .85rem 1.1rem;
            border: 0;
            border-bottom: 1px solid var(--line);
            background: #fff;
            text-align: left;
            font: inherit;
            color: inherit;
            cursor: pointer;
        }

        .ev-result:last-child {
            border-bottom: 0;
        }

        .ev-result:hover {
            background: #F2F7F1;
        }

        .ev-result b {
            display: block;
            font-weight: 600;
            font-size: 1.0625rem;
        }

        .ev-result .sub {
            display: block;
            font-size: .875rem;
            color: var(--muted);
        }

        .ev-tag {
            font-size: .8125rem;
            border-radius: 999px;
            padding: .2rem .7rem;
            white-space: nowrap;
        }

        .ev-tag.in {
            background: #E1F2E2;
            color: #1E5B22;
        }

        .ev-tag.out {
            background: #EEF1ED;
            color: var(--muted);
        }

        .ev-msg {
            margin: 0;
            padding: .9rem 1.1rem;
            color: var(--muted);
            font-size: .9375rem;
        }

        .ev-msg.err {
            color: var(--danger);
        }

        .ev-note {
            margin: 0;
            padding: .6rem 1.1rem;
            background: var(--grain-tint);
            color: #6B4E00;
            font-size: .875rem;
            border-bottom: 1px solid #EFD98A;
        }

        .ev-body {
            padding-top: 1.75rem;
            padding-bottom: 3rem;
        }

        .ev-pending {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem 1rem;
            align-items: center;
            justify-content: space-between;
            background: var(--grain-tint);
            border: 1px solid #EFD98A;
            color: #5C4300;
            border-radius: 10px;
            padding: .75rem 1rem;
            margin-bottom: 1.25rem;
            font-size: .9375rem;
        }

        .ev-pending button {
            background: none;
            border: 0;
            font: inherit;
            font-weight: 600;
            text-decoration: underline;
            color: inherit;
            cursor: pointer;
        }

        .ev-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            margin: 0 0 2.25rem;
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 12px;
        }

        .ev-stat {
            display: flex;
            flex-direction: column-reverse;
            padding: 1.1rem 1.5rem;
            border-left: 1px solid var(--line);
        }

        .ev-stat:first-child {
            border-left: 0;
        }

        .ev-stat dt {
            color: var(--muted);
            font-size: .9375rem;
        }

        .ev-stat dd {
            margin: 0;
            font-size: 2.25rem;
            font-weight: 700;
            line-height: 1.15;
            color: var(--paddy);
            font-variant-numeric: tabular-nums;
        }

        .ev-grid {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
            gap: 2.5rem;
            align-items: start;
        }

        .ev-h2 {
            margin: 0 0 .25rem;
            font-size: 1.125rem;
            font-weight: 650;
        }

        .ev-lede {
            margin: 0 0 1rem;
            color: var(--muted);
            font-size: .9375rem;
        }

        .ev-table-wrap {
            overflow-x: auto;
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 12px;
        }

        .ev-days {
            width: 100%;
            min-width: 28rem;
            border-collapse: collapse;
        }

        .ev-days th,
        .ev-days td {
            padding: .8rem 1rem;
            text-align: right;
            border-top: 1px solid var(--line);
            font-variant-numeric: tabular-nums;
        }

        .ev-days thead th {
            border-top: 0;
            vertical-align: bottom;
            font-weight: 600;
        }

        .ev-days tbody th {
            text-align: left;
            font-weight: 500;
            color: var(--muted);
        }

        .ev-days tbody tr:first-child td {
            font-weight: 700;
            color: var(--paddy);
        }

        .ev-days .is-today {
            background: var(--grain-tint);
        }

        .ev-days thead .is-today {
            box-shadow: inset 0 3px 0 var(--grain);
        }

        .ev-date {
            display: block;
            font-weight: 400;
            color: var(--muted);
            font-size: .875rem;
        }

        .ev-today {
            display: inline-block;
            margin-left: .4rem;
            font-size: .75rem;
            font-weight: 600;
            background: var(--grain);
            color: #3D2E00;
            border-radius: 999px;
            padding: .05rem .5rem;
        }

        .ev-recent {
            list-style: none;
            margin: 0;
            padding: 0;
            border-top: 1px solid var(--line);
        }

        .ev-recent li {
            border-bottom: 1px solid var(--line);
        }

        .ev-recent a {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: .7rem .25rem;
            color: inherit;
            text-decoration: none;
        }

        .ev-recent a:hover {
            background: #E9EFE8;
        }

        .ev-time {
            color: var(--muted);
            font-size: .875rem;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .ev-dot {
            display: inline-block;
            width: .5rem;
            height: .5rem;
            border-radius: 50%;
            background: var(--alert);
            margin-right: .45rem;
        }

        .ev-empty {
            padding: 1rem .25rem;
            color: var(--muted);
            font-size: .9375rem;
        }

        @media (max-width: 56rem) {
            .ev-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .ev-hero-grid {
                grid-template-columns: 1fr;
            }

            .ev-reg {
                width: 100%;
                max-width: 14rem;
            }
        }

        @media (max-width: 40rem) {
            .ev-stats {
                grid-template-columns: 1fr;
            }

            .ev-stat {
                border-left: 0;
                border-top: 1px solid var(--line);
            }

            .ev-stat:first-child {
                border-top: 0;
            }

            .ev-search input {
                font-size: 1.0625rem;
            }
        }

        .ev-hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 2rem 3rem;
            align-items: start;
        }

        .ev-reg {
            width: 12rem;
            background: #fff;
            color: var(--ink);
            border-radius: 14px;
            padding: 1rem;
        }

        .ev-reg-qr {
            display: block;
            width: 100%;
            padding: 0;
            border: 0;
            background: none;
            cursor: zoom-in;
        }

        .ev-reg-qr svg {
            display: block;
            width: 100%;
            height: auto;
        }

        .ev-reg-text {
            margin: .75rem 0 0;
            font-size: .9375rem;
            line-height: 1.4;
        }

        .ev-reg a {
            color: var(--paddy);
            font-weight: 600;
        }

        .ev-reg-url {
            margin: .35rem 0 0;
            font-size: .8125rem;
            color: var(--muted);
            overflow-wrap: anywhere;
        }

        .ev-qr-dialog {
            border: 0;
            border-radius: 16px;
            padding: 1.5rem;
            width: min(26rem, calc(100vw - 2rem));
            text-align: center;
            color: var(--ink);
        }

        .ev-qr-dialog::backdrop {
            background: rgba(20, 57, 42, .78);
        }

        .ev-qr-dialog svg {
            display: block;
            width: 100%;
            height: auto;
        }

        .ev-qr-dialog p {
            margin: 1rem 0 0;
        }

        .ev-qr-dialog a {
            color: var(--paddy);
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .ev-qr-close {
            margin-top: 1rem;
            background: var(--paddy);
            color: #fff;
            border: 0;
            border-radius: 10px;
            padding: .7rem 1.4rem;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
        }

        .ev-qr-close:hover {
            background: var(--paddy-soft);
        }

        @media (max-width: 56rem) {}
    </style>

    <section class="ev-hero">
        <div class="ev-wrap ev-hero-grid">

            <div>
                <h1 class="ev-title">{{ config('event.name') }}</h1>
                <p class="ev-sub">{{ config('event.venue') }}. {{ $rangeText }}.</p>

                <label for="q" class="ev-sr">Find a participant</label>
                <div class="ev-search">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" />
                        <path d="M20 20l-3.5-3.5" />
                    </svg>
                    <input id="q" type="search" autofocus autocomplete="off" placeholder="Type a first or last name">
                </div>
                <p class="ev-help">Type at least 2 letters. Press Enter to open the only match.</p>

                <div id="results" class="ev-results" aria-live="polite"></div>

                <form id="open-form" method="POST" action="{{ route('participants.open') }}" hidden>
                    @csrf
                    <input type="hidden" name="code">
                    <input type="hidden" name="name">
                    <input type="hidden" name="organization">
                </form>
            </div>

            @if ($registration['qr'])
                <aside class="ev-reg" aria-label="Registration link">
                    <button type="button" class="ev-reg-qr" id="qr-open" title="Click to enlarge"
                        aria-label="Enlarge the registration QR code">
                        {!! $registration['qr'] !!}
                    </button>
                    <p class="ev-reg-text">
                        Scan to register, or
                        <a href="{{ $registration['url'] }}" target="_blank" rel="noopener">open the registration link</a>.
                    </p>
                    <p class="ev-reg-url">{{ $registration['label'] }}</p>
                </aside>
            @endif
        </div>
    </section>

    @if ($registration['qr'])
        <dialog id="qr-dialog" class="ev-qr-dialog" aria-label="Registration QR code">
            {!! $registration['qr'] !!}
            <p>Scan with a phone camera to register.</p>
            <p><a href="{{ $registration['url'] }}" target="_blank" rel="noopener">{{ $registration['label'] }}</a></p>
            <form method="dialog"><button class="ev-qr-close">Close</button></form>
        </dialog>
    @endif

    <div class="ev-wrap ev-body">

        @if ($pendingTotal > 0)
            <div class="ev-pending">
                <span>Waiting to upload: {{ $pending['checkins'] }} check-in(s) and {{ $pending['meals'] }} meal
                    record(s).</span>
                <form method="POST" action="{{ route('sync') }}">
                    @csrf
                    <button>Upload now</button>
                </form>
            </div>
        @endif

        <dl class="ev-stats">
            <div class="ev-stat">
                <dt>Participants attended (all days)</dt>
                <dd>{{ $stats['attended'] }}</dd>
            </div>
            <div class="ev-stat">
                <dt>Checked in today</dt>
                <dd>{{ $stats['today'] }}</dd>
            </div>
            <div class="ev-stat">
                <dt>Accommodation nights</dt>
                <dd>{{ $stats['accommodation'] }}</dd>
            </div>
        </dl>

        <div class="ev-grid">
            <section>
                <h2 class="ev-h2">Event days</h2>
                <p class="ev-lede">Check-ins, meals, and accommodation for each day.</p>

                <div class="ev-table-wrap">
                    <table class="ev-days">
                        <thead>
                            <tr>
                                <th scope="col"><span class="ev-sr">Count</span></th>
                                @foreach ($days as $d)
                                    <th scope="col" @class(['is-today' => $d['is_today']])>
                                        Day {{ $d['number'] }}
                                        @if ($d['is_today'])<span class="ev-today">Today</span>@endif
                                        <span class="ev-date">{{ $d['date']->format('M j') }}</span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ([['checked_in', 'Checked in'], ['lunch', 'Lunch'], ['dinner', 'Dinner'], ['accommodation', 'Accommodation']] as [$key, $label])
                                <tr>
                                    <th scope="row">{{ $label }}</th>
                                    @foreach ($days as $d)
                                        <td @class(['is-today' => $d['is_today']])>{{ $d[$key] }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section>
                <h2 class="ev-h2">Recent check-ins</h2>
                <p class="ev-lede">An amber dot means the registration system hasn't received it yet.</p>

                <ul class="ev-recent">
                    @forelse ($recent as $a)
                        <li>
                            <a href="{{ route('participants.show', $a->participant) }}">
                                <span>
                                    @unless ($a->synced_at)<span class="ev-dot" title="Not uploaded yet"></span><span
                                    class="ev-sr">Not uploaded yet.</span>@endunless
                                    {{ $a->participant->name }}
                                </span>
                                <span
                                    class="ev-time">{{ $a->checked_in_at->format($a->checked_in_at->isToday() ? 'g:i A' : 'M j, g:i A') }}</span>
                            </a>
                        </li>
                    @empty
                        <li class="ev-empty">No check-ins yet. People appear here as they arrive.</li>
                    @endforelse
                </ul>
            </section>
        </div>

    </div>

    <script>
        (() => {
            const input = document.getElementById('q');
            const box = document.getElementById('results');
            const form = document.getElementById('open-form');
            const searchUrl = @json(route('participants.search'));
            let current = [], timer, controller;

            const esc = s => String(s ?? '').replace(/[&<>"']/g, c =>
                ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

            const message = (text, error = false) => {
                box.innerHTML = `<p class="ev-msg${error ? ' err' : ''}">${text}</p>`;
            };

            function render(results, offline = false) {
                current = results;
                const note = offline
                    ? '<p class="ev-note">Offline: showing participants saved on this device only.</p>'
                    : '';

                if (!results.length) {
                    box.innerHTML = note + '<p class="ev-msg">No participants found. Check the spelling or try the other name.</p>';
                    return;
                }

                box.innerHTML = note + results.map((r, i) => `
                    <button type="button" class="ev-result" data-i="${i}">
                        <span>
                            <b>${esc(r.name)}</b>
                            <span class="sub">${esc(r.organization || 'No organization')}</span>
                            <span class="sub">${esc(r.code)}</span>
                        </span>
                        <span class="ev-tag ${r.checked_in ? 'in' : 'out'}">${r.checked_in ? 'Checked in today' : 'Not yet today'}</span>
                    </button>`).join('');
            }

            async function run(term) {
                controller?.abort();
                controller = new AbortController();
                message('Searching...');
                try {
                    const res = await fetch(`${searchUrl}?q=${encodeURIComponent(term)}`, {
                        headers: { Accept: 'application/json' },
                        signal: controller.signal,
                    });
                    if (!res.ok) throw new Error();
                    const data = await res.json();
                    render(data.results, data.offline);
                } catch (e) {
                    if (e.name !== 'AbortError') message('Search is unavailable right now. Try again.', true);
                }
            }

            function open(r) {
                form.code.value = r.code;
                form.name.value = r.name;
                form.organization.value = r.organization ?? '';
                form.submit();
            }

            input.addEventListener('input', () => {
                clearTimeout(timer);
                const term = input.value.trim();
                if (term.length < 2) { current = []; box.innerHTML = ''; return; }
                timer = setTimeout(() => run(term), 350);
            });

            input.addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (current.length === 1) open(current[0]);
                }
            });

            box.addEventListener('click', e => {
                const btn = e.target.closest('[data-i]');
                if (btn) open(current[btn.dataset.i]);
            });
        })();
    </script>
    <script>
        (() => {
            const dialog = document.getElementById('qr-dialog');
            const open = document.getElementById('qr-open');
            if (!dialog || !open) return;
            open.addEventListener('click', () => dialog.showModal());
            dialog.addEventListener('click', e => { if (e.target === dialog) dialog.close(); });
        })();
    </script>
</x-app-layout>