@php
    $start = \Carbon\Carbon::parse(config('event.start_date'));
    $end = $start->copy()->addDays($dayCount - 1);
    $rangeText = $start->format('F j') . ' to ' . $end->format('j, Y');
    $pendingTotal = $pending['checkins'] + $pending['meals'];
@endphp

<x-app-layout>
    <style>
        .ev-hero {
            background: var(--paddy);
            color: #fff;
            padding: 2.25rem 0 2.5rem;
        }

        .ev-hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 2rem 3rem;
            align-items: start;
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

        .ev-h2 {
            margin: 0 0 .25rem;
            font-size: 1.125rem;
            font-weight: 650;
        }

        .ev-lede {
            margin: 0;
            color: var(--muted);
            font-size: .9375rem;
        }

        .ev-empty {
            padding: 1.25rem 1rem;
            color: var(--muted);
            font-size: .9375rem;
        }

        .ev-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            margin: 0 0 1.25rem;
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

        .at-head {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .at-refresh {
            background: #fff;
            border: 1px solid #B9C6BB;
            border-radius: 10px;
            padding: .55rem 1rem;
            font: inherit;
            font-weight: 600;
            color: var(--paddy);
            cursor: pointer;
        }

        .at-refresh:hover {
            background: #E8F3E8;
        }

        .at-refresh:disabled {
            opacity: .6;
            cursor: default;
        }

        .at-note {
            margin: 0 0 1rem;
            padding: .7rem 1rem;
            border-radius: 10px;
            background: var(--grain-tint);
            border: 1px solid #EFD98A;
            color: #5C4300;
            font-size: .9375rem;
        }

        .at-note.err {
            background: #FDE8E6;
            border-color: #F1B8B2;
            color: #8A1C13;
        }

        .at-charts {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.75rem;
        }

        .at-card {
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 1.25rem;
        }

        .at-card h3 {
            margin: 0 0 .75rem;
            font-size: 1rem;
            font-weight: 650;
        }

        .at-legend {
            list-style: none;
            display: flex;
            flex-wrap: wrap;
            gap: .4rem 1.1rem;
            margin: 0 0 .5rem;
            padding: 0;
            font-size: .875rem;
            color: var(--muted);
        }

        .at-legend li {
            display: flex;
            align-items: center;
            gap: .4rem;
        }

        .at-table tbody tr[data-code] {
            cursor: pointer;
        }

        .at-table tbody tr[data-code]:hover {
            background: #F2F7F1;
        }

        .at-open {
            color: var(--paddy);
            font-weight: 600;
            white-space: nowrap;
        }

        .sw {
            width: .7rem;
            height: .7rem;
            border-radius: 3px;
        }

        .t-presenter {
            background: var(--grain);
        }

        .t-participant {
            background: var(--leaf);
        }

        .t-other {
            background: #7C8B94;
        }

        .t-day {
            background: var(--paddy);
        }

        .at-bars {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .at-bar {
            display: grid;
            grid-template-columns: 6.5rem 1fr 2.5rem;
            gap: .75rem;
            align-items: center;
            padding: .45rem 0;
        }

        .at-track {
            height: 1rem;
            background: #EEF1ED;
            border-radius: 999px;
            overflow: hidden;
        }

        .at-fill {
            display: block;
            height: 100%;
            border-radius: 999px;
        }

        .at-bar b {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .at-filter {
            display: flex;
            flex-wrap: wrap;
            gap: .75rem 1rem;
            align-items: center;
            justify-content: space-between;
            margin-bottom: .75rem;
        }

        .at-q {
            flex: 1;
            min-width: 14rem;
            max-width: 30rem;
            box-sizing: border-box;
            border: 1px solid #B9C6BB;
            border-radius: 10px;
            background: #fff;
            padding: .7rem .9rem;
            font: inherit;
        }

        .at-shown {
            color: var(--muted);
            font-size: .9375rem;
        }

        .ev-table-wrap {
            overflow-x: auto;
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 12px;
        }

        .at-table {
            width: 100%;
            min-width: 44rem;
            border-collapse: collapse;
        }

        .at-table th,
        .at-table td {
            padding: .75rem 1rem;
            text-align: left;
            border-top: 1px solid var(--line);
            vertical-align: middle;
        }

        .at-table thead th {
            border-top: 0;
            background: #F7F9F6;
            white-space: nowrap;
        }

        .at-sort {
            background: none;
            border: 0;
            padding: 0;
            font: inherit;
            font-weight: 600;
            color: var(--muted);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: .25rem;
        }

        .at-sort::after {
            content: '';
            display: inline-block;
            width: .8rem;
        }

        th[aria-sort="ascending"] .at-sort,
        th[aria-sort="descending"] .at-sort {
            color: var(--ink);
        }

        th[aria-sort="ascending"] .at-sort::after {
            content: '\2191';
        }

        th[aria-sort="descending"] .at-sort::after {
            content: '\2193';
        }

        .at-link {
            background: none;
            border: 0;
            padding: 0;
            font: inherit;
            font-weight: 600;
            color: var(--ink);
            text-align: left;
            cursor: pointer;
        }

        .at-link:hover {
            color: var(--paddy);
            text-decoration: underline;
        }

        .at-badge {
            display: inline-block;
            border-radius: 999px;
            padding: .15rem .7rem;
            font-size: .8125rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .b-presenter {
            background: #FBEFC4;
            color: #6B4E00;
        }

        .b-participant {
            background: #E1F2E2;
            color: #1E5B22;
        }

        .b-other {
            background: #E6EBEE;
            color: #44525B;
        }

        .at-dot {
            display: inline-block;
            width: .55rem;
            height: .55rem;
            border-radius: 50%;
            background: var(--leaf);
            margin-right: .45rem;
        }

        .at-dot.off {
            background: #C5CEC6;
        }

        .at-table td small {
            display: block;
            margin-left: 1rem;
            color: var(--muted);
            font-size: .8125rem;
        }

        .at-absent {
            color: var(--muted);
        }

        .at-count {
            display: inline-block;
            min-width: 1.75rem;
            text-align: center;
            border-radius: 999px;
            background: var(--paddy);
            color: #fff;
            font-weight: 700;
            padding: .1rem .6rem;
        }

        .at-count.zero {
            background: #C5CEC6;
            color: var(--ink);
        }

        @media (max-width: 56rem) {
            .ev-hero-grid {
                grid-template-columns: 1fr;
            }

            .ev-reg {
                width: 100%;
                max-width: 14rem;
            }

            .at-charts {
                grid-template-columns: 1fr;
            }

            .ev-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .ev-stat:nth-child(3) {
                border-left: 0;
            }

            .ev-stat:nth-child(n+3) {
                border-top: 1px solid var(--line);
            }
        }

        @media (max-width: 30rem) {
            .ev-stats {
                grid-template-columns: 1fr;
            }

            .ev-stat {
                border-left: 0;
            }

            .ev-stat:nth-child(n+2) {
                border-top: 1px solid var(--line);
            }

            .ev-search input {
                font-size: 1.0625rem;
            }
        }

        .sg { margin: 0 0 2rem; }

        .sg-form {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr)) auto;
            gap: 1rem;
            align-items: end;
            margin-top: 1rem;
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 1.25rem;
        }

        .sg-field label {
            display: block;
            margin: 0 0 .35rem;
            font-size: .9375rem;
            font-weight: 600;
        }

        .sg-field input {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #B9C6BB;
            border-radius: 10px;
            background: #fff;
            padding: .7rem .9rem;
            font: inherit;
        }

        .sg-err {
            margin: .35rem 0 0;
            font-size: .875rem;
            color: var(--danger);
        }

        .sg-save {
            background: var(--paddy);
            color: #fff;
            border: 0;
            border-radius: 10px;
            padding: .8rem 1.4rem;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
        }

        .sg-save:hover {
            background: var(--paddy-soft);
        }

        .sg-save:disabled {
            opacity: .65;
            cursor: default;
        }

        @media (max-width: 56rem) {
            .sg-form {
                grid-template-columns: 1fr;
            }

            .sg-save {
                width: 100%;
            }
        }
    </style>

    <section class="ev-hero">
        <div class="ev-wrap ev-hero-grid">

            {{-- <div>
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
            </div> --}}

            <div>
                <h1 class="ev-title">{{ config('event.name') }}</h1>
                <p class="ev-sub">{{ config('event.venue') }}. {{ $rangeText }}.</p>

                {{-- Used by the attendance table to open a participant's profile --}}
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

        <section class="sg" id="signatory" aria-labelledby="sg-title">
            <h2 class="ev-h2" id="sg-title">Certificate signatory</h2>
            <p class="ev-lede">Printed at the bottom of every certificate. Saving changes the one existing record, and the name prints in capital letters.</p>

            <form method="POST" action="{{ route('signatory.update') }}" class="sg-form" id="sg-form">
                @csrf
                @method('PUT')

                <div class="sg-field">
                    <label for="sg-name">Name</label>
                    <input id="sg-name" name="name" type="text" maxlength="120" required autocomplete="off"
                        value="{{ old('name', $signatory['name']) }}">
                    @error('name')
                    <p class="sg-err">{{ $message }}</p>@enderror
                </div>

                <div class="sg-field">
                    <label for="sg-role">Role</label>
                    <input id="sg-role" name="role" type="text" maxlength="160" required autocomplete="off"
                        value="{{ old('role', $signatory['role']) }}">
                    @error('role')
                    <p class="sg-err">{{ $message }}</p>@enderror
                </div>

                <button class="sg-save" id="sg-save">Save signatory</button>
            </form>
        </section>
        <section id="attendance" aria-labelledby="at-title">
            
            <div class="at-head">
                <div>
                    <h2 class="ev-h2" id="at-title">Attendance records</h2>
                    <p class="ev-lede" id="at-meta">Loading...</p>
                </div>
                <button type="button" class="at-refresh" id="at-refresh">Refresh</button>
            </div>

            <div id="at-notice"></div>

            <dl class="ev-stats">
                <div class="ev-stat">
                    <dt>Total records</dt>
                    <dd id="st-total">-</dd>
                </div>
                <div class="ev-stat">
                    <dt>Presenters</dt>
                    <dd id="st-presenters">-</dd>
                </div>
                <div class="ev-stat">
                    <dt>Participants</dt>
                    <dd id="st-participants">-</dd>
                </div>
                <div class="ev-stat">
                    <dt>Total attendance</dt>
                    <dd id="st-attendance">-</dd>
                </div>
            </dl>

            <div class="at-charts">
                <div class="at-card">
                    <h3>Attendance by type</h3>
                    <ul class="at-legend">
                        <li><span class="sw t-presenter"></span>Presenter</li>
                        <li><span class="sw t-participant"></span>Participant</li>
                        <li><span class="sw t-other"></span>Other</li>
                    </ul>
                    <ul class="at-bars" id="at-types"></ul>
                </div>
                <div class="at-card">
                    <h3>Attendance by day</h3>
                    <ul class="at-bars" id="at-days"></ul>
                </div>
            </div>

            <div class="at-filter">
                <label for="at-q" class="ev-sr">Filter attendance records</label>
                <input id="at-q" class="at-q" type="search" autocomplete="off"
                    placeholder="Filter records by name, affiliation, or type">
                <span class="at-shown" id="at-shown" aria-live="polite"></span>
            </div>

            <div class="ev-table-wrap">
                <table class="at-table">
                    <thead id="at-thead">
                        <tr>
                            <th scope="col" data-key="name" aria-sort="none"><button type="button" class="at-sort"
                                    data-sort="name">Name</button></th>
                            <th scope="col" data-key="type" aria-sort="none"><button type="button" class="at-sort"
                                    data-sort="type">Type</button></th>
                            <th scope="col" data-key="affiliation" aria-sort="none"><button type="button"
                                    class="at-sort" data-sort="affiliation">Affiliation</button></th>
                            @for ($i = 0; $i < $dayCount; $i++)
                                <th scope="col" data-key="d{{ $i }}" aria-sort="none"><button type="button" class="at-sort"
                                        data-sort="d{{ $i }}">Day {{ $i + 1 }}</button></th>
                            @endfor
                            <th scope="col" data-key="count" aria-sort="none"><button type="button" class="at-sort"
                                    data-sort="count">Count</button></th>
                            <th scope="col"><span class="ev-sr">Open profile</span></th>
                        </tr>
                    </thead>
                    <tbody id="at-body">
                        <tr>
                            <td colspan="{{ 5 + $dayCount }}" class="ev-empty">Loading attendance records...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
        
    </div>

    {{--
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
    </script> --}}

    <script>
        (() => {
            const dialog = document.getElementById('qr-dialog');
            const open = document.getElementById('qr-open');
            if (!dialog || !open) return;
            open.addEventListener('click', () => dialog.showModal());
            dialog.addEventListener('click', e => { if (e.target === dialog) dialog.close(); });
        })();
    </script>

    <script>
        (() => {
            const url = @json(route('attendance.index'));
            const dayCount = @json($dayCount);
            const byId = id => document.getElementById(id);
            const els = {
                body: byId('at-body'), thead: byId('at-thead'), q: byId('at-q'), shown: byId('at-shown'),
                meta: byId('at-meta'), notice: byId('at-notice'), refresh: byId('at-refresh'),
                total: byId('st-total'), presenters: byId('st-presenters'), participants: byId('st-participants'),
                attendance: byId('st-attendance'), types: byId('at-types'), days: byId('at-days'),
            };
            const openForm = byId('open-form');

            let records = [];
            let loaded = false;
            let query = '';
            let sort = { key: 'recent', dir: -1 };

            const esc = s => String(s ?? '').replace(/[&<>"']/g, c =>
                ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const typeClass = t => (t === 'presenter' || t === 'participant') ? t : 'other';

            function value(r, key) {
                if (key === 'name') return r.name.toLowerCase();
                if (key === 'type') return r.type;
                if (key === 'affiliation') return r.affiliation.toLowerCase();
                if (key === 'count') return r.count;
                if (key === 'recent') return Math.max(0, ...r.days.map(d => d.at || 0));
                return r.days[Number(key.slice(1))]?.at || 0;
            }

            function bars(target, rows) {
                const max = Math.max(1, ...rows.map(r => r.value));
                target.innerHTML = rows.map(r => `
                    <li class="at-bar">
                        <span>${esc(r.label)}</span>
                        <span class="at-track" aria-hidden="true"><span class="at-fill ${r.cls}" style="width:${r.value ? Math.max(3, Math.round(r.value / max * 100)) : 0}%"></span></span>
                        <b>${r.value}</b>
                    </li>`).join('');
            }

            function renderStats() {
                const count = t => records.filter(r => r.type === t).length;
                els.total.textContent = records.length;
                els.presenters.textContent = count('presenter');
                els.participants.textContent = count('participant');
                els.attendance.textContent = records.reduce((n, r) => n + r.count, 0);

                const other = records.length - count('presenter') - count('participant');
                const typeRows = [
                    { label: 'Participant', value: count('participant'), cls: 't-participant' },
                    { label: 'Presenter', value: count('presenter'), cls: 't-presenter' },
                ];
                if (other > 0) typeRows.push({ label: 'Other', value: other, cls: 't-other' });
                bars(els.types, typeRows.sort((a, b) => b.value - a.value));

                bars(els.days, Array.from({ length: dayCount }, (_, i) => ({
                    label: `Day ${i + 1}`,
                    value: records.filter(r => r.days[i] && r.days[i].date).length,
                    cls: 't-day',
                })));
            }

            function tableMessage(text) {
                els.body.innerHTML = `<tr><td colspan="${5 + dayCount}" class="ev-empty">${esc(text)}</td></tr>`;
            }

            function renderTable() {
                const q = query.trim().toLowerCase();
                const rows = records.filter(r => !q || [r.name, r.affiliation, r.type, r.code].some(v => String(v).toLowerCase().includes(q)));

                rows.sort((a, b) => {
                    const x = value(a, sort.key), y = value(b, sort.key);
                    const c = x < y ? -1 : (x > y ? 1 : 0);
                    return (c * sort.dir) || a.name.localeCompare(b.name);
                });

                els.shown.textContent = `${rows.length} of ${records.length} ${records.length === 1 ? 'record' : 'records'} shown`;

                if (!rows.length) {
                    tableMessage(records.length ? 'No records match your filter.' : 'No attendance has been recorded yet.');
                    return;
                }

                els.body.innerHTML = rows.map(r => `
                    <tr data-code="${esc(r.code)}">
                        <td><button type="button" class="at-link" title="Open profile">${esc(r.name)}</button></td>
                        <td><span class="at-badge b-${typeClass(r.type)}">${esc(r.type)}</span></td>
                        <td>${esc(r.affiliation) || '-'}</td>
                        ${r.days.map(d => d.date
                    ? `<td><span class="at-dot"></span>${esc(d.date)}${d.time ? `<small>${esc(d.time)}</small>` : ''}</td>`
                    : '<td class="at-absent"><span class="at-dot off"></span>Absent</td>').join('')}
                        <td><span class="at-count${r.count ? '' : ' zero'}">${r.count}</span></td>
                        <td class="at-open">Open profile &rsaquo;</td>
                    </tr>`).join('');
            }

            function renderHead() {
                els.thead.querySelectorAll('th[data-key]').forEach(th => {
                    th.setAttribute('aria-sort', th.dataset.key === sort.key ? (sort.dir === 1 ? 'ascending' : 'descending') : 'none');
                });
            }

            function showMeta(data) {
                const time = data.updated_at
                    ? new Date(data.updated_at).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' })
                    : null;
                els.meta.textContent = time ? `Updated ${time}. Refreshes every 30 seconds.` : 'No data received yet.';
                els.notice.innerHTML = data.stale
                    ? `<p class="at-note">The attendance service could not be reached. ${time ? 'Showing saved data from ' + esc(time) + '.' : 'No saved data is available yet.'}</p>`
                    : '';
            }

            async function load(fresh = false) {
                els.refresh.disabled = true;
                try {
                    const res = await fetch(url + (fresh ? '?fresh=1' : ''), { headers: { Accept: 'application/json' } });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    const data = await res.json();
                    records = Array.isArray(data.records) ? data.records : [];
                    loaded = true;
                    showMeta(data);
                    renderStats();
                    renderTable();
                } catch (e) {
                    els.notice.innerHTML = '<p class="at-note err">Could not load attendance records. Check the connection and press Refresh.</p>';
                    if (!loaded) tableMessage('Attendance records are unavailable right now.');
                } finally {
                    els.refresh.disabled = false;
                }
            }

            els.q.addEventListener('input', () => { query = els.q.value; renderTable(); });
            els.refresh.addEventListener('click', () => load(true));

            els.thead.addEventListener('click', e => {
                const btn = e.target.closest('[data-sort]');
                if (!btn) return;
                const key = btn.dataset.sort;
                sort = {
                    key,
                    dir: sort.key === key ? -sort.dir : ((key === 'count' || key.startsWith('d')) ? -1 : 1),
                };
                renderHead();
                renderTable();
            });

            // Click anywhere on a row to open that person's profile (check-in, meals, certificate)
            let opening = false;
            let resetTimer;

            function resetOpening() {
                opening = false;
                clearTimeout(resetTimer);
                els.body.querySelectorAll('tr[data-code]').forEach(r => { r.style.opacity = ''; });
            }

            // Click anywhere on a row to open that person's profile (check-in, meals, certificate)
            els.body.addEventListener('click', e => {
                const row = e.target.closest('tr[data-code]');
                if (!row || opening) return;
                const r = records.find(x => x.code === row.dataset.code);
                if (!r) return;

                opening = true;
                row.style.opacity = '.6';
                resetTimer = setTimeout(resetOpening, 5000);   // never stay locked if the page doesn't change

                openForm.code.value = r.code;
                openForm.name.value = r.name;
                openForm.organization.value = r.affiliation || '';
                openForm.submit();
            });

            // Coming back with the browser's Back button can restore the page as it was left
            window.addEventListener('pageshow', e => {
                resetOpening();
                if (e.persisted) load(false);   // also refresh the records
            });

            setInterval(() => { if (!document.hidden) load(false); }, 30000);
            document.addEventListener('visibilitychange', () => { if (!document.hidden) load(false); });

            renderHead();
            load(false);
        })();
    </script>

    <script>
    (() => {
        const form = document.getElementById('sg-form');
        if (!form) return;

        form.addEventListener('submit', () => {
            const btn = document.getElementById('sg-save');
            btn.disabled = true;
            btn.textContent = 'Saving...';
        });

        // After the browser's Back button, the page can come back with the button still disabled
        window.addEventListener('pageshow', () => {
            const btn = document.getElementById('sg-save');
            btn.disabled = false;
            btn.textContent = 'Save signatory';
        });
    })();
</script>
</x-app-layout>