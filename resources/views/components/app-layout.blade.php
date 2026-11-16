@props(['bare' => false])

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite('resources/css/app.css')
    <style>
        :root {
            --paddy: #14392A;
            --paddy-soft: #1F5239;
            --leaf: #2F7D32;
            --grain: #E6B422;
            --grain-tint: #FFF6D6;
            --ground: #F2F5F0;
            --paper: #FFFFFF;
            --ink: #17231C;
            --muted: #5B6A61;
            --line: #DCE3DB;
            --alert: #B45309;
            --danger: #B42318;
        }

        body {
            margin: 0;
            background: var(--ground);
            color: var(--ink);
            line-height: 1.5;
            font-family: 'Bricolage Grotesque Variable', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
        }

        :focus-visible {
            outline: 3px solid var(--paddy);
            outline-offset: 2px;
        }

        .ev-nav :focus-visible,
        .ev-hero :focus-visible {
            outline-color: var(--grain);
        }

        .ev-wrap {
            max-width: 72rem;
            margin: 0 auto;
            padding: 0 1.25rem;
        }

        .ev-sr {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            clip: rect(0 0 0 0);
            white-space: nowrap;
        }

        .ev-skip {
            position: absolute;
            left: -9999px;
        }

        .ev-skip:focus {
            left: 1rem;
            top: 1rem;
            z-index: 60;
            background: #fff;
            padding: .5rem .75rem;
            border-radius: 8px;
        }

        .ev-nav {
            background: var(--paddy);
            color: #fff;
        }

        .ev-nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 3.5rem;
        }

        .ev-brand {
            display: flex;
            align-items: center;
            gap: .6rem;
            color: #fff;
            text-decoration: none;
            font-weight: 650;
        }

        .ev-brand svg {
            width: 1.25rem;
            height: 1.25rem;
            fill: var(--grain);
        }

        .ev-brand small {
            font-weight: 400;
            color: #B9CDBF;
            font-size: .875rem;
        }

        .ev-logout {
            background: none;
            border: 1px solid #3B6A52;
            color: #E5EFE8;
            border-radius: 8px;
            padding: .4rem .85rem;
            font: inherit;
            font-size: .875rem;
            cursor: pointer;
        }

        .ev-logout:hover {
            background: var(--paddy-soft);
        }

        .ev-pagehead {
            background: var(--paper);
            border-bottom: 1px solid var(--line);
            padding: 1rem 0;
        }

        .ev-toast {
            position: fixed;
            left: 50%;
            bottom: 1.25rem;
            transform: translateX(-50%);
            z-index: 50;
            display: flex;
            gap: 1rem;
            align-items: center;
            max-width: min(32rem, calc(100vw - 2rem));
            padding: .75rem 1rem;
            border-radius: 10px;
            font-size: .9375rem;
            box-shadow: 0 6px 24px rgba(20, 57, 42, .25);
        }

        .ev-toast.ok {
            background: var(--paddy);
            color: #fff;
        }

        .ev-toast.err {
            background: #8A1C13;
            color: #fff;
        }

        .ev-toast button {
            background: none;
            border: 0;
            color: inherit;
            font-size: 1.25rem;
            line-height: 1;
            cursor: pointer;
        }

        @media (prefers-reduced-motion: reduce) {
            * {
                transition: none !important;
                animation: none !important;
            }
        }

        .ev-navright {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .ev-navlink {
            color: #E5EFE8;
            text-decoration: none;
            font-size: .9375rem;
        }

        .ev-navlink:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <a class="ev-skip" href="#main">Skip to content</a>

    <header class="ev-nav">
        <div class="ev-wrap ev-nav-inner">
            @if ($bare)
                {{-- Public pages: brand only, no links to the staff area --}}
                <span class="ev-brand">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 2c4 3 6 7 6 11a6 6 0 0 1-12 0c0-4 2-8 6-11z" />
                    </svg>
                    <span>Ugnay Palay</span>
                </span>
            @else
                <a href="{{ route('dashboard') }}" class="ev-brand">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 2c4 3 6 7 6 11a6 6 0 0 1-12 0c0-4 2-8 6-11z" />
                    </svg>
                    <span>Ugnay Palay <small>Registration desk</small></span>
                </a>
                <div class="ev-navright">
                    <a class="ev-navlink" href="{{ route('registrations.create') }}" target="_blank" rel="noopener">Register
                        someone</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="ev-logout">Log out</button>
                    </form>
                </div>
            @endif
        </div>
    </header>

    @isset($header)
        <div class="ev-pagehead">
            <div class="ev-wrap">{{ $header }}</div>
        </div>
    @endisset

    <main id="main">{{ $slot }}</main>

    @if (session('status'))
        <div class="ev-toast ok" role="status"><span>{{ session('status') }}</span><button type="button"
                aria-label="Dismiss">&times;</button></div>
    @endif
    @if (session('error'))
        <div class="ev-toast err" role="alert"><span>{{ session('error') }}</span><button type="button"
                aria-label="Dismiss">&times;</button></div>
    @endif

    <script>
        document.querySelectorAll('.ev-toast.ok').forEach(t => setTimeout(() => t.remove(), 6000));
        document.querySelectorAll('.ev-toast button').forEach(b => b.addEventListener('click', () => b.parentElement.remove()));
    </script>
</body>

</html>