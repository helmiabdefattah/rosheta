@php $isAr = app()->getLocale() === 'ar'; @endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('app.screen.title') }} &middot; {{ $clinic->name ?? config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html, body { height: 100%; margin: 0; }
        body { background: radial-gradient(circle at 30% 20%, #1e293b 0%, #0f172a 55%, #020617 100%); }
        @keyframes pulse-in {
            0%   { transform: scale(0.6); opacity: 0; }
            60%  { transform: scale(1.08); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }
        .pulse-in { animation: pulse-in 0.7s cubic-bezier(.2,.8,.2,1); }
    </style>
</head>
<body class="text-white">
    <div class="h-full flex flex-col">

        {{-- Header: clinic + clock + back to dashboard --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-white/10">
            <div class="flex items-center gap-3 min-w-0">
                <span class="text-2xl">🩺</span>
                <div class="min-w-0">
                    <div class="font-bold truncate">{{ __('app.screen.title') }}</div>
                    <div class="text-xs text-slate-400 truncate">{{ $clinic->name ?? config('app.name') }}</div>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div id="clock" class="text-2xl font-bold tabular-nums text-slate-300"></div>
                <a href="{{ route('practice.assistant.dashboard') }}"
                   class="text-sm px-3 py-1.5 rounded-lg border border-white/20 text-slate-300 hover:bg-white/10 whitespace-nowrap">
                    ← {{ __('app.clinic.title') }}
                </a>
            </div>
        </div>

        <div class="flex-1 grid grid-cols-1 lg:grid-cols-5 gap-6 p-6 min-h-0">

            {{-- Counter --}}
            <div class="lg:col-span-3 flex flex-col items-center justify-center text-center">
                <div class="text-lg md:text-2xl font-semibold uppercase tracking-[0.3em] text-amber-400 mb-6">
                    {{ __('app.display.title') }}
                </div>

                <div id="active" class="hidden">
                    <div class="text-slate-400 text-xl md:text-2xl mb-2">{{ __('app.display.queue_label') }}</div>
                    <div id="queue" class="font-extrabold leading-none text-amber-300 text-[10rem] md:text-[16rem]">—</div>
                    <div id="current-name" class="text-2xl md:text-3xl font-semibold text-slate-200 mt-2"></div>
                </div>

                <div id="idle">
                    <div class="text-[8rem] md:text-[12rem] leading-none text-slate-700 font-extrabold">—</div>
                    <div class="mt-2 text-xl md:text-2xl text-slate-500 italic">{{ __('app.assistant.no_under_examination') }}</div>
                </div>

                {{-- Actions: check in (like the interactive screen) + call next --}}
                <div class="flex flex-wrap items-center justify-center gap-4 mt-10">
                    <button id="kiosk-btn" type="button"
                            class="px-6 py-4 rounded-2xl bg-indigo-500 hover:bg-indigo-400 active:scale-95 transition
                                   text-white text-xl md:text-2xl font-bold shadow-lg shadow-indigo-900/40">
                        🎫 {{ __('app.display.check_in') }}
                    </button>
                    <button id="next-btn" type="button"
                            class="px-8 py-4 rounded-2xl bg-amber-500 hover:bg-amber-400 active:scale-95 transition
                                   text-slate-900 text-xl md:text-2xl font-bold shadow-lg shadow-amber-900/40
                                   disabled:opacity-50 disabled:cursor-not-allowed">
                        ⏭ {{ __('app.display.next') }}
                    </button>
                </div>
            </div>

            {{-- Today's queue --}}
            <div class="lg:col-span-2 bg-white/5 border border-white/10 rounded-2xl flex flex-col min-h-0">
                <div class="px-5 py-3 border-b border-white/10 flex items-center justify-between">
                    <h2 class="font-semibold text-slate-200">{{ __('app.screen.queue') }}</h2>
                    <span id="queue-count" class="text-xs text-slate-400 bg-white/10 px-2 py-0.5 rounded-full">0</span>
                </div>
                <ul id="queue-list" class="flex-1 overflow-y-auto divide-y divide-white/5"></ul>
                <div id="queue-empty" class="hidden p-8 text-center text-slate-500 italic">{{ __('app.screen.empty') }}</div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var QUEUE_URL = @json(route('practice.screen.queue'));
            var NEXT_URL = @json(route('practice.display.next', $clinic));
            var KIOSK_URL = @json(route('practice.kiosk.welcome', $clinic));
            var CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            var STATUS_COLORS = {
                scheduled: 'bg-slate-500/20 text-slate-300',
                under_examination: 'bg-amber-500/20 text-amber-300',
                completed: 'bg-emerald-500/20 text-emerald-300',
                cancelled: 'bg-red-500/20 text-red-300',
                escaped: 'bg-orange-500/20 text-orange-300',
                pending: 'bg-yellow-500/20 text-yellow-300',
                confirmed: 'bg-blue-500/20 text-blue-300',
                missed: 'bg-red-500/20 text-red-300',
            };

            var els = {
                active: document.getElementById('active'),
                idle: document.getElementById('idle'),
                queue: document.getElementById('queue'),
                currentName: document.getElementById('current-name'),
                list: document.getElementById('queue-list'),
                count: document.getElementById('queue-count'),
                empty: document.getElementById('queue-empty'),
                clock: document.getElementById('clock'),
                nextBtn: document.getElementById('next-btn'),
                kioskBtn: document.getElementById('kiosk-btn'),
            };

            var lastId = null;

            function tick() {
                var d = new Date();
                els.clock.textContent =
                    String(d.getHours()).padStart(2, '0') + ':' +
                    String(d.getMinutes()).padStart(2, '0') + ':' +
                    String(d.getSeconds()).padStart(2, '0');
            }
            setInterval(tick, 1000); tick();

            // The public counter does the voice announcement; this staff screen
            // stays silent so both can be open in the clinic without doubling up.
            function renderCurrent(c) {
                if (!c) {
                    els.active.classList.add('hidden');
                    els.idle.classList.remove('hidden');
                    lastId = null;
                    return;
                }
                els.idle.classList.add('hidden');
                els.active.classList.remove('hidden');
                els.queue.textContent = c.sort_number;
                els.currentName.textContent = c.name || '';

                if (c.id !== lastId) {
                    lastId = c.id;
                    els.active.classList.remove('pulse-in');
                    void els.active.offsetWidth;
                    els.active.classList.add('pulse-in');
                }
            }

            function renderQueue(queue) {
                els.count.textContent = queue.length;
                els.empty.classList.toggle('hidden', queue.length > 0);
                els.list.innerHTML = '';

                queue.forEach(function (p) {
                    var li = document.createElement('li');
                    li.className = 'flex items-center gap-3 px-4 py-3 ' +
                        (p.status === 'under_examination' ? 'bg-amber-500/10' : '');

                    var num = document.createElement('div');
                    num.className = 'w-9 h-9 shrink-0 rounded-full bg-white/10 flex items-center justify-center font-bold text-slate-300';
                    num.textContent = p.sort_number;

                    var info = document.createElement('div');
                    info.className = 'min-w-0 flex-1';
                    var name = document.createElement('div');
                    name.className = 'font-semibold text-slate-100 truncate';
                    name.textContent = p.name || '—';
                    var time = document.createElement('div');
                    time.className = 'text-xs text-slate-400';
                    time.textContent = p.time || '';
                    info.appendChild(name);
                    info.appendChild(time);

                    var badge = document.createElement('span');
                    badge.className = 'text-xs px-2 py-1 rounded-full shrink-0 ' +
                        (STATUS_COLORS[p.status] || 'bg-slate-500/20 text-slate-300');
                    badge.textContent = p.status_label;

                    li.appendChild(num);
                    li.appendChild(info);
                    li.appendChild(badge);
                    els.list.appendChild(li);
                });
            }

            function poll() {
                fetch(QUEUE_URL, { headers: { 'Accept': 'application/json' }, cache: 'no-store' })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        renderCurrent(data.current);
                        renderQueue(data.queue || []);
                    })
                    .catch(function () { /* keep the last render on transient errors */ });
            }

            // Complete whoever is under examination and call the next patient.
            // The waiting-room counter picks the change up on its own poll and
            // announces it out loud.
            els.nextBtn.addEventListener('click', function () {
                els.nextBtn.disabled = true;
                fetch(NEXT_URL, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    cache: 'no-store',
                })
                    .then(function (r) { return r.json(); })
                    .then(function () { poll(); })
                    .catch(function () { /* a later poll will reconcile */ })
                    .finally(function () { els.nextBtn.disabled = false; });
            });

            // Same as the interactive screen: remember where to return so the
            // printed ticket can come back here after the kiosk flow.
            els.kioskBtn.addEventListener('click', function () {
                try { sessionStorage.setItem('kioskReturnUrl', location.href); } catch (e) {}
                location.href = KIOSK_URL;
            });

            poll();
            setInterval(poll, 3000);
        })();
    </script>
    @include('clinic.partials.keep-awake')
</body>
</html>
