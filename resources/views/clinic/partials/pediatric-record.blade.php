{{--
    Paediatric / newborn growth + vaccination record, shown under the diagnosis
    for paediatric clinics (those doctors have no body chart). Two cards:

      1. This visit's measurements (weight, height/length, head circumference,
         temperature, notes) — one row per visit, edited in place — followed by
         the growth history across every visit so the trend reads at a glance.
      2. The child's vaccination log, added to on any visit.

    Self-contained like the clinical-chart partial: it loads its own data.
    Expects: $appointment, $doctor.
--}}
@php
    use App\Support\PediatricCare;

    $isPediatric = PediatricCare::forSpecialization($doctor->specialization);
@endphp

@if ($isPediatric)
    @php
        $clientId = $appointment->client_id;

        // This visit's measurement (edited in place), and the full growth history.
        $record = \App\Models\PediatricVisitRecord::where('appointment_id', $appointment->id)->first();
        $growth = \App\Models\PediatricVisitRecord::where('client_id', $clientId)
            ->orderByDesc('recorded_at')->orderByDesc('id')
            ->limit(40)
            ->get();

        $vaccinations = \App\Models\PediatricVaccination::with('doctor')
            ->where('client_id', $clientId)
            ->orderByDesc('given_at')->orderByDesc('id')
            ->get();

        $actingDoctorId = $doctor->id;
        $commonVaccines = array_filter((array) __('app.pediatric.common_vaccines'));

        $num = fn ($v, $unit) => $v === null ? '—' : rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.').' '.$unit;
    @endphp

    {{-- 1. Measurements + growth history --}}
    <section id="pediatric" class="card p-4 sm:p-5">
        <div class="flex items-center justify-between gap-2 mb-1">
            <h2 class="text-base font-semibold text-slate-900">👶 {{ __('app.pediatric.section_title') }}</h2>
            @if ($appointment->client->age !== null)
                <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
                    {{ __('app.pediatric.age') }}: {{ $appointment->client->age }}
                </span>
            @endif
        </div>
        <p class="text-xs text-slate-500 mb-4">{{ __('app.pediatric.measurements_hint') }}</p>

        <form method="POST" action="{{ route('practice.doctor.pediatric.measurement.store', $appointment) }}" class="space-y-3">
            @csrf
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div>
                    <label for="ped-weight" class="block text-sm text-slate-500 mb-1">{{ __('app.pediatric.weight') }}</label>
                    <input type="number" step="any" min="0" name="weight_kg" id="ped-weight"
                           value="{{ old('weight_kg', $record?->weight_kg) }}" inputmode="decimal"
                           class="w-full border rounded-lg px-3 py-2 text-sm">
                    @error('weight_kg')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="ped-height" class="block text-sm text-slate-500 mb-1">{{ __('app.pediatric.height') }}</label>
                    <input type="number" step="any" min="0" name="height_cm" id="ped-height"
                           value="{{ old('height_cm', $record?->height_cm) }}" inputmode="decimal"
                           class="w-full border rounded-lg px-3 py-2 text-sm">
                    @error('height_cm')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="ped-head" class="block text-sm text-slate-500 mb-1">{{ __('app.pediatric.head') }}</label>
                    <input type="number" step="any" min="0" name="head_circumference_cm" id="ped-head"
                           value="{{ old('head_circumference_cm', $record?->head_circumference_cm) }}" inputmode="decimal"
                           class="w-full border rounded-lg px-3 py-2 text-sm">
                    @error('head_circumference_cm')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="ped-temp" class="block text-sm text-slate-500 mb-1">{{ __('app.pediatric.temperature') }}</label>
                    <input type="number" step="any" min="20" max="45" name="temperature_c" id="ped-temp"
                           value="{{ old('temperature_c', $record?->temperature_c) }}" inputmode="decimal"
                           class="w-full border rounded-lg px-3 py-2 text-sm">
                    @error('temperature_c')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label for="ped-notes" class="block text-sm text-slate-500 mb-1">{{ __('app.pediatric.notes') }}</label>
                <textarea name="notes" id="ped-notes" rows="2"
                          class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('notes', $record?->notes) }}</textarea>
            </div>
            <button class="btn btn-primary">{{ __('app.pediatric.save_measurement') }}</button>
        </form>

        {{-- Growth history: every visit's measurements, newest first. --}}
        <div class="mt-5 border-t border-slate-100 pt-4">
            <h3 class="text-sm font-semibold text-slate-800 mb-2">{{ __('app.pediatric.growth_history') }}</h3>
            @if ($growth->isEmpty())
                <p class="text-sm text-slate-400">{{ __('app.pediatric.no_measurements') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] text-slate-400 text-start">
                                <th class="text-start font-medium py-1">{{ __('app.pediatric.date') }}</th>
                                <th class="text-start font-medium py-1">{{ __('app.pediatric.weight') }}</th>
                                <th class="text-start font-medium py-1">{{ __('app.pediatric.height') }}</th>
                                <th class="text-start font-medium py-1">{{ __('app.pediatric.head') }}</th>
                                <th class="text-start font-medium py-1">{{ __('app.pediatric.temperature') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($growth as $g)
                                <tr class="{{ $record && $g->id === $record->id ? 'bg-indigo-50/50' : '' }}">
                                    <td class="py-1.5 text-slate-500 whitespace-nowrap">
                                        {{ optional($g->recorded_at ?? $g->created_at)->format('Y-m-d') }}
                                    </td>
                                    <td class="py-1.5 text-slate-800">{{ $num($g->weight_kg, __('app.pediatric.unit_kg')) }}</td>
                                    <td class="py-1.5 text-slate-800">{{ $num($g->height_cm, __('app.pediatric.unit_cm')) }}</td>
                                    <td class="py-1.5 text-slate-800">{{ $num($g->head_circumference_cm, __('app.pediatric.unit_cm')) }}</td>
                                    <td class="py-1.5 text-slate-800">{{ $num($g->temperature_c, __('app.pediatric.unit_c')) }}</td>
                                </tr>
                                @if ($g->notes)
                                    <tr><td colspan="5" class="pb-2 text-xs text-slate-500">{{ $g->notes }}</td></tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>

    {{-- 2. Vaccination log --}}
    <section id="pediatric-vaccinations" class="card p-4 sm:p-5">
        <div class="flex items-center justify-between gap-2 mb-3">
            <h2 class="text-base font-semibold text-slate-900">💉 {{ __('app.pediatric.vaccinations_title') }}</h2>
            <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ $vaccinations->count() }}</span>
        </div>

        <ul class="space-y-2 text-sm mb-4">
            @forelse ($vaccinations as $v)
                <li class="flex items-start justify-between gap-2 border border-slate-100 rounded-lg px-3 py-2">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            <span class="font-medium text-slate-800">{{ $v->vaccine }}</span>
                            @if ($v->dose)<span class="text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-600">{{ $v->dose }}</span>@endif
                            @if ($v->given_at)
                                <span class="text-xs text-emerald-700">✔ {{ $v->given_at->format('Y-m-d') }}</span>
                            @endif
                            @if ($v->due_at)
                                <span class="text-xs {{ $v->due_at->isPast() ? 'text-red-600 font-medium' : 'text-amber-700' }}">
                                    ⏰ {{ __('app.pediatric.due_short') }} {{ $v->due_at->format('Y-m-d') }}
                                </span>
                            @endif
                        </div>
                        @if ($v->notes)<p class="text-xs text-slate-500 mt-0.5">{{ $v->notes }}</p>@endif
                        @if ($v->doctor)<p class="text-[11px] text-slate-400 mt-0.5">{{ $v->doctor->name }}</p>@endif
                    </div>
                    @if ((int) $v->doctor_id === (int) $actingDoctorId)
                        <form method="POST" action="{{ route('practice.doctor.pediatric.vaccinations.destroy', [$appointment, $v]) }}"
                              onsubmit="return confirm('{{ __('app.pediatric.vaccination_remove_confirm') }}')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm shrink-0" aria-label="{{ __('app.common.remove') }}">✕</button>
                        </form>
                    @endif
                </li>
            @empty
                <li class="text-slate-400 italic">{{ __('app.pediatric.no_vaccinations') }}</li>
            @endforelse
        </ul>

        <form method="POST" action="{{ route('practice.doctor.pediatric.vaccinations.store', $appointment) }}"
              class="space-y-3 rounded-lg border border-slate-200 bg-slate-50/60 p-3">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2">
                    <label for="vax-name" class="block text-xs text-slate-500 mb-1">{{ __('app.pediatric.vaccine') }}</label>
                    <input type="text" name="vaccine" id="vax-name" list="vax-suggestions" autocomplete="off" required
                           placeholder="{{ __('app.pediatric.vaccine_placeholder') }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm">
                    <datalist id="vax-suggestions">
                        @foreach ($commonVaccines as $vx)
                            <option value="{{ $vx }}"></option>
                        @endforeach
                    </datalist>
                    @error('vaccine')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="vax-dose" class="block text-xs text-slate-500 mb-1">{{ __('app.pediatric.dose') }}</label>
                    <input type="text" name="dose" id="vax-dose" placeholder="{{ __('app.pediatric.dose_placeholder') }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="vax-given" class="block text-xs text-slate-500 mb-1">{{ __('app.pediatric.given_at') }}</label>
                        <input type="date" name="given_at" id="vax-given" value="{{ now()->format('Y-m-d') }}"
                               class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label for="vax-due" class="block text-xs text-slate-500 mb-1">{{ __('app.pediatric.due_at') }}</label>
                        <input type="date" name="due_at" id="vax-due"
                               class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="sm:col-span-2">
                    <label for="vax-notes" class="block text-xs text-slate-500 mb-1">{{ __('app.pediatric.notes') }}</label>
                    <input type="text" name="notes" id="vax-notes" class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <button class="btn btn-primary btn-sm">{{ __('app.pediatric.add_vaccination') }}</button>
        </form>
    </section>
@endif
