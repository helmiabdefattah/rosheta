@php
    use App\Enums\DentalItemLevel;
    /** One line-item row. $i = int for server rows, null for the JS template. */
    $i = $i ?? null;
    $isTpl = $i === null;
    $row = $row ?? [];
    $nm = function (string $suffix) use ($i, $isTpl) {
        $dk = 'data-key="'.e($suffix).'"';
        return $isTpl ? $dk : ('name="items['.$i.']'.e($suffix).'" '.$dk);
    };
    $inp = 'w-full border rounded-lg px-2 py-1.5 text-sm';
    $catalogByLevel = ($catalog ?? collect())->groupBy(fn ($c) => $c->level->value);
    $rowLevel = $row['level'] ?? '';
    $rowTeeth = $row['teeth'] ?? [];
@endphp

<div class="item-row card p-3 bg-slate-50/60">
    <div class="grid grid-cols-12 gap-2 items-start">
        {{-- Catalog picker (fills the fields below; carries dental_item_id) --}}
        <div class="col-span-12 md:col-span-4">
            <select {!! $nm('[dental_item_id]') !!} class="{{ $inp }} item-catalog">
                <option value="">{{ __('app.dental.line.custom') }}</option>
                @foreach(DentalItemLevel::cases() as $lvl)
                    @php $opts = $catalogByLevel->get($lvl->value, collect()); @endphp
                    @if($opts->isNotEmpty())
                        <optgroup label="{{ $lvl->label() }}">
                            @foreach($opts as $opt)
                                <option value="{{ $opt->id }}"
                                        data-level="{{ $opt->level->value }}"
                                        data-unit="{{ $opt->default_unit }}"
                                        data-name="{{ $opt->displayName() }}"
                                        @selected(($row['dental_item_id'] ?? '') == $opt->id)>{{ $opt->displayName() }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                @endforeach
            </select>
            <input type="text" {!! $nm('[name]') !!} value="{{ $row['name'] ?? '' }}" placeholder="{{ __('app.dental.line.name') }}" class="{{ $inp }} item-name mt-1" required>
            {{-- Level is detected from the chosen catalog item (custom items default to patient-level). --}}
            <input type="hidden" {!! $nm('[level]') !!} value="{{ $rowLevel }}" class="item-level">
        </div>

        {{-- Qty --}}
        <div class="col-span-4 md:col-span-2">
            <input type="number" step="0.01" min="0.01" {!! $nm('[quantity]') !!} value="{{ $row['quantity'] ?? 1 }}" placeholder="{{ __('app.dental.line.qty') }}" class="{{ $inp }} item-qty" required>
        </div>

        {{-- Unit --}}
        <div class="col-span-4 md:col-span-2">
            <input type="text" {!! $nm('[unit]') !!} value="{{ $row['unit'] ?? 'piece' }}" placeholder="{{ __('app.dental.line.unit') }}" class="{{ $inp }} item-unit" required>
        </div>

        {{-- Price --}}
        <div class="col-span-4 md:col-span-2">
            <input type="number" step="0.01" min="0" {!! $nm('[unit_price]') !!} value="{{ $row['unit_price'] ?? '' }}" placeholder="{{ __('app.dental.line.price') }}" class="{{ $inp }} item-price" required>
        </div>

        {{-- Line total + remove --}}
        <div class="col-span-12 md:col-span-2 flex items-center justify-between gap-2">
            <span class="text-sm font-semibold text-slate-800 item-line-total">0.00</span>
            <button type="button" class="remove-item text-red-500 hover:text-red-700 text-sm" title="{{ __('app.dental.line.remove') }}">✕</button>
        </div>
    </div>

    {{-- Per-item pricing: operating fee (patient side) + the resulting patient price. --}}
    <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm border-t border-slate-100 pt-2">
        <label class="flex items-center gap-1.5 text-slate-600">
            {{ __('app.dental.patient_account.operating_fee') }}
            <input type="number" step="0.01" min="0" {!! $nm('[operating_fee]') !!} value="{{ $row['operating_fee'] ?? '' }}" placeholder="0.00" class="item-op-fee border rounded-lg px-2 py-1 text-sm w-24">
        </label>
        <span class="text-slate-600">{{ __('app.dental.patient_account.patient_price') }}: <b class="item-patient-price text-slate-900">0.00</b></span>
    </div>

    {{-- Per-item teeth (tooth-level rows, per-item mode) --}}
    <div class="item-teeth mt-2 {{ ($rowLevel === 'tooth') ? '' : 'hidden' }}">
        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-secondary btn-sm pick-teeth">🦷 {{ __('app.dental.teeth.select') }}</button>
            <span class="item-teeth-chips flex flex-wrap gap-1">
                @foreach($rowTeeth as $t)<span class="px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 text-xs font-mono">{{ $t }}</span>@endforeach
            </span>
        </div>
        <div class="item-teeth-hidden hidden">
            @foreach($rowTeeth as $t)<input type="hidden" {!! $nm('[teeth][]') !!} value="{{ $t }}">@endforeach
        </div>
    </div>
</div>
