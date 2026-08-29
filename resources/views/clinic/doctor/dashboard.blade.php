@extends('clinic.layouts.app')

@section('content')
@php
    $statusColors = [
        'scheduled' => 'bg-slate-100 text-slate-700',
        'under_examination' => 'bg-amber-100 text-amber-800',
        'completed' => 'bg-emerald-100 text-emerald-700',
        'cancelled' => 'bg-red-100 text-red-700',
        'escaped' => 'bg-orange-100 text-orange-700',
        'pending' => 'bg-yellow-100 text-yellow-800',
        'confirmed' => 'bg-blue-100 text-blue-700',
        'missed' => 'bg-red-100 text-red-700',
    ];
@endphp

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">{{ __('app.doctor.title') }}</h1>
        <p class="text-slate-500 text-sm">
            {{ now()->translatedFormat('l, d M Y') }} @if ($clinic) &middot; {{ $clinic->name }} @endif
        </p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        {{-- Clinic switcher: only worth a menu when there's more than one. --}}
        @if ($clinics->count() > 1)
            <details data-menu class="relative">
                <summary class="list-none cursor-pointer select-none inline-flex items-center gap-1.5 text-sm font-medium px-4 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700">
                    🏥 <span class="max-w-[12rem] truncate">{{ $clinic?->name }}</span>
                    <span class="text-[10px] text-slate-400">▼</span>
                </summary>
                <div class="absolute end-0 mt-2 w-64 bg-white border border-slate-200 rounded-xl shadow-lg py-1 z-50 text-start">
                    <div class="px-4 py-1.5 text-xs uppercase tracking-wide text-slate-400">{{ __('app.clinic.switch') }}</div>
                    @foreach ($clinics as $c)
                        <form method="POST" action="{{ route('practice.doctor.clinic.switch') }}">
                            @csrf
                            <input type="hidden" name="clinic_id" value="{{ $c->id }}">
                            <button class="w-full flex items-center justify-between gap-2 px-4 py-2 text-sm text-start hover:bg-slate-50
                                           {{ $c->id === $clinic?->id ? 'font-semibold text-indigo-700 bg-indigo-50/60' : 'text-slate-700' }}">
                                <span class="truncate">{{ $c->name }}</span>
                                @if ($c->id === $clinic?->id)<span class="text-indigo-600">✓</span>@endif
                            </button>
                        </form>
                    @endforeach
                </div>
            </details>
        @endif

        <a href="{{ route('practice.doctor.manager.index') }}"
           class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
            📊 {{ __('app.manager.button') }}
        </a>
        <a href="{{ route('practice.doctor.setup.index') }}"
           class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-medium px-4 py-2 rounded-lg">
            🧩 {{ __('app.setup.button') }}
        </a>
        {{-- Call next: completes whoever is in the chair and starts the next one
             in today's queue — the same action the assistant's screen fires, so
             the waiting-room counter announces it exactly as it always did. --}}
        <button type="button" id="doctor-next-btn"
                class="bg-amber-500 hover:bg-amber-400 text-slate-900 text-sm font-bold px-4 py-2 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed">
            ⏭ {{ __('app.display.next') }}
        </button>
        <button type="button" onclick="toggle('broadcast-modal')"
                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
            📣 {{ __('app.notify.button') }}
        </button>
        <a href="{{ route('practice.doctor.clinic.edit') }}"
           class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-medium px-4 py-2 rounded-lg">
            ⚙️ {{ __('app.clinic.title') }}
        </a>
    </div>
</div>

@if ($current)
    <div class="bg-amber-50 border-2 border-amber-300 rounded-xl p-5 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="min-w-0">
            <div class="text-xs font-semibold uppercase text-amber-600 mb-1">{{ __('app.doctor.currently_examining') }}</div>
            <div class="text-xl font-bold text-slate-900">#{{ $current->queue_number }} — {{ $current->client->name }}</div>
            <div class="text-sm text-slate-600">{{ $current->typeLabel() }} &middot; {{ $current->scheduled_at->format('H:i') }}</div>
        </div>
        <div class="shrink-0 flex flex-col sm:flex-row gap-2">
            <a href="{{ route('practice.doctor.examine', $current) }}"
               class="text-center bg-amber-600 hover:bg-amber-700 text-white font-medium px-5 py-2.5 rounded-lg">
                {{ __('app.doctor.open_examination') }}
            </a>
            <form method="POST" action="{{ route('practice.appointments.status', $current) }}">
                @csrf
                <input type="hidden" name="status" value="completed">
                <button class="w-full text-center bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-5 py-2.5 rounded-lg">
                    ✔ {{ __('app.assistant.mark_completed') }}
                </button>
            </form>
        </div>
    </div>
@endif

{{-- Appointments for the selected day --}}
<div class="flex items-center justify-between mb-3">
    <h2 class="font-semibold text-slate-800">
        {{ $selectedDate->translatedFormat('l, d M Y') }}
        @if ($selectedDate->isToday())
            <span class="ms-2 text-xs font-medium text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">{{ __('app.calendar.today') }}</span>
        @endif
        <span class="ms-1 text-sm font-normal text-slate-400">· {{ $appointments->count() }} {{ __('app.clinic.appointments') }}</span>
    </h2>
    @unless ($selectedDate->isToday())
        <a href="{{ route('practice.doctor.dashboard') }}" class="text-sm text-indigo-600 hover:underline">↩ {{ __('app.calendar.today') }}</a>
    @endunless
</div>

<div class="md:bg-white md:rounded-xl md:shadow-sm md:overflow-x-auto">
    <table class="w-full text-sm block md:table">
        <thead class="bg-slate-50 text-slate-500 text-left hidden md:table-header-group">
            <tr>
                <th class="px-4 py-3 w-12">{{ __('app.table.num') }}</th>
                <th class="px-4 py-3">{{ __('app.table.patient') }}</th>
                <th class="px-4 py-3">{{ __('app.table.type') }}</th>
                <th class="px-4 py-3">{{ __('app.table.time') }}</th>
                <th class="px-4 py-3">{{ __('app.table.amount') }}</th>
                <th class="px-4 py-3">{{ __('app.table.status') }}</th>
                <th class="px-4 py-3 text-end">{{ __('app.table.action') }}</th>
            </tr>
        </thead>
        <tbody class="block md:table-row-group md:divide-y md:divide-slate-100">
            @forelse ($appointments as $appt)
                <tr class="block md:table-row rounded-xl shadow-sm md:shadow-none border md:border-x-0 md:border-t-0 border-slate-200 mb-3 md:mb-0 p-3 md:p-0 {{ $appt->status === 'under_examination' ? 'bg-amber-50' : 'bg-white md:bg-transparent' }}">
                    <td class="block md:table-cell md:px-4 md:py-3 font-bold text-slate-400">
                        <span class="md:hidden text-xs uppercase text-slate-400">{{ __('app.table.num') }}: </span>#{{ $appt->queue_number }}
                    </td>
                    <td class="block md:table-cell md:px-4 md:py-3 font-semibold text-slate-900 text-base md:text-sm">
                        {{ $appt->client->name }}
                        @include('clinic.partials.insurance-badge', ['appt' => $appt])
                    </td>
                    <td class="flex justify-between md:table-cell md:px-4 md:py-3 border-t border-slate-100 mt-2 pt-2 md:border-0 md:mt-0 md:pt-0">
                        <span class="md:hidden text-xs uppercase text-slate-400">{{ __('app.table.type') }}</span>
                        <span>{{ $appt->typeLabel() }}</span>
                    </td>
                    <td class="flex justify-between md:table-cell md:px-4 md:py-3">
                        <span class="md:hidden text-xs uppercase text-slate-400">{{ __('app.table.time') }}</span>
                        <span>{{ $appt->scheduled_at->format('H:i') }}</span>
                    </td>
                    {{-- Money: visit fee + extras added during the examination. --}}
                    <td class="flex justify-between items-center md:table-cell md:px-4 md:py-3">
                        <span class="md:hidden text-xs uppercase text-slate-400">{{ __('app.table.amount') }}</span>
                        @include('clinic.partials.amount-cell', ['appt' => $appt])
                    </td>
                    <td class="flex justify-between items-center md:table-cell md:px-4 md:py-3">
                        <span class="md:hidden text-xs uppercase text-slate-400">{{ __('app.table.status') }}</span>
                        <span class="text-xs px-2 py-1 rounded-full {{ $statusColors[$appt->status] ?? 'bg-slate-100 text-slate-700' }}">
                            {{ $appt->statusLabel() }}
                        </span>
                    </td>
                    <td class="block md:table-cell md:px-4 md:py-3 text-end mt-3 md:mt-0">
                        @if (! in_array($appt->status, ['completed', 'cancelled']))
                            {{-- The doctor drives the queue from here too, not just
                                 from the assistant's screen: start whoever is in the
                                 chair, or close them out. Same endpoint the assistant
                                 posts to — it already allows either role. --}}
                            <div class="flex flex-col md:flex-row md:justify-end gap-2">
                                <a href="{{ route('practice.doctor.examine', $appt) }}"
                                   class="block md:inline-block text-center bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-3 py-2 md:py-1.5 rounded-lg">{{ __('app.doctor.examine') }}</a>

                                @if (in_array($appt->status, ['scheduled', 'escaped', 'pending', 'confirmed']))
                                    <form method="POST" action="{{ route('practice.appointments.status', $appt) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="under_examination">
                                        <button class="w-full md:w-auto text-center bg-amber-100 hover:bg-amber-200 text-amber-800 text-xs px-3 py-2 md:py-1.5 rounded-lg">
                                            ▶ {{ __('app.assistant.start_examination') }}
                                        </button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('practice.appointments.status', $appt) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="completed">
                                    <button class="w-full md:w-auto text-center bg-emerald-100 hover:bg-emerald-200 text-emerald-800 text-xs px-3 py-2 md:py-1.5 rounded-lg">
                                        ✔ {{ __('app.assistant.mark_completed') }}
                                    </button>
                                </form>
                            </div>
                        @else
                            <a href="{{ route('practice.doctor.examine', $appt) }}"
                               class="text-indigo-600 hover:underline text-xs">{{ __('app.doctor.view') }}</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr class="block md:table-row"><td colspan="7" class="block md:table-cell px-4 py-10 text-center text-slate-400 bg-white rounded-xl md:rounded-none">{{ __('app.doctor.empty') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Month calendar: green = clinic open, badge = booked appointments --}}
<div class="bg-white rounded-xl shadow-sm p-3 sm:p-5 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-slate-800">{{ $calendar['month']->translatedFormat('F Y') }}</h2>
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('practice.doctor.dashboard', ['month' => $calendar['prev']]) }}"
               class="px-3 py-1 rounded border border-slate-300 hover:bg-slate-50">‹</a>
            <a href="{{ route('practice.doctor.dashboard') }}"
               class="px-3 py-1 rounded border border-slate-300 hover:bg-slate-50">{{ __('app.calendar.today') }}</a>
            <a href="{{ route('practice.doctor.dashboard', ['month' => $calendar['next']]) }}"
               class="px-3 py-1 rounded border border-slate-300 hover:bg-slate-50">›</a>
        </div>
    </div>

    <div class="grid grid-cols-7 gap-0.5 sm:gap-1 text-center text-[10px] sm:text-xs font-semibold text-slate-400 mb-1">
        @foreach ($calendar['dayHeaders'] as $dh)
            <div class="py-1 truncate">{{ $dh }}</div>
        @endforeach
    </div>

    <div class="grid grid-cols-7 gap-0.5 sm:gap-1">
        @foreach ($calendar['weeks'] as $week)
            @foreach ($week as $day)
                @php $closedWithAppts = ! $day['isOpen'] && $day['count'] > 0; @endphp
                <a href="{{ route('practice.doctor.dashboard', ['month' => $calendar['month']->format('Y-m'), 'date' => $day['date']->format('Y-m-d')]) }}"
                   class="relative block min-h-[46px] sm:min-h-[76px] rounded-md sm:rounded-lg border p-1 sm:p-1.5 transition hover:shadow-sm hover:border-indigo-300
                    {{ $day['isOpen'] ? 'bg-emerald-50 border-emerald-200' : ($closedWithAppts ? 'bg-red-50 border-red-200' : 'bg-slate-50 border-slate-100') }}
                    {{ $day['inMonth'] ? '' : 'opacity-40' }}
                    {{ $day['isSelected'] ? 'ring-2 ring-indigo-600 border-indigo-400' : ($day['isToday'] ? 'ring-2 ring-indigo-300' : '') }}">
                    {{-- Day number, small, in the corner --}}
                    <span class="absolute top-0.5 start-1 sm:top-1 sm:start-2 text-[10px] sm:text-xs {{ $day['isOpen'] ? 'text-emerald-700 font-semibold' : ($closedWithAppts ? 'text-red-700 font-semibold' : 'text-slate-400') }}">
                        {{ $day['date']->day }}
                    </span>
                    {{-- Appointment count, large and centered --}}
                    @if ($day['count'] > 0)
                        <div class="absolute inset-0 flex flex-col items-center justify-center pt-3 sm:pt-2"
                             title="{{ $day['count'] }} {{ __('app.clinic.appointments') }}{{ $closedWithAppts ? ' — '.__('app.calendar.closed_with_appointments') : '' }}">
                            <span class="text-base sm:text-3xl font-extrabold leading-none {{ $closedWithAppts ? 'text-red-600' : 'text-indigo-600' }}">{{ $day['count'] }}</span>
                            <span class="hidden md:block text-[10px] uppercase tracking-wide mt-0.5 {{ $closedWithAppts ? 'text-red-400' : 'text-indigo-400' }}">{{ __('app.clinic.appointments') }}</span>
                        </div>
                    @endif
                </a>
            @endforeach
        @endforeach
    </div>

    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-4 text-xs text-slate-500">
        <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded bg-emerald-100 border border-emerald-300"></span> {{ __('app.calendar.open') }}</span>
        <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded bg-slate-100 border border-slate-200"></span> {{ __('app.calendar.closed') }}</span>
        <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded bg-red-50 border border-red-200"></span> {{ __('app.calendar.closed_with_appointments') }}</span>
        <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full bg-indigo-600"></span> {{ __('app.calendar.has_appointments') }}</span>
    </div>
</div>

@include('clinic.partials.broadcast-modal')
@endsection

@push('scripts')
<script>
    (function () {
        var btn = document.getElementById('doctor-next-btn');
        if (!btn) return;

        var NEXT_URL = @json(route('practice.display.next', $clinic));
        var CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

        btn.addEventListener('click', function () {
            btn.disabled = true;
            fetch(NEXT_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store',
            })
                .then(function (r) { return r.json(); })
                // Reload rather than patch the row: the queue, the "now examining"
                // banner and the day's counts all move together.
                .then(function () { window.location.reload(); })
                .catch(function () { btn.disabled = false; });
        });
    })();
</script>
@endpush
