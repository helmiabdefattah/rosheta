@php
    $isAr = app()->getLocale() === 'ar';
    // The clinic's doctor, shown on the counter so patients see whose room this is.
    $displayDoctor = $clinic?->doctor;
    $doctorPhoto = $displayDoctor?->getFirstMediaUrl('profile_image') ?: null;
    $brandName = $isAr ? 'مستشفى-أون' : 'mostashfaOn';
    $brandHost = preg_replace('#^https?://#', '', rtrim(config('app.url'), '/'));
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('app.display.title') }} &middot; {{ $clinic->name ?? config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html, body { height: 100%; margin: 0; overflow: hidden; }
        body { background: radial-gradient(circle at 30% 20%, #1e293b 0%, #0f172a 55%, #020617 100%); cursor: none; }
        /* Gentle drift to prevent screen burn-in on always-on displays. */
        @keyframes drift {
            0%   { transform: translate(0, 0); }
            25%  { transform: translate(14px, -10px); }
            50%  { transform: translate(-10px, 12px); }
            75%  { transform: translate(10px, 8px); }
            100% { transform: translate(0, 0); }
        }
        .drift { animation: drift 32s ease-in-out infinite; }
        @keyframes pulse-in {
            0%   { transform: scale(0.6); opacity: 0; }
            60%  { transform: scale(1.08); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }
        .pulse-in { animation: pulse-in 0.7s cubic-bezier(.2,.8,.2,1); }
    </style>
</head>
<body class="h-full flex flex-col text-white select-none">
    {{-- One-time overlay: a tap unlocks browser audio + fullscreen for the screensaver. --}}
    <div id="starter"
         class="fixed inset-0 z-50 flex flex-col items-center justify-center gap-6 bg-slate-950/95 cursor-pointer">
        <div class="text-7xl">📺</div>
        <div class="text-2xl font-semibold text-slate-200">{{ __('app.display.title') }}</div>
        <div class="text-lg text-slate-400 animate-pulse">{{ __('app.display.tap_to_start') }}</div>
    </div>

    {{-- Header: whose room this is, and the time --}}
    <header class="shrink-0 flex items-start justify-between gap-6 px-8 pt-6">
        <div class="flex items-center gap-5 min-w-0">
            @if ($doctorPhoto)
                <img src="{{ $doctorPhoto }}" alt=""
                     class="w-24 h-24 xl:w-32 xl:h-32 rounded-full object-cover border-4 border-white/25 shadow-2xl shrink-0">
            @endif
            <div class="min-w-0">
                <div class="text-4xl xl:text-6xl font-extrabold leading-tight truncate">
                    {{ $clinic->name ?? $brandName }}
                </div>
                @if ($displayDoctor?->name)
                    {{-- Printed as stored: every doctor name in this system already
                         carries its own title ("د/ …"), so adding one doubles it. --}}
                    <div class="text-2xl xl:text-3xl text-slate-300 mt-1 truncate">
                        {{ $displayDoctor->name }}
                    </div>
                @endif
            </div>
        </div>

        <div id="clock" class="text-3xl xl:text-4xl font-bold tabular-nums text-slate-300 shrink-0"></div>
    </header>

    {{-- Main stage. The number is sized off the viewport height so it fills a
         tablet in landscape and still fits a short one without being cropped. --}}
    <main class="flex-1 min-h-0 w-full flex items-center justify-center px-6">
        <div class="drift text-center">
            <div class="text-xl md:text-3xl font-semibold uppercase tracking-[0.3em] text-amber-400 mb-4">
                {{ __('app.display.title') }}
            </div>

            {{-- Active call: only the appointment sort (queue) number — no patient data. --}}
            <div id="active" class="hidden">
                <div class="text-slate-400 text-2xl md:text-4xl mb-1">{{ __('app.display.queue_label') }}</div>
                <div id="queue" class="font-extrabold leading-none text-amber-300"
                     style="font-size: clamp(6rem, 34vh, 20rem);">—</div>
            </div>

            {{-- Idle state --}}
            <div id="idle">
                <div class="leading-none text-slate-700 font-extrabold" style="font-size: clamp(5rem, 26vh, 14rem);">—</div>
                <div class="mt-2 text-2xl md:text-3xl text-slate-500 italic">{{ __('app.display.waiting') }}</div>
            </div>
        </div>
    </main>

    {{-- The platform behind the screen --}}
    <footer class="shrink-0 flex items-center justify-end gap-4 px-8 pb-6 opacity-80">
        <img src="{{ asset('images/mo-logo.png') }}" alt="" class="h-14 xl:h-20 w-auto object-contain">
        <div class="leading-tight text-end">
            <div class="text-2xl xl:text-3xl font-bold">{{ $brandName }}</div>
            @if ($brandHost)
                <div class="text-base xl:text-lg text-slate-400">{{ $brandHost }}</div>
            @endif
        </div>
    </footer>

    {{-- Open the self-service kiosk to check a patient in. Remembers this
         display so the printed ticket can return here (see the ticket view).
         Revealed once the starter overlay is dismissed. Shown only when the
         display is opened with ?checkin=1 (the "check-in" launch button). --}}
    @if (request()->boolean('checkin'))
    <button id="kiosk-btn" type="button"
            class="hidden fixed bottom-8 start-8 z-40 px-6 py-4 rounded-2xl
                   bg-indigo-500 hover:bg-indigo-400 active:scale-95 transition
                   text-white text-2xl md:text-3xl font-bold shadow-lg shadow-indigo-900/40"
            style="cursor: pointer;">
        🎫 {{ __('app.display.check_in') }}
    </button>
    @endif

    {{-- No "call next" button here: this screen is the patient-facing counter.
         The queue is advanced from the staff assistant screen instead. --}}

    <script>
        (function () {
            var LANG = @json(app()->getLocale());
            var SPEECH_LANG = LANG === 'ar' ? 'ar-SA' : 'en-US';
            var VOICE_PREFIX = LANG === 'ar' ? 'ar' : 'en';
            var CURRENT_URL = @json(route('practice.display.current', $clinic));
            var KIOSK_URL = @json(route('practice.kiosk.welcome', $clinic));
            var VOICES_BASE = @json(asset('storage/voices/ar'));
            // Returning from the kiosk: skip the "tap to start" overlay.
            var AUTO_START = @json(request()->boolean('started'));

            // Setting .lang isn't enough — pick a voice whose language matches,
            // otherwise the browser falls back to its default (English) voice.
            function pickVoice() {
                var voices = window.speechSynthesis.getVoices() || [];
                return voices.find(function (v) { return (v.lang || '').toLowerCase().indexOf(VOICE_PREFIX) === 0; })
                    || voices.find(function (v) { return (v.lang || '').toLowerCase().indexOf(VOICE_PREFIX) !== -1; })
                    || null;
            }

            var els = {
                starter: document.getElementById('starter'),
                active: document.getElementById('active'),
                idle: document.getElementById('idle'),
                queue: document.getElementById('queue'),
                clock: document.getElementById('clock'),
                kioskBtn: document.getElementById('kiosk-btn'),
            };

            var lastId = null;     // appointment id last shown — change => re-announce
            var audioReady = false;

            function tick() {
                var d = new Date();
                els.clock.textContent =
                    String(d.getHours()).padStart(2, '0') + ':' +
                    String(d.getMinutes()).padStart(2, '0') + ':' +
                    String(d.getSeconds()).padStart(2, '0');
            }
            setInterval(tick, 1000); tick();

            function speak(queueNumber) {
                if (!audioReady || !('speechSynthesis' in window)) return;
                var voice = pickVoice();
                window.speechSynthesis.cancel();
                for (var i = 0; i < 2; i++) {   // say the number twice for the room
                    var u = new SpeechSynthesisUtterance(String(queueNumber));
                    u.lang = SPEECH_LANG;
                    u.rate = 0.9;
                    if (voice) u.voice = voice;
                    window.speechSynthesis.speak(u);
                }
            }

            // Announce a queue number. In Arabic we play the pre-recorded clips
            // (main.mp3 then {queue}.mp3) — same as the dashboard's "start
            // examination" button — and fall back to speech synthesis otherwise.
            function announce(queueNumber) {
                if (!audioReady) return;
                if (LANG === 'ar') {
                    var clips = [VOICES_BASE + '/main.mp3', VOICES_BASE + '/' + queueNumber + '.mp3'];
                    var i = 0, clipsWork = false, spoken = false;
                    function fallback() { if (!clipsWork && !spoken) { spoken = true; speak(queueNumber); } }
                    (function playNext() {
                        if (i >= clips.length) return;
                        var audio = new Audio(clips[i++]);
                        audio.oncanplaythrough = function () { clipsWork = true; };
                        audio.onended = playNext;
                        audio.onerror = fallback;   // clips missing → speak instead
                        var p = audio.play();
                        if (p && p.catch) { p.catch(fallback); }
                    })();
                    return;
                }
                speak(queueNumber);
            }

            function render(c) {
                if (!c) {
                    els.active.classList.add('hidden');
                    els.idle.classList.remove('hidden');
                    lastId = null;
                    return;
                }
                els.idle.classList.add('hidden');
                els.active.classList.remove('hidden');
                var number = (c.sort_number != null) ? c.sort_number : c.queue_number;
                els.queue.textContent = number;

                if (c.id !== lastId) {
                    lastId = c.id;
                    els.active.classList.remove('pulse-in');
                    void els.active.offsetWidth;          // restart the animation
                    els.active.classList.add('pulse-in');
                    announce(number);
                }
            }

            function poll() {
                fetch(CURRENT_URL, { headers: { 'Accept': 'application/json' }, cache: 'no-store' })
                    .then(function (r) { return r.json(); })
                    .then(function (data) { render(data.current); })
                    .catch(function () { /* keep showing last value on transient errors */ });
            }

            // Reveal the display: unlock audio, go full-screen, show buttons,
            // start polling. On a real tap [userGesture] audio/fullscreen work;
            // on AUTO_START (return from kiosk) audio stays muted until the next
            // tap, but the screen is ready without asking to tap again.
            function startDisplay(userGesture) {
                audioReady = true;
                if (userGesture && 'speechSynthesis' in window) {
                    // A muted utterance primes the engine on some browsers.
                    var warm = new SpeechSynthesisUtterance(' ');
                    warm.volume = 0; window.speechSynthesis.speak(warm);
                }
                if (userGesture) {
                    var el = document.documentElement;
                    if (el.requestFullscreen) { el.requestFullscreen().catch(function () {}); }
                }
                els.starter.style.display = 'none';
                if (els.kioskBtn) { els.kioskBtn.classList.remove('hidden'); }
                poll();
            }

            els.starter.addEventListener('click', function () { startDisplay(true); });
            if (AUTO_START) { startDisplay(false); }

            // Go to the kiosk, remembering this display so the printed ticket
            // returns here afterwards (sessionStorage survives the same-tab
            // navigation through the kiosk flow; read by the ticket view).
            if (els.kioskBtn) {
                els.kioskBtn.addEventListener('click', function () {
                    try { sessionStorage.setItem('kioskReturnUrl', location.href); } catch (e) {}
                    location.href = KIOSK_URL;
                });
            }

            setInterval(poll, 3000);
        })();
    </script>
    @include('clinic.partials.keep-awake')
</body>
</html>
