@php
    use App\Support\DentalTooth;
    use App\Enums\DentalToothCondition;
    use App\Enums\DentalToothTreatment;
    $order = $order ?? null;
    $inp = 'w-full border rounded-lg px-3 py-2 text-sm';
    $lbl = 'block text-sm font-semibold text-slate-700 mb-1';

    $presetClientId = $presetClientId ?? null;

    // The patient's recorded teeth marks (condition colour + treatment symbol),
    // overlaid on the chart the same way the examination screen shows them.
    $markClientId = old('client_id', $order->client_id ?? $presetClientId);
    $labMarks = $markClientId ? \App\Models\ClinicalChartNote::teethMarks((int) $markClientId) : [];
    $teethMode = old('teeth_mode', $order->teeth_mode ?? 'order');
    $orderTeeth = old('teeth', $order && $order->teeth_mode === 'order' ? ($order->teeth ?? []) : []);

    $itemRows = old('items');
    if (! is_array($itemRows)) {
        $itemRows = ($order && $order->items->count())
            ? $order->items->map(fn ($it) => [
                'dental_item_id' => $it->dental_item_id,
                'name' => $it->name,
                'level' => $it->level->value,
                'unit' => $it->unit,
                'quantity' => $it->quantity,
                'unit_price' => $it->unit_price,
                'teeth' => $it->teeth ?? [],
            ])->all()
            : [['level' => '', 'quantity' => 1, 'unit' => 'piece', 'teeth' => []]];
    }

    // The teeth chart only matters once the order has a tooth-level item.
    $hasToothItem = collect($itemRows)->contains(fn ($r) => ($r['level'] ?? '') === 'tooth');
@endphp

<form method="POST" action="{{ $action }}" id="order-form">
    @csrf
    @if($order) @method('PUT') @endif
    @if(request()->boolean('embed'))<input type="hidden" name="embed" value="1">@endif

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Order meta --}}
            <div class="card p-5 grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="{{ $lbl }}">{{ __('app.dental.order.lab') }} <span class="text-red-500">*</span></label>
                    <select name="dental_lab_id" class="{{ $inp }}" required>
                        @foreach($labs as $lab)
                            <option value="{{ $lab->id }}" @selected(old('dental_lab_id', $order->dental_lab_id ?? '') == $lab->id)>{{ $lab->name }}</option>
                        @endforeach
                    </select>
                    @if($labs->isEmpty())
                        <p class="text-xs text-amber-600 mt-1">
                            <a href="{{ route('practice.dental-lab.labs.create') }}" class="underline">{{ __('app.dental.lab.add') }}</a>
                        </p>
                    @endif
                </div>
                <div>
                    <label class="{{ $lbl }}">{{ __('app.dental.order.patient') }}</label>
                    <select name="client_id" class="{{ $inp }}">
                        <option value="">{{ __('app.dental.order.no_patient') }}</option>
                        @foreach($patients as $p)
                            <option value="{{ $p->id }}" @selected(old('client_id', $order->client_id ?? $presetClientId) == $p->id)>{{ $p->name }}@if($p->phone_number) — {{ $p->phone_number }}@endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="{{ $lbl }}">{{ __('app.dental.order.notes') }}</label>
                    <textarea name="notes" rows="2" class="{{ $inp }}" placeholder="{{ __('app.dental.order.notes_ph') }}">{{ old('notes', $order->notes ?? '') }}</textarea>
                </div>
            </div>

            {{-- Line items --}}
            <div class="card p-5">
                <div class="flex items-center justify-between mb-1">
                    <h3 class="font-bold text-slate-800">{{ __('app.dental.line.title') }}</h3>
                </div>
                <p class="text-xs text-slate-500 mb-3">{{ __('app.dental.line.pick') }}</p>

                {{-- Column headers (desktop), aligned to the item grid. --}}
                <div class="hidden md:grid grid-cols-12 gap-2 px-3 mb-1 text-xs font-semibold text-slate-400">
                    <div class="md:col-span-4">{{ __('app.dental.line.name') }}</div>
                    <div class="md:col-span-2">{{ __('app.dental.line.qty') }}</div>
                    <div class="md:col-span-2">{{ __('app.dental.line.unit') }}</div>
                    <div class="md:col-span-2">{{ __('app.dental.line.price') }}</div>
                    <div class="md:col-span-2 text-end">{{ __('app.dental.line.total') }}</div>
                </div>

                <div id="items-wrap" class="space-y-3">
                    @foreach($itemRows as $i => $row)
                        @include('clinic.dental-lab.orders._item-row', ['i' => $i, 'row' => $row, 'catalog' => $catalog])
                    @endforeach
                </div>

                <button type="button" id="add-item" class="mt-3 inline-flex items-center gap-2 px-4 py-2 rounded-lg border-2 border-dashed border-slate-300 text-slate-600 font-semibold hover:border-indigo-400 hover:text-indigo-600 transition-all text-sm">
                    + {{ __('app.dental.line.add') }}
                </button>
            </div>

            {{-- Teeth chart: appears once the order has a tooth-level item --}}
            <div class="card p-5 {{ $hasToothItem ? '' : 'hidden' }}" id="teeth-section">
                <div class="flex items-center gap-4 mb-3 flex-wrap">
                    <span class="font-bold text-slate-800">{{ __('app.dental.teeth.title') }}</span>
                    <label class="inline-flex items-center gap-1.5 text-sm text-slate-600">
                        <input type="radio" name="teeth_mode" value="order" @checked($teethMode !== 'item')> {{ __('app.dental.teeth.whole_order') }}
                    </label>
                    <label class="inline-flex items-center gap-1.5 text-sm text-slate-600">
                        <input type="radio" name="teeth_mode" value="item" @checked($teethMode === 'item')> {{ __('app.dental.teeth.per_item') }}
                    </label>
                </div>

                {{-- Legend for the patient's recorded marks (shown on both charts). --}}
                <div class="mb-3 space-y-1.5 text-[11px] {{ empty($labMarks) ? 'hidden' : '' }}" id="teeth-legend">
                    <div>
                        <span class="text-slate-400 me-1">{{ __('app.chart.dental.conditions') }}:</span>
                        @foreach (DentalToothCondition::cases() as $c)
                            <span class="inline-flex items-center gap-1 me-2 align-middle">
                                <span class="inline-block w-3 h-3 rounded-sm" style="background: {{ $c->color() }}"></span>
                                <span class="text-slate-600">{{ $c->label() }}</span>
                            </span>
                        @endforeach
                    </div>
                    <div>
                        <span class="text-slate-400 me-1">{{ __('app.chart.dental.treatments') }}:</span>
                        @foreach (DentalToothTreatment::cases() as $t)
                            <span class="inline-flex items-center gap-1 me-2 align-middle">
                                <span class="inline-flex items-center justify-center w-4 h-4 rounded-sm text-white font-bold" style="background: {{ $t->color() }}; font-size: 9px;">{{ $t->symbol() }}</span>
                                <span class="text-slate-600">{{ $t->label() }}</span>
                            </span>
                        @endforeach
                    </div>
                </div>

                <div id="order-teeth-block" class="{{ $teethMode === 'item' ? 'hidden' : '' }}">
                    <p class="text-xs text-slate-500 mb-2">{{ __('app.dental.teeth.pick') }}</p>
                    @include('clinic.dental-lab.partials._teeth-chart', ['chartId' => 'order-chart'])
                    <div class="flex items-center justify-between mt-2 text-sm">
                        <span class="text-slate-500">{{ __('app.dental.teeth.selected') }}: <span id="order-teeth-summary" class="font-mono text-slate-700"></span></span>
                        <button type="button" id="order-teeth-clear" class="text-indigo-600 hover:underline text-xs">{{ __('app.dental.teeth.clear') }}</button>
                    </div>
                    <div id="order-teeth-inputs"></div>
                </div>
            </div>
        </div>

        {{-- Summary / actions --}}
        <div>
            <div class="card p-5 sticky top-4">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-slate-500">{{ __('app.dental.patient_account.lab_price') }}</span>
                    <span class="text-2xl font-black text-slate-900"><span id="order-total">0.00</span></span>
                </div>
                <button class="btn btn-primary btn-block">{{ __('app.dental.actions.save') }}</button>
                <a href="{{ route('practice.dental-lab.orders.index') }}" class="btn btn-secondary btn-block mt-2">{{ __('app.dental.actions.cancel') }}</a>
            </div>
        </div>
    </div>
</form>

{{-- Item row template for the repeater --}}
<template id="item-template">
    @include('clinic.dental-lab.orders._item-row', ['i' => null, 'row' => ['level' => '', 'quantity' => 1, 'unit' => 'piece', 'teeth' => []], 'catalog' => $catalog])
</template>

{{-- Per-item teeth modal --}}
<div id="teeth-modal" class="hidden fixed inset-0 z-[100] items-center justify-center bg-slate-900/50 p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold text-slate-800">{{ __('app.dental.teeth.select') }}</h3>
            <button type="button" id="teeth-modal-cancel" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <p class="text-xs text-slate-500 mb-2">{{ __('app.dental.teeth.pick') }}</p>
        @include('clinic.dental-lab.partials._teeth-chart', ['chartId' => 'modal-chart'])
        <div class="flex justify-end gap-2 mt-4">
            <button type="button" id="teeth-modal-cancel-2" class="btn btn-secondary btn-sm" onclick="document.getElementById('teeth-modal-cancel').click()">{{ __('app.dental.actions.cancel') }}</button>
            <button type="button" id="teeth-modal-apply" class="btn btn-primary btn-sm">{{ __('app.dental.teeth.apply') }}</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var form = document.getElementById('order-form');
    if (!form) return;
    var wrap = document.getElementById('items-wrap');
    var tpl = document.getElementById('item-template');
    var addBtn = document.getElementById('add-item');
    var orderTotalEl = document.getElementById('order-total');
    var NONE = @json(__('app.dental.teeth.none'));

    function reindex() {
        wrap.querySelectorAll('.item-row').forEach(function (row, i) {
            row.querySelectorAll('[data-key]').forEach(function (el) {
                el.setAttribute('name', 'items[' + i + ']' + el.getAttribute('data-key'));
            });
        });
    }

    var orderPatientTotalEl = document.getElementById('order-patient-total');

    function recalc() {
        var labTotal = 0, patientTotal = 0;
        wrap.querySelectorAll('.item-row').forEach(function (row) {
            var q = parseFloat(row.querySelector('.item-qty').value) || 0;
            var p = parseFloat(row.querySelector('.item-price').value) || 0;
            var feeEl = row.querySelector('.item-op-fee');
            var fee = parseFloat(feeEl && feeEl.value) || 0;
            var lt = q * p;            // lab price for this line
            var pt = lt + fee;         // patient price for this line
            labTotal += lt; patientTotal += pt;
            row.querySelector('.item-line-total').textContent = lt.toFixed(2);
            var pp = row.querySelector('.item-patient-price');
            if (pp) pp.textContent = pt.toFixed(2);
        });
        orderTotalEl.textContent = labTotal.toFixed(2);
        if (orderPatientTotalEl) orderPatientTotalEl.textContent = patientTotal.toFixed(2);
    }

    function mode() {
        var r = form.querySelector('input[name="teeth_mode"]:checked');
        return r ? r.value : 'order';
    }
    function updateRowTeeth(row) {
        var level = row.querySelector('.item-level').value;
        row.querySelector('.item-teeth').classList.toggle('hidden', !(mode() === 'item' && level === 'tooth'));
    }
    function applyMode() {
        document.getElementById('order-teeth-block').classList.toggle('hidden', mode() !== 'order');
        wrap.querySelectorAll('.item-row').forEach(updateRowTeeth);
    }
    function hasToothItem() {
        return Array.from(wrap.querySelectorAll('.item-level')).some(function (s) { return s.value === 'tooth'; });
    }
    function updateTeethSection() {
        // The teeth chart only appears once a tooth-level item is on the order.
        document.getElementById('teeth-section').classList.toggle('hidden', !hasToothItem());
        applyMode();
    }

    wrap.addEventListener('change', function (e) {
        if (e.target.classList.contains('item-catalog')) {
            var opt = e.target.selectedOptions[0];
            var row = e.target.closest('.item-row');
            if (opt && opt.value) {
                if (opt.dataset.level) row.querySelector('.item-level').value = opt.dataset.level;
                if (opt.dataset.unit) row.querySelector('.item-unit').value = opt.dataset.unit;
                if (opt.dataset.name) row.querySelector('.item-name').value = opt.dataset.name;
            }
            updateRowTeeth(row);
            updateTeethSection();
        }
        if (e.target.classList.contains('item-level')) {
            updateRowTeeth(e.target.closest('.item-row'));
            updateTeethSection();
        }
    });
    wrap.addEventListener('input', function (e) {
        if (e.target.classList.contains('item-qty') || e.target.classList.contains('item-price') || e.target.classList.contains('item-op-fee')) recalc();
    });

    addBtn.addEventListener('click', function () {
        var node = tpl.content.firstElementChild.cloneNode(true);
        wrap.appendChild(node);
        reindex(); recalc(); updateRowTeeth(node); updateTeethSection();
    });
    wrap.addEventListener('click', function (e) {
        if (e.target.closest('.remove-item')) {
            if (wrap.querySelectorAll('.item-row').length > 1) {
                e.target.closest('.item-row').remove(); reindex(); recalc(); updateTeethSection();
            }
        }
    });
    form.querySelectorAll('input[name="teeth_mode"]').forEach(function (r) { r.addEventListener('change', applyMode); });

    // ----- tooth chart helpers -----
    function paint(chart, set) {
        chart.querySelectorAll('.tooth').forEach(function (b) { b.classList.toggle('is-sel', set.has(b.dataset.tooth)); });
    }
    function wire(chart, getSet, after) {
        chart.addEventListener('click', function (e) {
            var b = e.target.closest('.tooth'); if (!b) return;
            var set = getSet();
            if (set.has(b.dataset.tooth)) set.delete(b.dataset.tooth); else set.add(b.dataset.tooth);
            after();
        });
    }

    // Order-level chart
    var orderChart = document.getElementById('order-chart');
    var orderSet = new Set(@json(array_values((array) $orderTeeth)));
    var orderHidden = document.getElementById('order-teeth-inputs');
    var orderSummary = document.getElementById('order-teeth-summary');
    function renderOrder() {
        paint(orderChart, orderSet);
        orderHidden.innerHTML = '';
        orderSet.forEach(function (c) {
            var i = document.createElement('input'); i.type = 'hidden'; i.name = 'teeth[]'; i.value = c; orderHidden.appendChild(i);
        });
        orderSummary.textContent = orderSet.size ? Array.from(orderSet).join(', ') : NONE;
    }
    wire(orderChart, function () { return orderSet; }, renderOrder);
    document.getElementById('order-teeth-clear').addEventListener('click', function () { orderSet.clear(); renderOrder(); });
    renderOrder();

    // Per-item modal chart
    var modal = document.getElementById('teeth-modal');
    var modalChart = document.getElementById('modal-chart');
    var modalSet = new Set();
    var modalRow = null;
    function openModal(row) {
        modalRow = row; modalSet = new Set();
        row.querySelectorAll('.item-teeth-hidden input').forEach(function (i) { modalSet.add(i.value); });
        paint(modalChart, modalSet);
        modal.classList.remove('hidden'); modal.classList.add('flex');
    }
    function closeModal() { modal.classList.add('hidden'); modal.classList.remove('flex'); modalRow = null; }
    wire(modalChart, function () { return modalSet; }, function () { paint(modalChart, modalSet); });
    wrap.addEventListener('click', function (e) { if (e.target.closest('.pick-teeth')) openModal(e.target.closest('.item-row')); });
    document.getElementById('teeth-modal-cancel').addEventListener('click', closeModal);
    document.getElementById('teeth-modal-apply').addEventListener('click', function () {
        if (!modalRow) return;
        var hidden = modalRow.querySelector('.item-teeth-hidden');
        var chips = modalRow.querySelector('.item-teeth-chips');
        hidden.innerHTML = ''; chips.innerHTML = '';
        modalSet.forEach(function (c) {
            var inp = document.createElement('input'); inp.type = 'hidden'; inp.setAttribute('data-key', '[teeth][]'); inp.value = c; hidden.appendChild(inp);
            var chip = document.createElement('span'); chip.className = 'px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 text-xs font-mono'; chip.textContent = c; chips.appendChild(chip);
        });
        reindex(); closeModal();
    });

    // ----- patient teeth marks overlay (condition colour + treatment symbol) -----
    var TOOTH_MARKS = @json((object) $labMarks);
    var MARKS_BASE = @json(url('practice/dental-lab/patient-marks'));
    var legend = document.getElementById('teeth-legend');

    function applyMarks(chart) {
        if (!chart) return;
        chart.querySelectorAll('.tooth').forEach(function (g) {
            var m = TOOTH_MARKS[g.dataset.tooth];
            var sym = g.querySelector('[data-tooth-sym]');
            if (m) {
                g.classList.add('tooth-marked');
                g.style.setProperty('--mark', m.fill);
                if (sym) sym.textContent = m.symbol || '';
                var t = g.querySelector('title');
                if (t && m.label) t.textContent = g.dataset.tooth + ' — ' + m.label;
            } else {
                g.classList.remove('tooth-marked');
                g.style.removeProperty('--mark');
                if (sym) sym.textContent = '';
            }
        });
    }
    function applyAllMarks() {
        applyMarks(orderChart);
        applyMarks(modalChart);
        if (legend) legend.classList.toggle('hidden', Object.keys(TOOTH_MARKS).length === 0);
    }
    applyAllMarks();

    // Refresh the overlay when the patient changes.
    var patientSel = form.querySelector('select[name="client_id"]');
    if (patientSel) {
        patientSel.addEventListener('change', function () {
            var id = patientSel.value;
            if (!id) { TOOTH_MARKS = {}; applyAllMarks(); return; }
            fetch(MARKS_BASE + '/' + encodeURIComponent(id), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                .then(function (r) { return r.ok ? r.json() : {}; })
                .then(function (d) { TOOTH_MARKS = d || {}; applyAllMarks(); })
                .catch(function () {});
        });
    }

    reindex(); recalc(); updateTeethSection();
})();
</script>
@endpush
