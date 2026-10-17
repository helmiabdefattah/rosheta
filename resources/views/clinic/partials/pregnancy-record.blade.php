{{--
    Pregnancy follow-up for women's clinics, shown under the diagnosis. Three
    parts:

      1. A banner that recomputes TODAY's gestational age (and the expected
         delivery date) from the dating the doctor set once — showing the date it
         was computed from, every time the file is opened.
      2. The dating form: from the last menstrual period, or a gestational age
         entered manually as of a reference date.
      3. This visit's fetal + maternal data, then the follow-up history.

    Self-contained like the other examination partials; it loads its own data.
    Expects: $appointment, $doctor.
--}}
@php
    use App\Support\WomensCare;

    $isWomens = WomensCare::forSpecialization($doctor->specialization);
@endphp

@if ($isWomens)
    @php
        $clientId = $appointment->client_id;

        $preg = \App\Models\PregnancyRecord::where('client_id', $clientId)->first();
        $visit = \App\Models\PregnancyVisit::where('appointment_id', $appointment->id)->first();
        $visits = \App\Models\PregnancyVisit::with('doctor')
            ->where('client_id', $clientId)
            ->orderByDesc('recorded_at')->orderByDesc('id')
            ->limit(40)
            ->get();

        $actingDoctorId = $doctor->id;

        $gaDays = $preg?->gestationalAgeDays();
        $edd = $preg?->expectedDeliveryDate();
        $anchor = $preg?->anchorDate();

        // Gestational age as "W weeks D days" from a day count.
        $fmtGa = function (?int $days) {
            if ($days === null) {
                return '—';
            }
            return __('app.pregnancy.ga_weeks_days', ['w' => intdiv($days, 7), 'd' => $days % 7]);
        };

        $num = fn ($v, $unit) => ($v === null || $v === '') ? '—'
            : rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.').' '.$unit;

        // Trimester, from today's age.
        $trimester = null;
        if ($gaDays !== null) {
            $w = intdiv($gaDays, 7);
            $trimester = $w < 14 ? 1 : ($w < 28 ? 2 : 3);
        }

        $datingMethod = old('dating_method', $preg->dating_method ?? 'lmp');
        $presentations = array_filter((array) __('app.pregnancy.presentations'));
    @endphp

    {{-- 1 + 2. Dating --}}
    <section id="pregnancy" class="card p-4 sm:p-5">
        <h2 class="text-base font-semibold text-slate-900 mb-3">🤰 {{ __('app.pregnancy.section_title') }}</h2>

        {{-- Live gestational age, recomputed for today from the stored dating. --}}
        @if ($gaDays !== null)
            <div class="rounded-xl border border-pink-200 bg-pink-50/70 p-4 mb-4">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <div class="text-xs text-pink-700/80">{{ __('app.pregnancy.ga_today') }} · {{ now()->format('Y-m-d') }}</div>
                        <div class="text-2xl font-bold text-pink-800 leading-tight">{{ $fmtGa($gaDays) }}</div>
                        @if ($trimester)
                            <div class="text-xs text-pink-700 mt-0.5">{{ __('app.pregnancy.trimester_'.$trimester) }}</div>
                        @endif
                    </div>
                    <div class="text-sm text-slate-700 space-y-0.5 text-end">
                        @if ($edd)
                            <div>{{ __('app.pregnancy.edd') }}:
                                <span class="font-semibold text-slate-900">{{ $edd->format('Y-m-d') }}</span>
                                <span class="text-xs text-slate-500">({{ $edd->isPast() ? __('app.pregnancy.overdue') : $edd->diffForHumans(['parts' => 1]) }})</span>
                            </div>
                        @endif
                        @if ($anchor)
                            <div class="text-xs text-slate-500">
                                {{ $preg->dating_method === 'manual' ? __('app.pregnancy.from_assessment') : __('app.pregnancy.from_lmp') }}:
                                {{ $anchor->format('Y-m-d') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <p class="text-sm text-slate-500 mb-4">{{ __('app.pregnancy.no_dating_hint') }}</p>
        @endif

        {{-- Dating form: pick a method; fill the matching fields. --}}
        <form method="POST" action="{{ route('practice.doctor.pregnancy.record.store', $appointment) }}" class="space-y-4"
              data-pregnancy-dating
              data-ga-format="{{ __('app.pregnancy.ga_weeks_days', ['w' => ':w', 'd' => ':d']) }}"
              data-label-today="{{ __('app.pregnancy.ga_today') }}"
              data-label-edd="{{ __('app.pregnancy.edd') }}"
              data-label-preview="{{ __('app.pregnancy.preview') }}">
            @csrf

            <div class="flex flex-wrap gap-2">
                <label class="flex items-center gap-2 text-sm border border-slate-200 rounded-lg px-3 py-2 cursor-pointer has-[:checked]:border-pink-400 has-[:checked]:bg-pink-50">
                    <input type="radio" name="dating_method" value="lmp" @checked($datingMethod === 'lmp') data-method-radio> {{ __('app.pregnancy.method_lmp') }}
                </label>
                <label class="flex items-center gap-2 text-sm border border-slate-200 rounded-lg px-3 py-2 cursor-pointer has-[:checked]:border-pink-400 has-[:checked]:bg-pink-50">
                    <input type="radio" name="dating_method" value="manual" @checked($datingMethod === 'manual') data-method-radio> {{ __('app.pregnancy.method_manual') }}
                </label>
            </div>

            {{-- From the last menstrual period --}}
            <div data-method-panel="lmp" class="rounded-lg border border-slate-200 bg-slate-50/60 p-3">
                <label for="preg-lmp" class="block text-sm text-slate-500 mb-1">{{ __('app.pregnancy.lmp_date') }}</label>
                <input type="date" name="lmp_date" id="preg-lmp" value="{{ old('lmp_date', optional($preg?->lmp_date)->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}"
                       class="w-full sm:w-64 border rounded-lg px-3 py-2 text-sm">
                <p class="text-[11px] text-slate-400 mt-1">{{ __('app.pregnancy.lmp_hint') }}</p>
                @error('lmp_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- A gestational age entered by hand as of a reference date --}}
            <div data-method-panel="manual" class="rounded-lg border border-slate-200 bg-slate-50/60 p-3">
                <p class="text-[11px] text-slate-400 mb-2">{{ __('app.pregnancy.manual_hint') }}</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div>
                        <label for="preg-ga-weeks" class="block text-sm text-slate-500 mb-1">{{ __('app.pregnancy.ga_weeks') }}</label>
                        <input type="number" min="0" max="45" name="ga_weeks" id="preg-ga-weeks" value="{{ old('ga_weeks', $preg?->ga_weeks) }}" inputmode="numeric"
                               class="w-full border rounded-lg px-3 py-2 text-sm">
                        @error('ga_weeks')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="preg-ga-days" class="block text-sm text-slate-500 mb-1">{{ __('app.pregnancy.ga_extra_days') }}</label>
                        <input type="number" min="0" max="6" name="ga_days" id="preg-ga-days" value="{{ old('ga_days', $preg?->ga_days) }}" inputmode="numeric"
                               class="w-full border rounded-lg px-3 py-2 text-sm">
                        @error('ga_days')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="col-span-2">
                        <label for="preg-assessed" class="block text-sm text-slate-500 mb-1">{{ __('app.pregnancy.assessed_on') }}</label>
                        <input type="date" name="ga_assessed_on" id="preg-assessed" value="{{ old('ga_assessed_on', optional($preg?->ga_assessed_on)->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}"
                               class="w-full border rounded-lg px-3 py-2 text-sm">
                        @error('ga_assessed_on')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Mother's summary --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div>
                    <label for="preg-gravida" class="block text-sm text-slate-500 mb-1">{{ __('app.pregnancy.gravida') }}</label>
                    <input type="number" min="0" max="30" name="gravida" id="preg-gravida" value="{{ old('gravida', $preg?->gravida) }}" inputmode="numeric"
                           class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="preg-para" class="block text-sm text-slate-500 mb-1">{{ __('app.pregnancy.para') }}</label>
                    <input type="number" min="0" max="30" name="para" id="preg-para" value="{{ old('para', $preg?->para) }}" inputmode="numeric"
                           class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="preg-blood" class="block text-sm text-slate-500 mb-1">{{ __('app.pregnancy.blood_group') }}</label>
                    <input type="text" name="blood_group" id="preg-blood" value="{{ old('blood_group', $preg?->blood_group) }}" placeholder="O+"
                           class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div>
                <label for="preg-notes" class="block text-sm text-slate-500 mb-1">{{ __('app.pregnancy.notes') }}</label>
                <textarea name="notes" id="preg-notes" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('notes', $preg?->notes) }}</textarea>
            </div>

            {{-- Automatic gestational age + EDD, recomputed live as the doctor
                 types — before the record is even saved. --}}
            <div data-ga-preview class="hidden text-sm rounded-lg bg-pink-50 border border-pink-200 text-pink-800 px-3 py-2"></div>

            <button class="btn btn-primary">{{ __('app.pregnancy.save_dating') }}</button>
        </form>
    </section>

    {{-- 3. Fetal + maternal data for this visit, then the follow-up history. --}}
    <section id="pregnancy-fetal" class="card p-4 sm:p-5">
        <h2 class="text-base font-semibold text-slate-900 mb-1">👶 {{ __('app.pregnancy.fetal_title') }}</h2>
        <p class="text-xs text-slate-500 mb-4">{{ __('app.pregnancy.fetal_hint') }}</p>

        <form method="POST" action="{{ route('practice.doctor.pregnancy.visit.store', $appointment) }}" class="space-y-3">
            @csrf
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <div>
                    <label for="pv-bp" class="block text-sm text-slate-500 mb-1">{{ __('app.pregnancy.blood_pressure') }}</label>
                    <input type="text" name="blood_pressure" id="pv-bp" value="{{ old('blood_pressure', $visit?->blood_pressure) }}" placeholder="120/80"
                           class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="pv-weight" class="block text-sm text-slate-500 mb-1">{{ __('app.pregnancy.mother_weight') }}</label>
                    <input type="number" step="any" min="0" name="weight_kg" id="pv-weight" value="{{ old('weight_kg', $visit?->weight_kg) }}" inputmode="decimal"
                           class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="pv-sfh" class="block text-sm text-slate-500 mb-1">{{ __('app.pregnancy.fundal_height') }}</label>
                    <input type="number" step="any" min="0" name="fundal_height_cm" id="pv-sfh" value="{{ old('fundal_height_cm', $visit?->fundal_height_cm) }}" inputmode="decimal"
                           class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="pv-fhr" class="block text-sm text-slate-500 mb-1">{{ __('app.pregnancy.fetal_heart_rate') }}</label>
                    <input type="number" min="0" max="300" name="fetal_heart_rate" id="pv-fhr" value="{{ old('fetal_heart_rate', $visit?->fetal_heart_rate) }}" inputmode="numeric"
                           class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="pv-efw" class="block text-sm text-slate-500 mb-1">{{ __('app.pregnancy.fetal_weight') }}</label>
                    <input type="number" min="0" max="9000" name="fetal_weight_g" id="pv-efw" value="{{ old('fetal_weight_g', $visit?->fetal_weight_g) }}" inputmode="numeric"
                           class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="pv-count" class="block text-sm text-slate-500 mb-1">{{ __('app.pregnancy.fetus_count') }}</label>
                    <input type="number" min="1" max="6" name="fetus_count" id="pv-count" value="{{ old('fetus_count', $visit?->fetus_count) }}" inputmode="numeric"
                           class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label for="pv-presentation" class="block text-sm text-slate-500 mb-1">{{ __('app.pregnancy.presentation') }}</label>
                    <input type="text" name="presentation" id="pv-presentation" list="pv-presentations" autocomplete="off"
                           value="{{ old('presentation', $visit?->presentation) }}" class="w-full border rounded-lg px-3 py-2 text-sm">
                    <datalist id="pv-presentations">
                        @foreach ($presentations as $pr)<option value="{{ $pr }}"></option>@endforeach
                    </datalist>
                </div>
            </div>
            <div>
                <label for="pv-us" class="block text-sm text-slate-500 mb-1">{{ __('app.pregnancy.ultrasound') }}</label>
                <textarea name="ultrasound" id="pv-us" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('ultrasound', $visit?->ultrasound) }}</textarea>
            </div>
            <div>
                <label for="pv-notes" class="block text-sm text-slate-500 mb-1">{{ __('app.pregnancy.notes') }}</label>
                <textarea name="notes" id="pv-notes" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('notes', $visit?->notes) }}</textarea>
            </div>
            <button class="btn btn-primary">{{ __('app.pregnancy.save_visit') }}</button>
        </form>

        {{-- Follow-up history across visits. --}}
        <div class="mt-5 border-t border-slate-100 pt-4">
            <h3 class="text-sm font-semibold text-slate-800 mb-2">{{ __('app.pregnancy.history') }}</h3>
            @if ($visits->isEmpty())
                <p class="text-sm text-slate-400">{{ __('app.pregnancy.no_visits') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] text-slate-400 text-start">
                                <th class="text-start font-medium py-1">{{ __('app.pregnancy.date') }}</th>
                                <th class="text-start font-medium py-1">{{ __('app.pregnancy.ga_short') }}</th>
                                <th class="text-start font-medium py-1">{{ __('app.pregnancy.bp_short') }}</th>
                                <th class="text-start font-medium py-1">{{ __('app.pregnancy.sfh_short') }}</th>
                                <th class="text-start font-medium py-1">{{ __('app.pregnancy.fhr_short') }}</th>
                                <th class="text-start font-medium py-1">{{ __('app.pregnancy.efw_short') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($visits as $vz)
                                <tr class="{{ $visit && $vz->id === $visit->id ? 'bg-pink-50/50' : '' }}">
                                    <td class="py-1.5 text-slate-500 whitespace-nowrap">{{ optional($vz->recorded_at ?? $vz->created_at)->format('Y-m-d') }}</td>
                                    <td class="py-1.5 text-slate-800 whitespace-nowrap">{{ $vz->ga_days === null ? '—' : $fmtGa($vz->ga_days) }}</td>
                                    <td class="py-1.5 text-slate-800 whitespace-nowrap" dir="ltr">{{ $vz->blood_pressure ?: '—' }}</td>
                                    <td class="py-1.5 text-slate-800">{{ $num($vz->fundal_height_cm, __('app.pregnancy.unit_cm')) }}</td>
                                    <td class="py-1.5 text-slate-800">{{ $vz->fetal_heart_rate ? $vz->fetal_heart_rate.' '.__('app.pregnancy.unit_bpm') : '—' }}</td>
                                    <td class="py-1.5 text-slate-800">{{ $vz->fetal_weight_g ? $vz->fetal_weight_g.' '.__('app.pregnancy.unit_g') : '—' }}</td>
                                </tr>
                                @if ($vz->ultrasound || $vz->notes)
                                    <tr><td colspan="6" class="pb-2 text-xs text-slate-500">{{ trim(($vz->ultrasound ? '🩻 '.$vz->ultrasound.'  ' : '').($vz->notes ?? '')) }}</td></tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>

    @push('scripts')
    <script>
        (function () {
            var form = document.querySelector('[data-pregnancy-dating]');
            if (!form) return;

            var preview = form.querySelector('[data-ga-preview]');
            var gaFormat = form.dataset.gaFormat || ':w / :d';
            var labelToday = form.dataset.labelToday || '';
            var labelEdd = form.dataset.labelEdd || '';
            var labelPreview = form.dataset.labelPreview || '';

            // Parse / format as plain calendar days (UTC) so no timezone shift
            // can move the result by a day.
            function parseDate(s) {
                if (!s) return null;
                var p = String(s).split('-');
                if (p.length !== 3) return null;
                var d = new Date(Date.UTC(+p[0], +p[1] - 1, +p[2]));
                return isNaN(d.getTime()) ? null : d;
            }
            function today() { var n = new Date(); return new Date(Date.UTC(n.getFullYear(), n.getMonth(), n.getDate())); }
            function addDays(d, n) { return new Date(d.getTime() + n * 86400000); }
            function iso(d) {
                return d.getUTCFullYear() + '-' + ('0' + (d.getUTCMonth() + 1)).slice(-2) + '-' + ('0' + d.getUTCDate()).slice(-2);
            }
            function gaText(days) { return gaFormat.replace(':w', Math.floor(days / 7)).replace(':d', days % 7); }
            function field(name) { return form.querySelector('[name="' + name + '"]'); }
            function currentMethod() { return (form.querySelector('[data-method-radio]:checked') || {}).value || 'lmp'; }

            // Same anchor maths as PregnancyRecord: age today = anchor age + days
            // elapsed since the anchor; EDD = full term (280d) back from the anchor.
            function compute() {
                var method = currentMethod(), anchor = null, anchorGa = 0;
                if (method === 'manual') {
                    anchor = parseDate((field('ga_assessed_on') || {}).value);
                    var w = parseInt((field('ga_weeks') || {}).value, 10);
                    var d = parseInt((field('ga_days') || {}).value, 10);
                    if (isNaN(w)) return null;
                    anchorGa = w * 7 + (isNaN(d) ? 0 : d);
                } else {
                    anchor = parseDate((field('lmp_date') || {}).value);
                }
                if (!anchor) return null;
                var delta = Math.floor((today().getTime() - anchor.getTime()) / 86400000);
                return { ga: Math.max(0, anchorGa + delta), edd: addDays(anchor, 280 - anchorGa) };
            }

            function renderPreview() {
                if (!preview) return;
                var r = compute();
                if (!r) { preview.classList.add('hidden'); preview.textContent = ''; return; }
                preview.classList.remove('hidden');
                preview.textContent = (labelPreview ? labelPreview + ' · ' : '')
                    + labelToday + ': ' + gaText(r.ga)
                    + ' · ' + labelEdd + ': ' + iso(r.edd);
            }

            // Show only the fields the chosen method uses, and disable the other
            // method's inputs so they don't fail required_if on the server.
            function sync() {
                var method = currentMethod();
                form.querySelectorAll('[data-method-panel]').forEach(function (panel) {
                    var on = panel.getAttribute('data-method-panel') === method;
                    panel.style.display = on ? '' : 'none';
                    panel.querySelectorAll('input').forEach(function (i) { i.disabled = !on; });
                });
                renderPreview();
            }

            form.querySelectorAll('[data-method-radio]').forEach(function (r) { r.addEventListener('change', sync); });
            ['lmp_date', 'ga_assessed_on', 'ga_weeks', 'ga_days'].forEach(function (n) {
                var el = field(n);
                if (el) el.addEventListener('input', renderPreview);
            });
            sync();
        })();
    </script>
    @endpush
@endif
