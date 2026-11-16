@php
    $start = \Carbon\Carbon::parse(config('event.start_date'));
    $end = $start->copy()->addDays(((int) config('event.days')) - 1);
    $rangeText = $start->format('F j') . ' to ' . $end->format('j, Y');

    $nameFields = [
        ['firstName', 'First name', 'text', true],
        ['midName', 'Middle name', 'text', false],
        ['lastName', 'Last name', 'text', true],
        ['extName', 'Name extension (Jr., III)', 'text', false],
        ['email', 'Email address', 'email', true],
        ['contactNo', 'Mobile number', 'tel', true],
    ];
    $yn = ['true' => 'Yes', 'false' => 'No'];
@endphp

<x-app-layout :bare="true">
    <style>
        .rg-hero {
            background: var(--paddy);
            color: #fff;
            padding: 2rem 0 2.25rem;
        }

        .rg-title {
            margin: 0;
            font-size: clamp(1.75rem, 4vw, 2.5rem);
            line-height: 1.1;
            font-weight: 700;
            letter-spacing: -.02em;
        }

        .rg-sub {
            margin: .5rem 0 0;
            color: #BFD3C6;
        }

        .rg-body {
            padding-top: 2rem;
            padding-bottom: 3rem;
            max-width: 52rem;
        }

        .rg-alert {
            margin: 0 0 1.25rem;
            padding: .75rem 1rem;
            border-radius: 10px;
            background: #FDE8E6;
            color: #8A1C13;
        }

        .rg-card {
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 1.25rem 1.5rem 1.5rem;
            margin-bottom: 1.25rem;
        }

        .rg-card h2 {
            margin: 0 0 1rem;
            font-size: 1.125rem;
            font-weight: 650;
        }

        .rg-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem 1.25rem;
        }

        .rg-field {
            border: 0;
            margin: 0;
            padding: 0;
            min-width: 0;
        }

        .rg-field>label,
        .rg-field>legend {
            display: block;
            padding: 0;
            margin: 0 0 .35rem;
            font-size: .9375rem;
            font-weight: 600;
        }

        .rg-opt {
            margin-left: .4rem;
            font-weight: 400;
            color: var(--muted);
            font-size: .8125rem;
        }

        .rg-field input[type=text],
        .rg-field input[type=email],
        .rg-field input[type=tel],
        .rg-field select {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #B9C6BB;
            border-radius: 10px;
            background: #fff;
            color: var(--ink);
            padding: .75rem .9rem;
            font: inherit;
            font-size: 1rem;
        }

        .rg-field input[aria-invalid=true],
        .rg-field select[aria-invalid=true] {
            border-color: var(--danger);
        }

        .rg-err {
            margin: .35rem 0 0;
            font-size: .875rem;
            color: var(--danger);
        }

        .rg-choices {
            display: flex;
            gap: .6rem;
        }

        .rg-choice {
            flex: 1;
            display: flex;
            align-items: center;
            gap: .65rem;
            min-height: 3rem;
            box-sizing: border-box;
            padding: .7rem .9rem;
            background: #fff;
            border: 1px solid #B9C6BB;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            line-height: 1.2;
        }

        .rg-choice input {
            flex: none;
            width: 1.25rem;
            height: 1.25rem;
            margin: 0;
            accent-color: var(--leaf);
        }

        .rg-choice:has(input:checked) {
            background: #E8F3E8;
            border-color: var(--leaf);
        }

        .rg-extra {
            margin-top: 1rem;
        }

        .rg-extra[hidden] {
            display: none;
        }

        .rg-stack>*+* {
            margin-top: 1.25rem;
        }

        .rg-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
            margin-top: 1.5rem;
        }

        .rg-submit {
            background: var(--paddy);
            color: #fff;
            border: 0;
            border-radius: 12px;
            padding: 1rem 2rem;
            font: inherit;
            font-size: 1.0625rem;
            font-weight: 700;
            cursor: pointer;
        }

        .rg-submit:hover {
            background: var(--paddy-soft);
        }

        .rg-submit:disabled {
            opacity: .65;
            cursor: default;
        }

        .rg-hp {
            position: absolute;
            left: -9999px;
            width: 1px;
            height: 1px;
            overflow: hidden;
        }

        .rg-done {
            text-align: center;
            padding: 2.5rem 1.5rem;
        }

        .rg-done p {
            margin: 0 0 1.25rem;
            color: var(--muted);
        }

        .rg-again {
            display: inline-block;
            background: var(--paddy);
            color: #fff;
            text-decoration: none;
            border-radius: 10px;
            padding: .8rem 1.4rem;
            font-weight: 600;
        }

        .rg-again:hover {
            background: var(--paddy-soft);
        }

        @media (max-width: 40rem) {
            .rg-grid {
                grid-template-columns: 1fr;
            }

            .rg-submit {
                width: 100%;
            }
        }
    </style>

    <section class="rg-hero">
        <div class="ev-wrap">
            <h1 class="rg-title">Register</h1>
            <p class="rg-sub">{{ config('event.name') }}</p>
            <p class="rg-sub" style="margin-top: .15rem;">{{ config('event.venue') }}. {{ $rangeText }}.</p>
        </div>
    </section>

    <div class="ev-wrap rg-body">
        @if (session('registered'))

            <section class="rg-card rg-done" role="status">
                <h2>Thank you, you're registered.</h2>
                <p>{{ session('registered') }}</p>
                <a class="rg-again" href="{{ route('registrations.create') }}">Register another person</a>
            </section>

        @else

            @if ($errors->any())
                <p class="rg-alert" role="alert">Please check the highlighted fields and try again.</p>
            @endif

            <form method="POST" action="{{ route('registrations.store') }}" id="register-form">
                @csrf

                {{-- Spam trap: real visitors never see or fill this field --}}
                <div class="rg-hp" aria-hidden="true">
                    <label>Leave this field empty <input type="text" name="website" tabindex="-1"
                            autocomplete="off"></label>
                </div>

                <section class="rg-card">
                    <h2>Personal details</h2>
                    <div class="rg-grid">
                        @foreach ($nameFields as [$name, $label, $type, $required])
                            <div class="rg-field">
                                <label for="{{ $name }}">{{ $label }}@unless ($required)<span
                                class="rg-opt">optional</span>@endunless</label>
                                <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name) }}"
                                    autocomplete="off" @required($required) @error($name) aria-invalid="true" @enderror>
                                @error($name)
                                <p class="rg-err">{{ $message }}</p>@enderror
                            </div>
                        @endforeach

                        <div class="rg-field">
                            <label for="sex">Sex</label>
                            <select id="sex" name="sex" required @error('sex') aria-invalid="true" @enderror>
                                <option value="">Select</option>
                                @foreach (['Male', 'Female'] as $opt)
                                    <option value="{{ $opt }}" @selected(old('sex') === $opt)>{{ $opt }}</option>
                                @endforeach
                            </select>
                            @error('sex')
                            <p class="rg-err">{{ $message }}</p>@enderror
                        </div>

                        <div class="rg-field">
                            <label for="ageBracket">Age bracket</label>
                            <select id="ageBracket" name="ageBracket" required @error('ageBracket') aria-invalid="true"
                            @enderror>
                                <option value="">Select</option>
                                @foreach (config('event.age_brackets') as $opt)
                                    <option value="{{ $opt }}" @selected(old('ageBracket') === $opt)>{{ $opt }}</option>
                                @endforeach
                            </select>
                            @error('ageBracket')
                            <p class="rg-err">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                <section class="rg-card">
                    <h2>Additional information</h2>
                    <div class="rg-stack">

                        <div>
                            <fieldset class="rg-field">
                                <legend>Indigenous group member</legend>
                                <div class="rg-choices">
                                    @foreach ($yn as $value => $text)
                                        <label class="rg-choice"><input type="radio" name="isIndigenous" value="{{ $value }}"
                                                @checked(old('isIndigenous', 'false') === $value) required> {{ $text }}</label>
                                    @endforeach
                                </div>
                                @error('isIndigenous')
                                <p class="rg-err">{{ $message }}</p>@enderror
                            </fieldset>
                            <div class="rg-field rg-extra" data-show-when="isIndigenous=true" hidden>
                                <label for="indigenousGroup">Indigenous group name</label>
                                <input id="indigenousGroup" name="indigenousGroup" type="text"
                                    value="{{ old('indigenousGroup') }}" autocomplete="off" required
                                    @error('indigenousGroup') aria-invalid="true" @enderror>
                                @error('indigenousGroup')
                                <p class="rg-err">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <fieldset class="rg-field">
                                <legend>Person with a disability</legend>
                                <div class="rg-choices">
                                    @foreach ($yn as $value => $text)
                                        <label class="rg-choice"><input type="radio" name="withDisability" value="{{ $value }}"
                                                @checked(old('withDisability', 'false') === $value) required> {{ $text }}</label>
                                    @endforeach
                                </div>
                                @error('withDisability')
                                <p class="rg-err">{{ $message }}</p>@enderror
                            </fieldset>
                            <div class="rg-field rg-extra" data-show-when="withDisability=true" hidden>
                                <label for="disability">Type of disability</label>
                                <input id="disability" name="disability" type="text" value="{{ old('disability') }}"
                                    autocomplete="off" required @error('disability') aria-invalid="true" @enderror>
                                @error('disability')
                                <p class="rg-err">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rg-card">
                    <h2>Affiliation</h2>

                    <fieldset class="rg-field">
                        <legend>PhilRice employee</legend>
                        <div class="rg-choices">
                            @foreach ($yn as $value => $text)
                                <label class="rg-choice"><input type="radio" name="isPhilriceEmp" value="{{ $value }}"
                                        @checked(old('isPhilriceEmp') === $value) required> {{ $text }}</label>
                            @endforeach
                        </div>
                        @error('isPhilriceEmp')
                        <p class="rg-err">{{ $message }}</p>@enderror
                    </fieldset>

                    <div class="rg-extra" data-show-when="isPhilriceEmp=true" hidden>
                        <div class="rg-grid">
                            @foreach ([['philriceName', 'Employee name (as in PhilRice records)'], ['philriceStation', 'PhilRice station'], ['philriceUnit', 'Unit or division']] as [$name, $label])
                                <div class="rg-field">
                                    <label for="{{ $name }}">{{ $label }}</label>
                                    <input id="{{ $name }}" name="{{ $name }}" type="text" value="{{ old($name) }}"
                                        autocomplete="off" required @error($name) aria-invalid="true" @enderror>
                                    @error($name)
                                    <p class="rg-err">{{ $message }}</p>@enderror
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="rg-field rg-extra" data-show-when="isPhilriceEmp=false" hidden>
                        <label for="affiliationName">Organization or school</label>
                        <input id="affiliationName" name="affiliationName" type="text" value="{{ old('affiliationName') }}"
                            autocomplete="off" required @error('affiliationName') aria-invalid="true" @enderror>
                        @error('affiliationName')
                        <p class="rg-err">{{ $message }}</p>@enderror
                    </div>
                </section>

                <div class="rg-actions">
                    <button class="rg-submit" id="submit-btn">Register participant</button>
                </div>
            </form>

        @endif
    </div>

    <script>
        (() => {
            const form = document.getElementById('register-form');
            if (!form) return;   // the confirmation card has no form

            // Show a follow-up question only when its answer applies. Hidden fields are disabled,
            // so they are not required by the browser and are not submitted.
            const sync = () => {
                document.querySelectorAll('[data-show-when]').forEach(box => {
                    const [name, value] = box.dataset.showWhen.split('=');
                    const checked = document.querySelector(`input[name="${name}"]:checked`);
                    const show = !!checked && checked.value === value;
                    box.hidden = !show;
                    box.querySelectorAll('input, select').forEach(el => { el.disabled = !show; });
                });
            };
            document.querySelectorAll('input[type=radio]').forEach(r => r.addEventListener('change', sync));
            sync();

            // One click only, so a double tap can't register the same person twice
            form.addEventListener('submit', () => {
                const btn = document.getElementById('submit-btn');
                btn.disabled = true;
                btn.textContent = 'Registering...';
            });
            window.addEventListener('pageshow', e => { if (e.persisted) location.reload(); });
        })();
    </script>
</x-app-layout>