<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('app.kiosk.title')) &middot; {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-600 to-violet-700 text-slate-800 flex flex-col">
    @php
        // The clinic's doctor photo (if one has been uploaded), shown above the
        // clinic name on every kiosk screen.
        $kioskDoctorPhoto = $clinic?->doctor?->getFirstMediaUrl('profile_image') ?: null;
    @endphp
    <header class="flex items-start justify-between px-6 py-4 text-white/90">
        <a href="{{ route('practice.kiosk.welcome', $clinic) }}" class="flex flex-col items-start gap-2">
            @if ($kioskDoctorPhoto)
                <img src="{{ $kioskDoctorPhoto }}" alt=""
                     class="w-16 h-16 rounded-full object-cover border-2 border-white/70 shadow-lg">
            @endif
            <span class="flex items-center gap-2 text-xl font-bold">
                <span class="text-3xl">🩺</span> {{ $clinic->name ?? config('app.name') }}
            </span>
        </a>
        <a href="{{ route('locale', app()->getLocale() === 'ar' ? 'en' : 'ar') }}"
           class="text-base px-4 py-2 rounded-xl bg-white/15 hover:bg-white/25">
            🌐 {{ __('app.language') }}
        </a>
    </header>

    <main class="flex-1 flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-xl bg-white rounded-3xl shadow-2xl p-8 sm:p-10">
            @if ($errors->any())
                <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 px-5 py-4 text-red-700 text-lg">
                    {{ $errors->first() }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="text-center text-white/70 text-sm pb-6">
        {{ __('app.kiosk.footer') }}
    </footer>
</body>
</html>
