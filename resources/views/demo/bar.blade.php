{{--
    Demo bar — shown on every clinic screen while a demo is running.
    Fixed to the top on desktop, to the bottom on mobile (thumb reach).
    Rendered only when the request is inside a demo, so production sessions
    never see it.
--}}
@if (($demoContext ?? null) && $demoContext->isDemo() && $demoContext->sessionId())
    @php
        $demoSession = \App\Models\DemoSession::find($demoContext->sessionId());
        $isAssistant = $demoSession && auth()->id() === $demoSession->assistant_user_id;
    @endphp

    @if ($demoSession)
        <div
            id="demo-bar"
            dir="rtl"
            class="fixed inset-x-0 bottom-0 sm:bottom-auto sm:top-0 z-[60] bg-slate-900 text-white shadow-lg
                   border-t sm:border-t-0 sm:border-b border-slate-700"
        >
            <div class="max-w-7xl mx-auto px-3 py-2 flex flex-wrap items-center justify-between gap-x-3 gap-y-2 text-sm">

                <div class="flex items-center gap-2 min-w-0">
                    <span class="text-lg leading-none">🧪</span>
                    <span class="font-semibold whitespace-nowrap">وضع التجربة</span>
                    <span class="hidden md:inline text-slate-300 text-xs truncate">
                        كل التعديلات ستُمسح بانتهاء التجربة
                    </span>
                    <span
                        id="demo-countdown"
                        data-seconds="{{ $demoSession->secondsRemaining() }}"
                        class="ms-1 px-2 py-0.5 rounded-full bg-slate-800 text-emerald-300 font-mono text-xs tabular-nums whitespace-nowrap"
                    >—:—:—</span>
                </div>

                <div class="flex items-center gap-2 flex-wrap justify-end">
                    <span class="hidden sm:inline text-xs text-slate-400 whitespace-nowrap">
                        أنت الآن: <span class="text-white font-semibold">{{ $isAssistant ? 'المساعد' : 'الطبيب' }}</span>
                    </span>

                    <form method="POST" action="{{ route('demo.switch-role') }}" class="inline">
                        @csrf
                        <button type="submit"
                            class="px-3 py-1.5 rounded-lg bg-slate-700 hover:bg-slate-600 text-xs font-semibold transition-colors whitespace-nowrap">
                            {{ $isAssistant ? 'تحوّل إلى الطبيب ⇄' : 'تحوّل إلى المساعد ⇄' }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('demo.reset') }}" class="inline"
                          onsubmit="return confirm('سيتم مسح كل ما أدخلته وإعادة بناء العيادة من جديد. متابعة؟');">
                        @csrf
                        <button type="submit"
                            class="px-3 py-1.5 rounded-lg bg-slate-700 hover:bg-slate-600 text-xs font-semibold transition-colors whitespace-nowrap">
                            أعد التجربة
                        </button>
                    </form>

                    <a href="{{ route('register') }}"
                       class="px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-900 text-xs font-bold transition-colors whitespace-nowrap">
                        أنشئ حسابك الحقيقي
                    </a>

                    <form method="POST" action="{{ route('demo.end') }}" class="inline"
                          onsubmit="return confirm('سيتم إنهاء التجربة ومسح كل بياناتها نهائياً. متابعة؟');">
                        @csrf
                        <button type="submit"
                            class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-red-600 text-xs font-semibold transition-colors whitespace-nowrap">
                            إنهاء
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Keep the bar from covering the page. --}}
        <div class="h-14 sm:h-0"></div>
        <style>
            @media (min-width: 640px) { body { padding-top: 3.25rem; } }
            @media (max-width: 639px) { body { padding-bottom: 4.5rem; } }
        </style>

        <script>
            (function () {
                var el = document.getElementById('demo-countdown');
                if (!el) return;

                var remaining = parseInt(el.dataset.seconds || '0', 10);

                function pad(n) { return n < 10 ? '0' + n : '' + n; }

                function tick() {
                    if (remaining <= 0) {
                        el.textContent = 'انتهت';
                        // Any request now returns the "demo ended" page anyway.
                        window.location.reload();
                        return;
                    }

                    var h = Math.floor(remaining / 3600);
                    var m = Math.floor((remaining % 3600) / 60);
                    var s = remaining % 60;

                    el.textContent = pad(h) + ':' + pad(m) + ':' + pad(s);
                    remaining--;
                }

                tick();
                setInterval(tick, 1000);
            })();
        </script>
    @endif
@endif
