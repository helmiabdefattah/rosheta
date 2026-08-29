@extends('clinic.layouts.kiosk')
@section('title', __('app.kiosk.register_title'))
{{-- Wider on a short screen so the fields and the keyboard sit side by side. --}}
@section('card-width', 'max-w-xl short:max-w-4xl')

@php
    $isAr = app()->getLocale() === 'ar';

    // Standard Arabic 101 / QWERTY rows. Kept here rather than in the script so
    // the keys are real markup — they stay tappable if the script fails to run.
    $keyRows = $isAr
        ? [
            ['ض','ص','ث','ق','ف','غ','ع','ه','خ','ح','ج','د'],
            ['ش','س','ي','ب','ل','ا','ت','ن','م','ك','ط'],
            ['ئ','ء','ؤ','ر','ى','ة','و','ز','ظ','أ','إ','آ'],
        ]
        : [
            ['q','w','e','r','t','y','u','i','o','p'],
            ['a','s','d','f','g','h','j','k','l'],
            ['z','x','c','v','b','n','m'],
        ];
@endphp

@section('content')
    <div class="text-center mb-8 short:mb-3">
        <div class="text-5xl mb-3 short:hidden">📝</div>
        <h1 class="text-3xl short:text-xl font-extrabold text-slate-900">{{ __('app.kiosk.new_patient') }}</h1>
        <p class="mt-2 short:mt-0.5 text-lg short:text-sm text-slate-500">{{ __('app.kiosk.register_intro') }}</p>
    </div>

    <form method="POST" action="{{ route('practice.kiosk.store', $clinic) }}"
          class="space-y-5 short:space-y-0 short:grid short:grid-cols-2 short:gap-5 short:items-start">
        @csrf

        {{-- Fields column --}}
        <div class="space-y-5 short:space-y-2.5">
            <div>
                <label for="kiosk-name" class="block text-sm short:text-xs font-semibold text-slate-600 mb-1 short:mb-0.5">{{ __('app.appointment.patient_name') }}</label>
                <input type="text" name="name" id="kiosk-name" value="{{ old('name') }}" required autofocus
                       data-kbd="text"
                       class="w-full text-xl short:text-lg py-4 short:py-2.5 px-4 short:px-3 rounded-2xl short:rounded-xl border-2 border-slate-200 focus:border-indigo-500 focus:ring-0 outline-none">
            </div>

            {{-- Phone was already entered on the previous step; keep it but hidden. --}}
            <input type="hidden" name="phone" value="{{ old('phone', $phone) }}">

            <div class="grid grid-cols-2 gap-4 short:gap-3">
                <div>
                    <label for="kiosk-gender" class="block text-sm short:text-xs font-semibold text-slate-600 mb-1 short:mb-0.5">{{ __('app.common.gender') }}</label>
                    <select name="gender" id="kiosk-gender"
                            class="w-full text-xl short:text-lg py-4 short:py-2.5 px-4 short:px-3 rounded-2xl short:rounded-xl border-2 border-slate-200 focus:border-indigo-500 focus:ring-0 outline-none bg-white">
                        <option value="">—</option>
                        <option value="male" @selected(old('gender') === 'male')>{{ __('app.genders.male') }}</option>
                        <option value="female" @selected(old('gender') === 'female')>{{ __('app.genders.female') }}</option>
                    </select>
                </div>
                <div>
                    <label for="kiosk-age" class="block text-sm short:text-xs font-semibold text-slate-600 mb-1 short:mb-0.5">{{ __('app.kiosk.age') }}</label>
                    {{-- type=text + inputmode=numeric, not type=number: the keys below
                         append characters, and a number input rejects that. --}}
                    <input type="text" name="age" id="kiosk-age" value="{{ old('age') }}" inputmode="numeric"
                           maxlength="3" dir="ltr" data-kbd="digits"
                           class="w-full text-xl short:text-lg py-4 short:py-2.5 px-4 short:px-3 rounded-2xl short:rounded-xl border-2 border-slate-200 focus:border-indigo-500 focus:ring-0 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-sm short:text-xs font-semibold text-slate-600 mb-2 short:mb-1">{{ __('app.kiosk.choose_type') }}</label>
                <div class="grid grid-cols-2 gap-3 short:gap-2">
                    <label class="flex items-center gap-3 short:gap-2 py-4 short:py-2.5 px-4 short:px-3 rounded-2xl short:rounded-xl border-2 border-slate-200 cursor-pointer has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50">
                        <input type="radio" name="type" value="examination" class="h-5 w-5 short:h-4 short:w-4" checked>
                        <span class="text-lg short:text-sm font-semibold">🩺 {{ __('app.types.examination') }}</span>
                    </label>
                    <label class="flex items-center gap-3 short:gap-2 py-4 short:py-2.5 px-4 short:px-3 rounded-2xl short:rounded-xl border-2 border-slate-200 cursor-pointer has-[:checked]:border-violet-500 has-[:checked]:bg-violet-50">
                        <input type="radio" name="type" value="consultation" class="h-5 w-5 short:h-4 short:w-4">
                        <span class="text-lg short:text-sm font-semibold">💬 {{ __('app.types.consultation') }}</span>
                    </label>
                </div>
            </div>

            <button type="submit"
                    class="w-full mt-2 short:mt-1 py-5 short:py-3 text-2xl short:text-lg font-extrabold rounded-2xl bg-indigo-600 text-white hover:bg-indigo-700 active:scale-95 transition">
                {{ __('app.kiosk.register_book') }} →
            </button>
        </div>

        {{-- On-screen keyboard. Kiosk tablets are often locked down with the
             system keyboard disabled, so the screen ships its own — the same
             approach the phone step already takes with its keypad. The device
             keyboard is left enabled, so whichever works is fine. --}}
        <div id="kiosk-keyboard" class="mt-6 short:mt-0 select-none">
            <p class="text-xs text-slate-400 mb-2 short:mb-1 text-center">{{ __('app.kiosk.keyboard_hint') }}</p>

            <div class="grid grid-cols-10 gap-1.5 short:gap-1 mb-1.5 short:mb-1" dir="ltr">
                @foreach (['1','2','3','4','5','6','7','8','9','0'] as $d)
                    <button type="button" data-key="{{ $d }}"
                            class="py-3 short:py-2 text-lg short:text-base font-bold rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-95 transition">{{ $d }}</button>
                @endforeach
            </div>

            @foreach ($keyRows as $row)
                <div class="flex justify-center gap-1.5 short:gap-1 mb-1.5 short:mb-1">
                    @foreach ($row as $k)
                        <button type="button" data-key="{{ $k }}"
                                class="flex-1 min-w-0 py-3 short:py-2 text-lg short:text-base font-bold rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-95 transition">{{ $k }}</button>
                    @endforeach
                </div>
            @endforeach

            <div class="flex gap-1.5 short:gap-1">
                <button type="button" data-key=" "
                        class="flex-1 py-3 short:py-2 text-base short:text-sm font-bold rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-95 transition">{{ __('app.kiosk.space') }}</button>
                <button type="button" data-action="back"
                        class="w-20 short:w-16 py-3 short:py-2 text-lg short:text-base font-bold rounded-xl bg-slate-200 hover:bg-slate-300 active:scale-95 transition">⌫</button>
                <button type="button" data-action="clear"
                        class="w-24 short:w-20 py-3 short:py-2 text-base short:text-sm font-bold rounded-xl bg-amber-100 text-amber-700 hover:bg-amber-200 active:scale-95 transition">{{ __('app.kiosk.clear') }}</button>
            </div>
        </div>
    </form>

    <a href="{{ route('practice.kiosk.welcome', $clinic) }}"
       class="block text-center mt-6 short:mt-2 text-slate-400 hover:text-slate-600 text-lg short:text-sm">
        {{ __('app.common.back') }}
    </a>

    <script>
        (function () {
            var keyboard = document.getElementById('kiosk-keyboard');
            var fields = Array.prototype.slice.call(document.querySelectorAll('[data-kbd]'));
            if (!keyboard || !fields.length) return;

            var active = fields[0];

            /**
             * The field a key press goes to: whichever of ours currently holds
             * focus, else the last one that did. Reading activeElement rather
             * than trusting the focus event alone keeps this right even when a
             * field is focused programmatically.
             */
            function target() {
                var el = document.activeElement;
                if (el && el.dataset && el.dataset.kbd) {
                    active = el;
                }
                return active;
            }

            function mark() {
                fields.forEach(function (f) {
                    f.classList.toggle('border-indigo-500', f === active);
                });
            }

            fields.forEach(function (f) {
                f.addEventListener('focus', function () { active = f; mark(); });
            });
            mark();

            keyboard.addEventListener('mousedown', function (e) {
                // Keep the caret in the field: focus must not move to the button.
                if (e.target.closest('button')) e.preventDefault();
            });

            keyboard.addEventListener('click', function (e) {
                var btn = e.target.closest('button');
                if (!btn) return;

                var field = target();
                var action = btn.dataset.action;

                if (action === 'clear') {
                    field.value = '';
                } else if (action === 'back') {
                    field.value = field.value.slice(0, -1);
                } else if (btn.dataset.key !== undefined) {
                    var key = btn.dataset.key;
                    // The age field takes digits only, whichever key was pressed.
                    if (field.dataset.kbd === 'digits' && !/^[0-9]$/.test(key)) return;
                    if (field.maxLength > 0 && field.value.length >= field.maxLength) return;
                    field.value += key;
                }

                mark();
                field.dispatchEvent(new Event('input', { bubbles: true }));
                field.focus();
            });
        })();
    </script>
@endsection
