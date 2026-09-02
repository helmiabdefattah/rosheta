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
    // The site's own name, not the framework's: it heads every screen and tab.
    $brand = \App\Support\SiteBrand::name(app()->getLocale());
    $backToMainLabel = app()->getLocale() === 'ar' ? '← العودة إلى اللوحة الرئيسية' : '← Back to main dashboard';
    // The three in-clinic screens, listed once so the wide bar and the phone
    // menu can never drift apart.
    $kioskLinks = $displayClinic ? [
        ['href' => route('practice.display.screen', $displayClinic), 'icon' => '📺', 'class' => 'text-slate-800',
         'label' => __('app.display.counter'), 'hint' => __('app.display.counter_hint')],
        ['href' => route('practice.display.screen', ['clinic' => $displayClinic, 'checkin' => 1]), 'icon' => '🎫', 'class' => 'text-amber-700',
         'label' => __('app.display.interactive'), 'hint' => __('app.display.interactive_hint')],
        ['href' => route('practice.screen'), 'icon' => '🩺', 'class' => 'text-indigo-700',
         'label' => __('app.display.assistant_screen'), 'hint' => __('app.display.assistant_screen_hint')],
    ] : [];
@endphp
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? __('app.clinic.title') }} &middot; {{ $brand }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* The practice area's shared controls. Plain CSS on purpose: the Tailwind
           CDN build cannot see classes that scripts add later, and these are
           used from partials and cloned templates alike. Buttons carry a role
           (primary / secondary / danger / status), never a section colour. */
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: .4rem; border-radius: .5rem;
               font-size: .875rem; font-weight: 600; line-height: 1.25rem; padding: .5rem 1rem;
               border: 1px solid transparent; white-space: nowrap; cursor: pointer;
               transition: background-color .12s, color .12s, border-color .12s; }
        .btn:disabled { opacity: .5; cursor: not-allowed; }
        .btn-sm { padding: .3rem .7rem; font-size: .8125rem; }
        .btn-block { width: 100%; }
        .btn-primary { background: #4f46e5; color: #fff; }           .btn-primary:hover { background: #4338ca; }
        .btn-secondary { background: #fff; color: #334155; border-color: #cbd5e1; } .btn-secondary:hover { background: #f8fafc; }
        .btn-ghost { background: transparent; color: #4f46e5; padding-inline: .5rem; } .btn-ghost:hover { background: #eef2ff; }
        .btn-danger { background: transparent; color: #dc2626; }    .btn-danger:hover { background: #fef2f2; }
        .btn-start { background: #f59e0b; color: #1f2937; }         .btn-start:hover { background: #d97706; }
        .btn-complete { background: #059669; color: #fff; }         .btn-complete:hover { background: #047857; }
        summary.btn::-webkit-details-marker { display: none; }
        summary.btn::marker { content: ''; }

        /* Cards: every section sits in its own white panel with a hairline
           border, so fields never read as floating on the page background. */
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: .75rem;
                box-shadow: 0 1px 2px rgba(15, 23, 42, .05); }
        details.card > summary { border-radius: .75rem; }
        details.card > summary:hover { background: #f8fafc; }
        details.card[open] > summary { border-bottom: 1px solid #f1f5f9; border-radius: .75rem .75rem 0 0; }

        /* Form controls: a visible border at rest, a clear ring when focused. */
        main input:not([type="file"]):not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
        main select, main textarea { border-color: #cbd5e1; background: #fff; color: #0f172a; }
        main input:not([type="file"]):not([type="checkbox"]):not([type="radio"]):focus,
        main select:focus, main textarea:focus {
            outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, .18);
        }
        main input::placeholder, main textarea::placeholder { color: #94a3b8; }

        /* A file picker that looks like the rest of the form: the native input
           stays for the browser, a button and the chosen name show for people. */
        .file-field { display: flex; align-items: center; gap: .5rem; min-width: 0; cursor: pointer; }
        .file-field input[type="file"] { position: absolute; width: 1px; height: 1px; opacity: 0; overflow: hidden; }
        .file-field .file-name { font-size: .75rem; color: #64748b; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    </style>
    {{-- Counterpart to @stack('scripts') at the end of the body: without it a
         view's @push('styles') is silently dropped. --}}
    @stack('styles')
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen overflow-x-hidden">
    <nav class="bg-white shadow-sm border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 h-14 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 min-w-0">
                <span class="text-2xl" aria-hidden="true">🩺</span>
                <a href="{{ $homeRoute }}" class="font-bold text-lg text-slate-900 truncate">{{ $brand }}</a>
                @auth
                    <span class="hidden sm:inline-block mx-1 text-xs px-2 py-1 rounded-full whitespace-nowrap
                        {{ auth()->user()->isDoctor() ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700' }}">
                        {{ auth()->user()->isDoctor() ? __('app.roles.doctor') : __('app.roles.assistant') }}
                    </span>
                @endauth
            </div>

            <div class="flex items-center gap-2 lg:gap-3">
                {{-- Laptop and up: every action inline. --}}
                <div class="hidden lg:flex items-center gap-3">
                    @if (auth()->check() && auth()->user()->isDoctor())
                        <a href="{{ route('doctor.dashboard') }}" class="btn btn-secondary btn-sm">{{ $backToMainLabel }}</a>
                    @endif
                    @if ($kioskLinks)
                        {{-- Kiosk launcher: the three in-clinic screens. --}}
                        <details data-menu class="relative">
                            <summary class="btn btn-secondary btn-sm list-none select-none">
                                <span aria-hidden="true">🖥️</span> {{ __('app.display.menu') }}
                                <span class="text-[10px] text-slate-400">▼</span>
                            </summary>
                            <div class="absolute end-0 mt-2 w-64 bg-white border border-slate-200 rounded-xl shadow-lg py-1 z-50">
                                @foreach ($kioskLinks as $link)
                                    <a href="{{ $link['href'] }}" target="_blank" class="block px-4 py-2.5 hover:bg-slate-50">
                                        <div class="text-sm font-medium {{ $link['class'] }}">{{ $link['icon'] }} {{ $link['label'] }}</div>
                                        <div class="text-xs text-slate-400">{{ $link['hint'] }}</div>
                                    </a>
                                @endforeach
                            </div>
                        </details>
                    @endif
                </div>

                @if ($showPatientChat)
                    <x-chat-dropdown side="doctor" />
                @endif

                <a href="{{ route('locale', app()->getLocale() === 'ar' ? 'en' : 'ar') }}"
                   class="btn btn-secondary btn-sm" aria-label="{{ __('app.language') }}">
                    <span aria-hidden="true">🌐</span><span class="hidden sm:inline">{{ __('app.language') }}</span>
                </a>

                @auth
                    <div class="hidden lg:flex items-center gap-3">
                        <span class="text-sm text-slate-600 max-w-[12rem] truncate">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-danger btn-sm">{{ __('app.logout') }}</button>
                        </form>
                    </div>

                    {{-- Phone and tablet: one menu holds what the wide bar shows inline. --}}
                    <details data-menu class="relative lg:hidden">
                        <summary class="btn btn-secondary btn-sm list-none select-none" aria-label="{{ __('app.examine.menu') }}">
                            <span aria-hidden="true" class="text-base leading-none">☰</span>
                        </summary>
                        <div class="absolute end-0 mt-2 w-64 bg-white border border-slate-200 rounded-xl shadow-lg py-1 z-50">
                            <div class="px-4 py-2 text-xs text-slate-500 border-b border-slate-100 truncate">
                                {{ auth()->user()->name }}
                                · {{ auth()->user()->isDoctor() ? __('app.roles.doctor') : __('app.roles.assistant') }}
                            </div>
                            @if (auth()->user()->isDoctor())
                                <a href="{{ route('doctor.dashboard') }}" class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">{{ $backToMainLabel }}</a>
                            @endif
                            @foreach ($kioskLinks as $link)
                                <a href="{{ $link['href'] }}" target="_blank" class="block px-4 py-2.5 hover:bg-slate-50">
                                    <div class="text-sm font-medium {{ $link['class'] }}">{{ $link['icon'] }} {{ $link['label'] }}</div>
                                    <div class="text-xs text-slate-400">{{ $link['hint'] }}</div>
                                </a>
                            @endforeach
                            <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100 mt-1">
                                @csrf
                                <button class="w-full text-start px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">{{ __('app.logout') }}</button>
                            </form>
                        </div>
                    </details>
                @endauth
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-6">
        @if (session('status'))
            <div data-flash="success" class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div data-flash="error" class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div data-flash="error" class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Toasts. The practice area never loaded toastr, so this provides the same
         small API the rest of the app calls — guarded, so a real toastr added
         later simply wins. --}}
    <div id="toast-stack" class="fixed z-[60] top-4 inset-inline-end-4 flex flex-col gap-2 pointer-events-none"
         style="inset-inline-end: 1rem;" aria-live="polite" aria-atomic="false"></div>
    <script>
        window.toastr = window.toastr || (function () {
            var stack = document.getElementById('toast-stack');

            var TONE = {
                success: 'bg-emerald-600',
                error: 'bg-red-600',
                warning: 'bg-amber-500 text-slate-900',
                info: 'bg-slate-800',
            };

            function show(message, tone) {
                if (!stack || !message) return;

                var el = document.createElement('div');
                el.className = 'pointer-events-auto cursor-pointer max-w-sm rounded-xl px-4 py-3 text-sm ' +
                    'font-medium text-white shadow-lg transition-opacity duration-300 ' + (TONE[tone] || TONE.info);
                el.setAttribute('role', tone === 'error' ? 'alert' : 'status');
                el.textContent = message;

                function dismiss() {
                    el.style.opacity = '0';
                    setTimeout(function () { el.remove(); }, 300);
                }

                el.addEventListener('click', dismiss);
                stack.appendChild(el);
                // Errors linger: they are usually something to act on.
                setTimeout(dismiss, tone === 'error' ? 8000 : 4000);
            }

            return {
                success: function (m) { show(m, 'success'); },
                error: function (m) { show(m, 'error'); },
                warning: function (m) { show(m, 'warning'); },
                info: function (m) { show(m, 'info'); },
            };
        })();
    </script>

    <script>
        function toggle(id) {
            document.getElementById(id).classList.toggle('hidden');
        }

        // Styled file pickers: show what was chosen. Delegated from the document,
        // so it keeps working after a screen swaps its <main> in the background.
        document.addEventListener('change', function (e) {
            var input = e.target;
            if (!input || input.type !== 'file') return;
            var field = input.closest('.file-field');
            var name = field && field.querySelector('.file-name');
            if (!name) return;
            var names = Array.prototype.map.call(input.files || [], function (f) { return f.name; });
            name.textContent = names.length ? names.join(', ') : (name.dataset.empty || '');
        });

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
