{{--
    One medicine line on the prescription form: the medicine itself, then an
    optional alternative carrying exactly the same fields.

    Rendered twice from the same source — once for the first row, once inside a
    <template> the "add medicine" button clones — so the two can never drift.
    $index is the array index, or the literal __INDEX__ placeholder in the
    template, which the script swaps for a real number when it clones.

    Expects: $index
--}}
<div class="rx-row rounded-lg border border-slate-200 bg-white p-3 mb-2">
    {{-- Primary medicine --}}
    <div class="rx-group">
        <div class="flex items-start gap-2">
            <div class="relative flex-1 min-w-0">
                <input name="items[{{ $index }}][medicine_name]" autocomplete="off"
                       class="rx-medicine w-full border rounded px-2 py-1.5 text-sm"
                       placeholder="{{ __('app.examine.medicine') }}">
                <ul class="rx-suggest hidden absolute z-30 mt-1 w-full max-h-56 overflow-y-auto rounded-lg border border-slate-200 bg-white text-sm shadow-lg"></ul>
                <p class="rx-form-hint mt-0.5 text-[11px] text-slate-400"></p>
            </div>
            <button type="button" class="rx-remove text-red-500 px-2 py-1 leading-none" aria-label="✕">✕</button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mt-2">
            <div>
                <label class="block text-[11px] text-slate-400 mb-0.5">{{ __('app.examine.dose') }}</label>
                <input name="items[{{ $index }}][dose]" list="rx-dose-{{ $index }}" autocomplete="off"
                       class="rx-dose w-full border rounded px-2 py-1.5 text-sm" placeholder="500 mg">
                <datalist id="rx-dose-{{ $index }}"></datalist>
            </div>
            <div>
                <label class="block text-[11px] text-slate-400 mb-0.5">{{ __('app.examine.frequency') }}</label>
                <input name="items[{{ $index }}][frequency]" class="w-full border rounded px-2 py-1.5 text-sm" placeholder="2x/day">
            </div>
            <div>
                <label class="block text-[11px] text-slate-400 mb-0.5">{{ __('app.examine.duration') }}</label>
                <input name="items[{{ $index }}][duration]" class="w-full border rounded px-2 py-1.5 text-sm" placeholder="7 days">
            </div>
            <div>
                <label class="block text-[11px] text-slate-400 mb-0.5">{{ __('app.examine.instructions') }}</label>
                <input name="items[{{ $index }}][instructions]" class="w-full border rounded px-2 py-1.5 text-sm" placeholder="after meals">
            </div>
        </div>
    </div>

    {{-- Alternative: same fields, all optional. Collapsed until it is needed. --}}
    <details class="rx-alt mt-2 group">
        <summary class="cursor-pointer text-xs font-medium text-slate-500 hover:text-slate-700 select-none">
            {{ __('app.examine.alternative') }}
        </summary>

        <div class="rx-group mt-2 rounded-lg bg-slate-50 border border-slate-200 p-2">
            <div class="relative">
                <input name="items[{{ $index }}][substitute_name]" autocomplete="off"
                       class="rx-medicine w-full border rounded px-2 py-1.5 text-sm"
                       placeholder="{{ __('app.examine.substitute_placeholder') }}">
                <ul class="rx-suggest hidden absolute z-30 mt-1 w-full max-h-56 overflow-y-auto rounded-lg border border-slate-200 bg-white text-sm shadow-lg"></ul>
                <p class="rx-form-hint mt-0.5 text-[11px] text-slate-400"></p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mt-2">
                <div>
                    <label class="block text-[11px] text-slate-400 mb-0.5">{{ __('app.examine.dose') }}</label>
                    <input name="items[{{ $index }}][substitute_dose]" list="rx-sub-dose-{{ $index }}" autocomplete="off"
                           class="rx-dose w-full border rounded px-2 py-1.5 text-sm">
                    <datalist id="rx-sub-dose-{{ $index }}"></datalist>
                </div>
                <div>
                    <label class="block text-[11px] text-slate-400 mb-0.5">{{ __('app.examine.frequency') }}</label>
                    <input name="items[{{ $index }}][substitute_frequency]" class="w-full border rounded px-2 py-1.5 text-sm">
                </div>
                <div>
                    <label class="block text-[11px] text-slate-400 mb-0.5">{{ __('app.examine.duration') }}</label>
                    <input name="items[{{ $index }}][substitute_duration]" class="w-full border rounded px-2 py-1.5 text-sm">
                </div>
                <div>
                    <label class="block text-[11px] text-slate-400 mb-0.5">{{ __('app.examine.instructions') }}</label>
                    <input name="items[{{ $index }}][substitute_instructions]" class="w-full border rounded px-2 py-1.5 text-sm">
                </div>
            </div>
        </div>
    </details>
</div>
