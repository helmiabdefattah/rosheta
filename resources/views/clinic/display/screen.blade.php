@php $isAr = app()->getLocale() === 'ar'; @endphp
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
<body class="text-white select-none">
    {{-- One-time overlay: a tap unlocks browser audio + fullscreen for the screensaver. --}}
    <div id="starter"
         class="fixed inset-0 z-50 flex flex-col items-center justify-center gap-6 bg-slate-950/95 cursor-pointer">
        <div class="text-7xl">📺</div>
        <div class="text-2xl font-semibold text-slate-200">{{ __('app.display.title') }}</div>
        <div class="text-lg text-slate-400 animate-pulse">{{ __('app.display.tap_to_start') }}</div>
    </div>

    {{-- Clock --}}
    <div class="fixed top-6 end-8 text-right">
        <div id="clock" class="text-3xl font-bold tabular-nums text-slate-300"></div>
        <div class="text-sm text-slate-500">{{ $clinic->name ?? config('app.name') }}</div>
    </div>

    {{-- Main stage --}}
    <div class="h-full w-full flex items-center justify-center px-6">
        <div class="drift text-center">
            <div class="text-xl md:text-3xl font-semibold uppercase tracking-[0.3em] text-amber-400 mb-8">
                {{ __('app.display.title') }}
            </div>

            {{-- Active call: only the appointment sort (queue) number — no patient data. --}}
            <div id="active" class="hidden">
                <div class="text-slate-400 text-2xl md:text-4xl mb-2">{{ __('app.display.queue_label') }}</div>
                <div id="queue" class="font-extrabold leading-none text-amber-300 text-[16rem] md:text-[22rem]">—</div>
            </div>

            {{-- Idle state --}}
            <div id="idle">
                <div class="text-[10rem] md:text-[14rem] leading-none text-slate-700 font-extrabold">—</div>
                <div class="mt-4 text-2xl md:text-3xl text-slate-500 italic">{{ __('app.display.waiting') }}</div>
            </div>
        </div>
    </div>

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

    {{-- Advance the queue: complete the current patient, call the next one,
         and play the same Arabic announcement the dashboard uses. Shown only
         when enabled from the assistant dashboard, and never on the check-in
         display (that screen is for patients, not staff). --}}
    @if ((! $clinic || $clinic->display_show_next_button) && ! request()->boolean('checkin'))
    <button id="next-btn" type="button"
            class="hidden fixed bottom-8 inset-x-0 mx-auto w-fit z-40 px-8 py-4 rounded-2xl
                   bg-amber-500 hover:bg-amber-400 active:scale-95 transition
                   text-slate-900 text-2xl md:text-3xl font-bold shadow-lg shadow-amber-900/40"
            style="cursor: pointer;">
        ⏭ {{ __('app.display.next') }}
    </button>
    @endif

    <script>
        (function () {
            var LANG = @json(app()->getLocale());
            var SPEECH_LANG = LANG === 'ar' ? 'ar-SA' : 'en-US';
            var VOICE_PREFIX = LANG === 'ar' ? 'ar' : 'en';
            var CURRENT_URL = @json(route('practice.display.current', $clinic));
            var NEXT_URL = @json(route('practice.display.next', $clinic));
            var KIOSK_URL = @json(route('practice.kiosk.welcome', $clinic));
            var VOICES_BASE = @json(asset('storage/voices/ar'));
            var CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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
                nextBtn: document.getElementById('next-btn'),
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

            // Complete the current patient and call the next one, then let the
            // normal render path announce them (lastId changes => announce()).
            function advance() {
                if (!audioReady) return;
                els.nextBtn.disabled = true;
                fetch(NEXT_URL, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    cache: 'no-store',
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) { render(data.current); })
                    .catch(function () { /* a later poll will reconcile */ })
                    .finally(function () { els.nextBtn.disabled = false; });
            }

            function poll() {
                fetch(CURRENT_URL, { headers: { 'Accept': 'application/json' }, cache: 'no-store' })
                    .then(function (r) { return r.json(); })
                    .then(function (data) { render(data.current); })
                    .catch(function () { /* keep showing last value on transient errors */ });
            }

            // Unlock audio + go full-screen on first interaction (browser policy).
            els.starter.addEventListener('click', function () {
                audioReady = true;
                if ('speechSynthesis' in window) {
                    // A muted utterance primes the engine on some browsers.
                    var warm = new SpeechSynthesisUtterance(' ');
                    warm.volume = 0; window.speechSynthesis.speak(warm);
                }
                var el = document.documentElement;
                if (el.requestFullscreen) { el.requestFullscreen().catch(function () {}); }
                els.starter.style.display = 'none';
                if (els.nextBtn) { els.nextBtn.classList.remove('hidden'); }   // reveal once audio is unlocked
                if (els.kioskBtn) { els.kioskBtn.classList.remove('hidden'); }
                poll();
            });

            if (els.nextBtn) { els.nextBtn.addEventListener('click', advance); }

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
</body>
</html>
