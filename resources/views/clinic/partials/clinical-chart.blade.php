{{--
    The specialisation's body chart, collapsed under the diagnosis.

    A dentist gets teeth, an orthopaedist a skeleton, an ophthalmologist eyes;
    anyone else gets nothing at all and this file renders empty. Notes belong to
    the patient, so a part shows what every doctor has ever written on it, not
    just this visit.

    Expects: $appointment (with client), $doctor.
--}}
@php
    use App\Support\ClinicalChart;

    $chartKey = ClinicalChart::forSpecialization($doctor->specialization);
@endphp

@if ($chartKey)
    @php
        $regions = ClinicalChart::regions($chartKey);
        $usesPoints = ClinicalChart::usesPoints($chartKey);

        $chartNotes = \App\Models\ClinicalChartNote::with('doctor')
            ->where('client_id', $appointment->client_id)
            ->where('chart', $chartKey)
            ->latest('id')
            ->get();

        $byRegion = $chartNotes->groupBy('region');
        $selectedRegion = request('chart_region');
        if (! array_key_exists((string) $selectedRegion, $regions)) {
            $selectedRegion = null;
        }

        // The chart is opened automatically when it already holds something, or
        // when the doctor was just working on a part.
        $openByDefault = $selectedRegion !== null || $chartNotes->isNotEmpty();

        // Everything the script needs: which parts carry notes, and the pins.
        $chartPayload = [
            'usesPoints' => $usesPoints,
            'labels' => $regions,
            'counts' => $byRegion->map->count(),
            'points' => $chartNotes->filter->hasPoint()->map(fn ($n) => [
                'region' => $n->region,
                'x' => $n->point_x,
                'y' => $n->point_y,
            ])->values(),
        ];
    @endphp

    <details id="clinical-chart" class="bg-white rounded-xl shadow-sm" @if ($openByDefault) open @endif>
        <summary class="cursor-pointer select-none list-none p-5 flex items-center justify-between gap-3">
            <span class="font-semibold text-slate-800">
                🩻 {{ ClinicalChart::title($chartKey) }}
            </span>
            <span class="flex items-center gap-2 text-xs text-slate-400">
                @if ($chartNotes->isNotEmpty())
                    <span class="px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 font-medium">
                        {{ trans_choice('app.chart.total_notes', $chartNotes->count(), ['count' => $chartNotes->count()]) }}
                    </span>
                @endif
                <span class="chart-caret transition-transform">▾</span>
            </span>
        </summary>

        <div class="px-5 pb-5 border-t border-slate-100 pt-4"
             data-clinical-chart
             data-chart="{{ $chartKey }}"
             data-points="{{ $usesPoints ? '1' : '0' }}"
             data-selected="{{ $selectedRegion }}"
             data-payload="{{ json_encode($chartPayload, JSON_UNESCAPED_UNICODE) }}">

            <p class="text-xs text-slate-500 mb-3">
                {{ $usesPoints ? __('app.chart.hint_points') : __('app.chart.hint') }}
            </p>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                {{-- The chart itself --}}
                <div class="min-w-0">
                    @include('clinic.partials.charts.'.$chartKey)
                </div>

                {{-- What is on the selected part, and how to add to it --}}
                <div class="min-w-0">
                    <div data-chart-empty class="{{ $selectedRegion ? 'hidden' : '' }} rounded-lg border border-dashed border-slate-200 p-6 text-center text-sm text-slate-400">
                        {{ __('app.chart.pick_part') }}
                    </div>

                    <div data-chart-panel class="{{ $selectedRegion ? '' : 'hidden' }}">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <h3 class="text-sm font-semibold text-slate-800">
                                <span class="text-slate-400 font-normal">{{ __('app.chart.selected') }}:</span>
                                <span data-chart-region-label></span>
                            </h3>
                        </div>

                        {{-- Existing notes on this part, newest first, from every
                             doctor who has seen this patient. --}}
                        <div data-chart-notes class="space-y-2 max-h-56 overflow-y-auto mb-3">
                            @foreach ($regions as $regionKey => $regionLabel)
                                <div data-chart-notes-for="{{ $regionKey }}"
                                     class="{{ $selectedRegion === (string) $regionKey ? '' : 'hidden' }} space-y-2">
                                    @forelse ($byRegion->get($regionKey, collect()) as $note)
                                        <div class="rounded-lg border border-slate-200 bg-slate-50/70 px-3 py-2">
                                            <p class="text-sm text-slate-800 whitespace-pre-line">{{ $note->note }}</p>
                                            <div class="mt-1 flex items-center justify-between gap-2 text-[11px] text-slate-400">
                                                <span>
                                                    {{ (int) $note->doctor_id === (int) $doctor->id ? __('app.chart.by_you') : ($note->doctor?->name ?? '—') }}
                                                    · {{ $note->created_at?->translatedFormat('d M Y') }}
                                                </span>
                                                @if ((int) $note->doctor_id === (int) $doctor->id)
                                                    <form method="POST"
                                                          action="{{ route('practice.doctor.chart-notes.destroy', [$appointment, $note]) }}"
                                                          onsubmit="return confirm('{{ __('app.chart.remove_confirm') }}')">
                                                        @csrf @method('DELETE')
                                                        <button class="text-red-500 hover:text-red-700">{{ __('app.chart.remove_note') }}</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-sm text-slate-400">{{ __('app.chart.no_notes') }}</p>
                                    @endforelse
                                </div>
                            @endforeach
                        </div>

                        <form method="POST" action="{{ route('practice.doctor.chart-notes.store', $appointment) }}" class="space-y-2">
                            @csrf
                            <input type="hidden" name="region" data-chart-region-input value="{{ $selectedRegion }}">
                            @if ($usesPoints)
                                <input type="hidden" name="point_x" data-chart-point-x>
                                <input type="hidden" name="point_y" data-chart-point-y>
                                <p data-chart-pin-hint class="hidden text-[11px] text-emerald-700">
                                    {{ __('app.chart.pin_here') }}
                                    <button type="button" data-chart-clear-pin class="underline">{{ __('app.chart.clear_pin') }}</button>
                                </p>
                            @endif
                            <textarea name="note" rows="3" required
                                      placeholder="{{ __('app.chart.note_placeholder') }}"
                                      class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                            @error('note')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                            <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg">
                                {{ __('app.chart.add_note') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </details>

    @once
    @push('scripts')
    <script>
        (function () {
            var root = document.querySelector('[data-clinical-chart]');
            if (!root) return;

            var config = JSON.parse(root.dataset.payload || '{}');
            var labels = config.labels || {};
            var counts = config.counts || {};
            var usesPoints = !!config.usesPoints;

            var regionInput = root.querySelector('[data-chart-region-input]');
            var regionLabel = root.querySelector('[data-chart-region-label]');
            var panel = root.querySelector('[data-chart-panel]');
            var empty = root.querySelector('[data-chart-empty]');
            var pointX = root.querySelector('[data-chart-point-x]');
            var pointY = root.querySelector('[data-chart-point-y]');
            var pinHint = root.querySelector('[data-chart-pin-hint]');

            /** Mark every part that already carries notes, so the chart reads at a glance. */
            root.querySelectorAll('[data-region]').forEach(function (part) {
                if (counts[part.dataset.region]) {
                    part.classList.add('has-notes');
                    part.setAttribute('data-count', counts[part.dataset.region]);
                }
            });

            function clearPin() {
                if (!usesPoints) return;
                pointX.value = '';
                pointY.value = '';
                pinHint.classList.add('hidden');
                root.querySelectorAll('[data-draft-pin]').forEach(function (p) { p.remove(); });
            }

            function select(region) {
                if (!labels.hasOwnProperty(region)) return;

                regionInput.value = region;
                regionLabel.textContent = labels[region];
                panel.classList.remove('hidden');
                empty.classList.add('hidden');

                root.querySelectorAll('[data-chart-notes-for]').forEach(function (list) {
                    list.classList.toggle('hidden', list.dataset.chartNotesFor !== region);
                });
                root.querySelectorAll('[data-region]').forEach(function (part) {
                    part.classList.toggle('is-selected', part.dataset.region === region);
                });

                clearPin();
            }

            root.addEventListener('click', function (e) {
                if (e.target.closest('[data-chart-clear-pin]')) { clearPin(); return; }

                var part = e.target.closest('[data-region]');
                if (!part) return;

                var region = part.dataset.region;
                select(region);

                if (!usesPoints) return;

                // Drop the pin where the click landed, as a fraction of the
                // part's own box, so it survives any later resize of the SVG.
                var box = part.getBoundingClientRect();
                var x = (e.clientX - box.left) / box.width;
                var y = (e.clientY - box.top) / box.height;
                if (x < 0 || x > 1 || y < 0 || y > 1) return;

                pointX.value = x.toFixed(4);
                pointY.value = y.toFixed(4);
                pinHint.classList.remove('hidden');

                var layer = root.querySelector('[data-pin-layer="' + region + '"]');
                if (layer) {
                    layer.querySelectorAll('[data-draft-pin]').forEach(function (p) { p.remove(); });
                    var pin = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                    pin.setAttribute('cx', (x * 100).toFixed(2) + '%');
                    pin.setAttribute('cy', (y * 100).toFixed(2) + '%');
                    pin.setAttribute('r', '4');
                    pin.setAttribute('class', 'chart-pin chart-pin-draft');
                    pin.setAttribute('data-draft-pin', '1');
                    layer.appendChild(pin);
                }
            });

            // Existing pins, drawn from what is already on file.
            (config.points || []).forEach(function (p) {
                var layer = root.querySelector('[data-pin-layer="' + p.region + '"]');
                if (!layer) return;
                var dot = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                dot.setAttribute('cx', (p.x * 100).toFixed(2) + '%');
                dot.setAttribute('cy', (p.y * 100).toFixed(2) + '%');
                dot.setAttribute('r', '3.5');
                dot.setAttribute('class', 'chart-pin');
                layer.appendChild(dot);
            });

            if (root.dataset.selected) select(root.dataset.selected);
        })();
    </script>
    @endpush

    @push('styles')
    <style>
        #clinical-chart summary::-webkit-details-marker { display: none; }
        #clinical-chart[open] .chart-caret { transform: rotate(180deg); }

        [data-clinical-chart] [data-region] { cursor: pointer; }
        [data-clinical-chart] [data-region] .part { transition: fill .12s, stroke .12s; }

        /* Part-based charts (teeth, bones) colour the whole part: the part IS
           the note's subject. */
        [data-clinical-chart][data-points="0"] [data-region]:hover .part { fill: #c7d2fe; }
        [data-clinical-chart][data-points="0"] [data-region].has-notes .part { fill: #fcd34d; }
        [data-clinical-chart][data-points="0"] [data-region].is-selected .part { fill: #4f46e5; stroke: #312e81; }
        [data-clinical-chart][data-points="0"] [data-region].is-selected text { fill: #fff; }

        /* Point-based charts (eyes) outline instead: the notes live at pins on
           the part, and flooding the sclera would bury both them and the iris. */
        [data-clinical-chart][data-points="1"] [data-region]:hover .part { stroke: #6366f1; stroke-width: 2.5; }
        [data-clinical-chart][data-points="1"] [data-region].has-notes .part { stroke: #d97706; stroke-width: 2.5; }
        [data-clinical-chart][data-points="1"] [data-region].is-selected .part { stroke: #4f46e5; stroke-width: 4; }

        [data-clinical-chart] .chart-pin { fill: #ef4444; stroke: #fff; stroke-width: 1.5; pointer-events: none; }
        [data-clinical-chart] .chart-pin-draft { fill: #059669; }
    </style>
    @endpush
    @endonce
@endif
