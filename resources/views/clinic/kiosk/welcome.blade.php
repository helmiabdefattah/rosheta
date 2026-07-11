@extends('clinic.layouts.kiosk')
@section('title', __('app.kiosk.title'))

@section('content')
    @unless (session()->has('locale'))
        {{-- Step 1: language choice — two big buttons, shown until a language is picked --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl font-extrabold text-slate-900">{{ __('app.kiosk.choose_language') }}</h1>
        </div>

        <div class="grid gap-4" dir="ltr">
            <a href="{{ route('locale', 'ar') }}"
               class="block w-full py-8 text-3xl font-extrabold rounded-2xl bg-indigo-600 text-white text-center hover:bg-indigo-700 active:scale-95 transition">
                العربية
            </a>
            <a href="{{ route('locale', 'en') }}"
               class="block w-full py-8 text-3xl font-extrabold rounded-2xl bg-slate-100 text-slate-900 text-center hover:bg-slate-200 active:scale-95 transition">
                English
            </a>
        </div>
    @else
    @if (session()->has('kiosk_ticket_number'))
        {{-- Check-in succeeded and the ticket was sent to the clinic's printer. --}}
        <div class="mb-6 rounded-2xl bg-emerald-50 border-2 border-emerald-200 p-6 text-center">
            <div class="text-2xl font-extrabold text-emerald-800">{{ __('app.kiosk.printed_title') }}</div>
            <div class="mt-2 text-lg text-emerald-700">{{ __('app.kiosk.printed_number') }}</div>
            <div class="mt-1 text-6xl font-black text-emerald-900">{{ session('kiosk_ticket_number') }}</div>
        </div>
    @endif
    {{-- Step 2: phone check-in (shown once a language has been chosen) --}}
    <div class="text-center mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900">{{ __('app.kiosk.welcome') }}</h1>
        <p class="mt-2 text-lg text-slate-500">{{ __('app.kiosk.enter_phone') }}</p>
    </div>

    <form method="POST" action="{{ route('practice.kiosk.lookup', $clinic) }}" id="kioskForm">
        @csrf
        <input
            type="tel"
            name="phone"
            id="phone"
            inputmode="numeric"
            autocomplete="off"
            value="{{ old('phone') }}"
            placeholder="01XXXXXXXXX"
            class="w-full text-center tracking-widest text-3xl font-bold py-5 rounded-2xl border-2 border-slate-200 focus:border-indigo-500 focus:ring-0 outline-none"
            dir="ltr"
            required
            autofocus>

        {{-- On-screen keypad for touch kiosks --}}
        <div class="grid grid-cols-3 gap-3 mt-6" id="keypad">
            @foreach (['1','2','3','4','5','6','7','8','9'] as $k)
                <button type="button" data-key="{{ $k }}"
                        class="py-5 text-2xl font-bold rounded-2xl bg-slate-100 hover:bg-slate-200 active:scale-95 transition">{{ $k }}</button>
            @endforeach
            <button type="button" data-action="clear"
                    class="py-5 text-xl font-bold rounded-2xl bg-amber-100 text-amber-700 hover:bg-amber-200 active:scale-95 transition">{{ __('app.kiosk.clear') }}</button>
            <button type="button" data-key="0"
                    class="py-5 text-2xl font-bold rounded-2xl bg-slate-100 hover:bg-slate-200 active:scale-95 transition">0</button>
            <button type="button" data-action="back"
                    class="py-5 text-2xl font-bold rounded-2xl bg-slate-100 hover:bg-slate-200 active:scale-95 transition">⌫</button>
        </div>

        <button type="submit"
                class="w-full mt-7 py-5 text-2xl font-extrabold rounded-2xl bg-indigo-600 text-white hover:bg-indigo-700 active:scale-95 transition">
            {{ __('app.kiosk.continue') }} →
        </button>
    </form>

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
    </script>
    @endunless
@endsection
