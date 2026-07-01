@extends('clinic.layouts.app')

@section('content')
@php use App\Models\Clinic; @endphp

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">{{ __('app.clinic.title') }}</h1>
        <p class="text-slate-500 text-sm">{{ __('app.clinic.subtitle') }}</p>
    </div>
    <a href="{{ route('practice.doctor.dashboard') }}" class="text-sm text-indigo-600 hover:underline">{{ __('app.common.back') }}</a>
</div>

<form method="POST" action="{{ route('practice.doctor.clinic.update') }}" class="space-y-6" id="clinic-form">
    @csrf @method('PUT')

    {{-- Appointment duration --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h2 class="font-semibold text-slate-800 mb-1">{{ __('app.clinic.duration_heading') }}</h2>
        <p class="text-xs text-slate-500 mb-4">{{ __('app.clinic.duration_hint') }}</p>
        <div class="flex items-center gap-3">
            <input type="number" name="appointment_duration" id="duration" min="5" max="240" step="5"
                   value="{{ old('appointment_duration', $clinic->appointment_duration ?? 30) }}"
                   class="w-28 border rounded px-3 py-2 text-sm">
            <span class="text-sm text-slate-500">{{ __('app.clinic.minutes') }}</span>
            <span class="text-sm text-slate-400">·</span>
            <span class="text-sm text-slate-600">
                ≈ <strong id="per-hour">{{ $clinic->slotsPerHour() }}</strong> {{ __('app.clinic.per_hour') }}
            </span>
        </div>
    </div>

    {{-- Opening days & hours --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h2 class="font-semibold text-slate-800 mb-4">{{ __('app.clinic.hours_heading') }}</h2>
        <div class="space-y-2">
            @foreach (Clinic::DAYS as $day)
                @php $h = $clinic->hoursFor($day); @endphp
                <div class="grid grid-cols-12 items-center gap-3 py-2 border-b border-slate-50 last:border-0" data-day-row>
                    <label class="col-span-4 md:col-span-3 flex items-center gap-2 text-sm font-medium text-slate-700">
                        <input type="checkbox" name="days[{{ $day }}][enabled]" value="1" data-enabled
                               class="rounded" @checked(! $h['closed'])>
                        {{ \Illuminate\Support\Carbon::parse($day)->translatedFormat('l') }}
                    </label>
                    <div class="col-span-8 md:col-span-9 flex flex-wrap items-center gap-2 text-sm">
                        <input type="time" name="days[{{ $day }}][open]" value="{{ $h['open'] }}" data-open
                               class="border rounded px-2 py-1.5 {{ $h['closed'] ? 'opacity-40' : '' }}">
                        <span class="text-slate-400">→</span>
                        <input type="time" name="days[{{ $day }}][close]" value="{{ $h['close'] }}" data-close
                               class="border rounded px-2 py-1.5 {{ $h['closed'] ? 'opacity-40' : '' }}">
                        <span class="ms-2 text-xs text-slate-500" data-slots></span>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4 text-sm text-slate-600">
            {{ __('app.clinic.weekly_total') }}: <strong id="weekly-total">0</strong> {{ __('app.clinic.appointments') }}
        </div>
    </div>

    <div class="flex gap-2">
        <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg">
            {{ __('app.clinic.save') }}
        </button>
    </div>
</form>

@push('scripts')
<script>
    // Live capacity: minutes between open & close / duration.
    function recalc() {
        const duration = Math.max(1, parseInt(document.getElementById('duration').value) || 30);
        document.getElementById('per-hour').textContent = Math.round((60 / duration) * 10) / 10;

        let weekly = 0;
        document.querySelectorAll('[data-day-row]').forEach(row => {
            const enabled = row.querySelector('[data-enabled]').checked;
            const open = row.querySelector('[data-open]');
            const close = row.querySelector('[data-close]');
            const out = row.querySelector('[data-slots]');
            open.disabled = close.disabled = !enabled;
            open.classList.toggle('opacity-40', !enabled);
            close.classList.toggle('opacity-40', !enabled);

            let slots = 0;
            if (enabled && open.value && close.value) {
                const [oh, om] = open.value.split(':').map(Number);
                const [ch, cm] = close.value.split(':').map(Number);
                const mins = (ch * 60 + cm) - (oh * 60 + om);
                slots = mins > 0 ? Math.floor(mins / duration) : 0;
            }
            out.textContent = enabled ? `${slots} {{ __('app.clinic.slots') }}` : '{{ __('app.clinic.closed') }}';
            weekly += slots;
        });
        document.getElementById('weekly-total').textContent = weekly;
    }

    document.getElementById('clinic-form').addEventListener('input', recalc);
    document.getElementById('clinic-form').addEventListener('change', recalc);
    recalc();
</script>
@endpush
@endsection
