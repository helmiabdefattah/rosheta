<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('app.kiosk.title')) &middot; {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // `short` targets the tablet in landscape, where height is the scarce
        // dimension: everything below shrinks so a screen fits without scrolling.
        tailwind.config = {
            theme: {
                extend: {
                    screens: {
                        short: { raw: '(max-height: 820px)' },
                        tiny: { raw: '(max-height: 620px)' },
                    },
                },
            },
        };
    </script>
    <style>
        /* A kiosk must never scroll: there is no visible scrollbar to discover
           and no mouse to drag one. Anything that cannot fit scrolls inside the
           card instead, which keeps the header and footer pinned. */
        html, body { height: 100%; overscroll-behavior: none; }
    </style>
</head>
<body class="h-full overflow-hidden bg-gradient-to-br from-indigo-600 to-violet-700 text-slate-800 flex flex-col">
    @php
        // The clinic's doctor photo (if one has been uploaded), shown above the
        // clinic name on every kiosk screen.
        $kioskDoctorPhoto = $clinic?->doctor?->getFirstMediaUrl('profile_image') ?: null;
    @endphp
    {{-- Header and footer stay fixed height so `main` owns the remaining space. --}}
    <header class="shrink-0 flex items-center justify-between px-6 py-4 short:px-4 short:py-2 text-white/90">
        <a href="{{ route('practice.kiosk.welcome', $clinic) }}" class="flex items-center gap-3">
            @if ($kioskDoctorPhoto)
                <img src="{{ $kioskDoctorPhoto }}" alt=""
                     class="w-16 h-16 short:w-10 short:h-10 rounded-full object-cover border-2 border-white/70 shadow-lg">
            @endif
            <span class="flex items-center gap-2 text-xl short:text-base font-bold">
                <span class="text-3xl short:text-xl">🩺</span> {{ $clinic->name ?? config('app.name') }}
            </span>
        </a>
        <a href="{{ route('locale', app()->getLocale() === 'ar' ? 'en' : 'ar') }}"
           class="text-base short:text-sm px-4 py-2 short:px-3 short:py-1.5 rounded-xl bg-white/15 hover:bg-white/25">
            🌐 {{ __('app.language') }}
        </a>
    </header>

    <main class="flex-1 min-h-0 flex items-center justify-center px-4 py-8 short:px-3 short:py-3">
        {{-- @yield('card-width') lets a screen go wider in landscape, where the
             extra width is what buys back the vertical space. --}}
        <div class="w-full @yield('card-width', 'max-w-xl') max-h-full overflow-y-auto bg-white rounded-3xl short:rounded-2xl shadow-2xl p-8 sm:p-10 short:p-5 tiny:p-4">
            @if ($errors->any())
                <div class="mb-6 short:mb-3 rounded-2xl bg-red-50 border border-red-200 px-5 py-4 short:px-4 short:py-2 text-red-700 text-lg short:text-base">
                    {{ $errors->first() }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="shrink-0 text-center text-white/70 text-sm short:text-xs pb-6 short:pb-2">
        {{ __('app.kiosk.footer') }}
    </footer>
</body>
</html>
