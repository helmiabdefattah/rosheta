@extends('clinic.layouts.kiosk')
@section('title', __('app.kiosk.title'))
{{-- Wider card on a short screen: the keypad moves next to the input instead of
     under it, which is what keeps the whole screen inside a landscape tablet. --}}
@section('card-width', 'max-w-xl short:max-w-3xl')

@section('content')
    @unless (session()->has('locale'))
        {{-- Step 1: language choice — two big buttons, shown until a language is picked --}}
        <div class="text-center mb-8 short:mb-4">
            <h1 class="text-3xl short:text-2xl font-extrabold text-slate-900">{{ __('app.kiosk.choose_language') }}</h1>
        </div>

        <div class="grid short:grid-cols-2 gap-4 short:gap-3" dir="ltr">
            <a href="{{ route('locale', 'ar') }}"
               class="block w-full py-8 short:py-6 text-3xl short:text-2xl font-extrabold rounded-2xl bg-indigo-600 text-white text-center hover:bg-indigo-700 active:scale-95 transition">
                العربية
            </a>
            <a href="{{ route('locale', 'en') }}"
               class="block w-full py-8 short:py-6 text-3xl short:text-2xl font-extrabold rounded-2xl bg-slate-100 text-slate-900 text-center hover:bg-slate-200 active:scale-95 transition">
                English
            </a>
        </div>
    @else
    @if (session()->has('kiosk_ticket_number'))
        {{-- Check-in succeeded and the ticket was sent to the clinic's printer. --}}
        <div class="mb-6 short:mb-3 rounded-2xl bg-emerald-50 border-2 border-emerald-200 p-6 short:p-3 text-center">
            <div class="text-2xl short:text-lg font-extrabold text-emerald-800">{{ __('app.kiosk.printed_title') }}</div>
            <div class="mt-2 short:mt-0.5 text-lg short:text-sm text-emerald-700">{{ __('app.kiosk.printed_number') }}</div>
            <div class="mt-1 text-6xl short:text-4xl font-black text-emerald-900">{{ session('kiosk_ticket_number') }}</div>
        </div>
    @endif
    {{-- Step 2: phone check-in (shown once a language has been chosen) --}}
    <div class="text-center mb-8 short:mb-3">
        <h1 class="text-3xl short:text-xl font-extrabold text-slate-900">{{ __('app.kiosk.welcome') }}</h1>
        <p class="mt-2 short:mt-0.5 text-lg short:text-sm text-slate-500">{{ __('app.kiosk.enter_phone') }}</p>
    </div>

    <form method="POST" action="{{ route('practice.kiosk.lookup', $clinic) }}" id="kioskForm"
          class="short:grid short:grid-cols-2 short:gap-6 short:items-start">
        @csrf

        {{-- Keypad column (first, so it stays on the same side in RTL and LTR). --}}
        <div class="short:order-2">
            <div class="grid grid-cols-3 gap-3 short:gap-2 mt-6 short:mt-0" id="keypad">
                @foreach (['1','2','3','4','5','6','7','8','9'] as $k)
                    <button type="button" data-key="{{ $k }}"
                            class="py-5 short:py-2.5 text-2xl short:text-xl font-bold rounded-2xl short:rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-95 transition">{{ $k }}</button>
                @endforeach
                <button type="button" data-action="clear"
                        class="py-5 short:py-2.5 text-xl short:text-base font-bold rounded-2xl short:rounded-xl bg-amber-100 text-amber-700 hover:bg-amber-200 active:scale-95 transition">{{ __('app.kiosk.clear') }}</button>
                <button type="button" data-key="0"
                        class="py-5 short:py-2.5 text-2xl short:text-xl font-bold rounded-2xl short:rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-95 transition">0</button>
                <button type="button" data-action="back"
                        class="py-5 short:py-2.5 text-2xl short:text-xl font-bold rounded-2xl short:rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-95 transition">⌫</button>
            </div>
        </div>

        {{-- Input + actions column. --}}
        <div class="short:order-1 short:flex short:flex-col short:h-full">
            <input
                type="tel"
                name="phone"
                id="phone"
                inputmode="numeric"
                autocomplete="off"
                value="{{ old('phone') }}"
                placeholder="01XXXXXXXXX"
                class="w-full text-center tracking-widest text-3xl short:text-2xl font-bold py-5 short:py-3 rounded-2xl border-2 border-slate-200 focus:border-indigo-500 focus:ring-0 outline-none"
                dir="ltr"
                required
                autofocus>

            <button type="submit"
                    class="w-full mt-7 short:mt-3 py-5 short:py-3 text-2xl short:text-xl font-extrabold rounded-2xl bg-indigo-600 text-white hover:bg-indigo-700 active:scale-95 transition">
                {{ __('app.kiosk.continue') }} →
            </button>

            {{-- Manual reset + idle auto-return to the main kiosk screen --}}
            <button type="button" id="kioskCancelBtn"
                    class="w-full mt-6 short:mt-2 py-4 short:py-2.5 text-xl short:text-base font-bold rounded-2xl bg-slate-100 text-slate-600 hover:bg-slate-200 active:scale-95 transition">
                {{ __('app.kiosk.cancel') }}
            </button>
            <p class="mt-3 short:mt-1.5 text-center text-sm short:text-xs text-slate-400">
                {{ __('app.kiosk.idle_return') }}
                <span id="kioskIdleCount" class="font-bold text-slate-500">30</span>
                {{ __('app.kiosk.seconds') }}
            </p>
        </div>
    </form>

    @php
        $displayUrl = route('practice.display.screen', ['clinic' => $clinic->id, 'checkin' => 1, 'started' => 1]);
    @endphp

    <script>
        const input = document.getElementById('phone');
        document.getElementById('keypad').addEventListener('click', (e) => {
            const btn = e.target.closest('button');
            if (!btn) return;
            if (btn.dataset.key) input.value += btn.dataset.key;
            else if (btn.dataset.action === 'back') input.value = input.value.slice(0, -1);
            else if (btn.dataset.action === 'clear') input.value = '';
            input.focus();
        });

        // Return to the waiting-room display (check-in kiosk) after 30s idle.
        (function () {
            const HOME = @json($displayUrl);
            const SECONDS = 30;
            let remaining = SECONDS;
            const label = document.getElementById('kioskIdleCount');
            const go = () => { window.location.href = HOME; };
            const reset = () => { remaining = SECONDS; if (label) label.textContent = remaining; };
            setInterval(() => {
                remaining -= 1;
                if (label) label.textContent = Math.max(remaining, 0);
                if (remaining <= 0) go();
            }, 1000);
            ['pointerdown', 'keydown', 'input', 'touchstart', 'scroll'].forEach((ev) =>
                document.addEventListener(ev, reset, { passive: true }));
            document.getElementById('kioskCancelBtn').addEventListener('click', go);
        })();
    </script>
    @endunless
@endsection
