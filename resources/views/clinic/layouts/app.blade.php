<!DOCTYPE html>
@php
    $clinicUser = auth()->user();
    $clinicDoctor = $clinicUser?->clinicDoctor();
    // Follows the clinic the doctor switched to, so the Kiosk menu always
    // opens the screens for the clinic they're currently working in.
    $displayClinic = $clinicDoctor?->activeClinic();
    $homeRoute = ($clinicUser && $clinicUser->isDoctor())
        ? route('practice.doctor.dashboard')
        : route('practice.assistant.dashboard');
    // Patient chat is the doctor's own correspondence, so assistants sharing
    // this workspace get neither the inbox nor the Alpine it needs — their
    // (vanilla-JS heavy) screens stay exactly as they were.
    $showPatientChat = (bool) $clinicUser?->isDoctor();
@endphp
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? __('app.clinic.title') }} &middot; {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- Counterpart to @stack('scripts') at the end of the body: without it a
         view's @push('styles') is silently dropped. --}}
    @stack('styles')
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen overflow-x-hidden">
    <nav class="bg-white shadow-sm border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 py-2 min-h-[4rem] flex flex-wrap items-center justify-between gap-x-2 gap-y-2">
            <div class="flex items-center gap-2 min-w-0">
                <span class="text-2xl">🩺</span>
                <a href="{{ $homeRoute }}" class="font-bold text-lg text-slate-900 truncate">{{ config('app.name') }}</a>
                @auth
                    <span class="mx-1 sm:mx-3 text-xs px-2 py-1 rounded-full whitespace-nowrap
                        {{ auth()->user()->isDoctor() ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700' }}">
                        {{ auth()->user()->isDoctor() ? __('app.roles.doctor') : __('app.roles.assistant') }}
                    </span>
                @endauth
            </div>
            <div class="flex flex-wrap items-center justify-end gap-2 sm:gap-4">
                @if (auth()->check() && auth()->user()->isDoctor())
                    <a href="{{ route('doctor.dashboard') }}"
                       class="text-sm px-3 py-1 rounded-lg border border-indigo-300 text-indigo-700 bg-indigo-50 hover:bg-indigo-100 whitespace-nowrap">
                        {{ app()->getLocale() === 'ar' ? '← العودة إلى اللوحة الرئيسية' : '← Back to main dashboard' }}
                    </a>
                @endif
                @if ($displayClinic)
                    {{-- Kiosk launcher: the three in-clinic screens. --}}
                    <details data-menu class="relative">
                        <summary class="list-none cursor-pointer select-none text-sm px-3 py-1 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 whitespace-nowrap flex items-center gap-1">
                            🖥️ {{ __('app.display.menu') }}
                            <span class="text-[10px] text-slate-400">▼</span>
                        </summary>
                        <div class="absolute end-0 mt-2 w-64 bg-white border border-slate-200 rounded-xl shadow-lg py-1 z-50">
                            <a href="{{ route('practice.display.screen', $displayClinic) }}" target="_blank"
                               class="block px-4 py-2.5 hover:bg-slate-50">
                                <div class="text-sm font-medium text-slate-800">📺 {{ __('app.display.counter') }}</div>
                                <div class="text-xs text-slate-400">{{ __('app.display.counter_hint') }}</div>
                            </a>
                            <a href="{{ route('practice.display.screen', ['clinic' => $displayClinic, 'checkin' => 1]) }}" target="_blank"
                               class="block px-4 py-2.5 hover:bg-amber-50">
                                <div class="text-sm font-medium text-amber-700">🎫 {{ __('app.display.interactive') }}</div>
                                <div class="text-xs text-slate-400">{{ __('app.display.interactive_hint') }}</div>
                            </a>
                            <a href="{{ route('practice.screen') }}" target="_blank"
                               class="block px-4 py-2.5 hover:bg-indigo-50">
                                <div class="text-sm font-medium text-indigo-700">🩺 {{ __('app.display.assistant_screen') }}</div>
                                <div class="text-xs text-slate-400">{{ __('app.display.assistant_screen_hint') }}</div>
                            </a>
                        </div>
                    </details>
                @endif
                @if ($showPatientChat)
                    <x-chat-dropdown side="doctor" />
                @endif
                <a href="{{ route('locale', app()->getLocale() === 'ar' ? 'en' : 'ar') }}"
                   class="text-sm px-3 py-1 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 whitespace-nowrap">
                    🌐 {{ __('app.language') }}
                </a>
                @auth
                    <span class="hidden sm:inline text-sm text-slate-600">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-sm text-red-600 hover:underline">{{ __('app.logout') }}</button>
                    </form>
                @endauth
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-6">
        @if (session('status'))
            <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <script>
        function toggle(id) {
            document.getElementById(id).classList.toggle('hidden');
        }

        // <details data-menu> dropdowns: only one open at a time, closing on an
        // outside click, on Escape, and once an item inside is chosen (a bare
        // <details> would stay open through all three).
        document.addEventListener('click', function (e) {
            var onSummary = e.target.closest && e.target.closest('summary');
            document.querySelectorAll('details[data-menu][open]').forEach(function (d) {
                if (!d.contains(e.target)) {
                    d.removeAttribute('open');
                } else if (!onSummary && e.target.closest('a, button')) {
                    d.removeAttribute('open');
                }
            });
        });
        document.addEventListener('toggle', function (e) {
            var opened = e.target;
            if (!opened.matches || !opened.matches('details[data-menu][open]')) return;
            document.querySelectorAll('details[data-menu][open]').forEach(function (d) {
                if (d !== opened) d.removeAttribute('open');
            });
        }, true);
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            document.querySelectorAll('details[data-menu][open]').forEach(function (d) {
                d.removeAttribute('open');
            });
        });
    </script>
    <style>
        /* Hide the native disclosure triangle on our dropdown summaries. */
        details[data-menu] > summary::-webkit-details-marker { display: none; }
        details[data-menu] > summary::marker { content: ''; }
    </style>

    @if (session('announce'))
        <script>
            (function () {
                var a = @json(session('announce'));
                var lang = @json(app()->getLocale());
                var phrase = String(a.queue_number);
                var bcp = lang === 'ar' ? 'ar-SA' : 'en-US';
                var prefix = lang === 'ar' ? 'ar' : 'en';

                function pickVoice() {
                    var voices = window.speechSynthesis.getVoices() || [];
                    return voices.find(function (v) { return (v.lang || '').toLowerCase().indexOf(prefix) === 0; })
                        || voices.find(function (v) { return (v.lang || '').toLowerCase().indexOf(prefix) !== -1; })
                        || null;
                }
                function utter() {
                    var u = new SpeechSynthesisUtterance(phrase);
                    u.lang = bcp; u.rate = 0.9;
                    var v = pickVoice(); if (v) u.voice = v;
                    return u;
                }
                function speak() {
                    if (!('speechSynthesis' in window)) return;
                    window.speechSynthesis.cancel();
                    var u = utter();
                    u.onend = function () { window.speechSynthesis.speak(utter()); };
                    if (window.speechSynthesis.getVoices().length === 0) {
                        window.speechSynthesis.addEventListener('voiceschanged', function handler() {
                            window.speechSynthesis.removeEventListener('voiceschanged', handler);
                            u.voice = pickVoice() || u.voice;
                            window.speechSynthesis.speak(u);
                        });
                    } else {
                        window.speechSynthesis.speak(u);
                    }
                }

                // Arabic: play the pre-recorded clips (main.mp3 then {n}.mp3) when
                // they exist; if they're missing, fall back to speech synthesis so
                // the number is still announced out loud.
                if (lang === 'ar') {
                    var base = @json(asset('storage/voices/ar'));
                    var clips = [base + '/main.mp3', base + '/' + phrase + '.mp3'];
                    var i = 0, clipsWork = false, spoken = false;
                    function fallback() { if (!clipsWork && !spoken) { spoken = true; speak(); } }
                    (function playNext() {
                        if (i >= clips.length) return;
                        var audio = new Audio(clips[i++]);
                        audio.oncanplaythrough = function () { clipsWork = true; };
                        audio.onended = playNext;
                        audio.onerror = fallback;
                        var p = audio.play();
                        if (p && p.catch) { p.catch(fallback); }
                    })();
                    return;
                }

                speak();
            })();
        </script>
    @endif
    @if ($showPatientChat)
        {{-- Alpine powers the chat widget in the navbar. Deferred, so the widget's
             own inline script still registers chatWidget() before Alpine boots. --}}
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @endif
    @stack('scripts')
</body>
</html>
