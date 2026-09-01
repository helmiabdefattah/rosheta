@extends('clinic.layouts.kiosk')
@section('title', __('app.kiosk.rx_title'))
@section('card-width', 'max-w-xl short:max-w-3xl')

@section('content')
    @php
        // Set only after a lookup: printed / already / none / failed.
        $result = $result ?? null;
    @endphp

    @if ($result)
        @php
            $tone = match ($result) {
                'printed' => ['bg-emerald-50', 'border-emerald-200', 'text-emerald-800', '🖨️'],
                'already' => ['bg-amber-50', 'border-amber-200', 'text-amber-800', '⚠️'],
                default => ['bg-slate-50', 'border-slate-200', 'text-slate-700', 'ℹ️'],
            };
            $heading = __('app.kiosk.rx_'.$result);
            $hint = __('app.kiosk.rx_'.$result.'_hint');
        @endphp

        <div class="{{ $tone[0] }} {{ $tone[1] }} {{ $tone[2] }} border-2 rounded-2xl p-8 short:p-5 text-center">
            <div class="text-6xl short:text-4xl mb-3 short:mb-2">{{ $tone[3] }}</div>
            <div class="text-2xl short:text-xl font-extrabold">{{ $heading }}</div>
            <p class="mt-2 short:mt-1 text-lg short:text-sm">{{ $hint }}</p>

            @if ($result === 'printed' && ($code ?? null))
                <p class="mt-3 short:mt-2 text-sm font-mono tracking-wide opacity-70">{{ $code }}</p>
            @endif
            @if ($result === 'already' && ($printedAt ?? null))
                <p class="mt-2 text-sm opacity-70">{{ $printedAt->translatedFormat('d M Y · H:i') }}</p>
            @endif
        </div>

        <a href="{{ route('practice.kiosk.prescription', $clinic) }}"
           class="mt-6 short:mt-3 block w-full py-5 short:py-3 text-center text-2xl short:text-lg font-extrabold rounded-2xl bg-indigo-600 text-white hover:bg-indigo-700 active:scale-95 transition">
            {{ __('app.kiosk.rx_again') }}
        </a>
    @else
        <div class="text-center mb-8 short:mb-3">
            <div class="text-5xl mb-3 short:hidden">🧾</div>
            <h1 class="text-3xl short:text-xl font-extrabold text-slate-900">{{ __('app.kiosk.rx_title') }}</h1>
            <p class="mt-2 short:mt-0.5 text-lg short:text-sm text-slate-500">{{ __('app.kiosk.rx_enter_phone') }}</p>
        </div>

        <form method="POST" action="{{ route('practice.kiosk.prescription.print', $clinic) }}"
              class="short:grid short:grid-cols-2 short:gap-6 short:items-start">
            @csrf

            {{-- Keypad first, so it keeps its side in both directions. --}}
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

            <div class="short:order-1 short:flex short:flex-col short:h-full">
                <input type="tel" name="phone" id="phone" inputmode="numeric" autocomplete="off"
                       value="{{ old('phone') }}" placeholder="01XXXXXXXXX" dir="ltr" required autofocus
                       class="w-full text-center tracking-widest text-3xl short:text-2xl font-bold py-5 short:py-3 rounded-2xl border-2 border-slate-200 focus:border-indigo-500 focus:ring-0 outline-none">

                <button type="submit"
                        class="w-full mt-7 short:mt-3 py-5 short:py-3 text-2xl short:text-xl font-extrabold rounded-2xl bg-indigo-600 text-white hover:bg-indigo-700 active:scale-95 transition">
                    {{ __('app.kiosk.rx_print') }}
                </button>

                <a href="{{ route('practice.kiosk.welcome', $clinic) }}"
                   class="w-full mt-6 short:mt-2 py-4 short:py-2.5 text-center text-xl short:text-base font-bold rounded-2xl bg-slate-100 text-slate-600 hover:bg-slate-200 active:scale-95 transition">
                    {{ __('app.kiosk.cancel') }}
                </a>
            </div>
        </form>

        <script>
            (function () {
                var input = document.getElementById('phone');
                document.getElementById('keypad').addEventListener('click', function (e) {
                    var btn = e.target.closest('button');
                    if (!btn) return;
                    if (btn.dataset.key) input.value += btn.dataset.key;
                    else if (btn.dataset.action === 'back') input.value = input.value.slice(0, -1);
                    else if (btn.dataset.action === 'clear') input.value = '';
                    input.focus();
                });
            })();
        </script>
    @endif
@endsection
