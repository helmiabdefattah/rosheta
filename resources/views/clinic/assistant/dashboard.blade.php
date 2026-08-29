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
        <h1 class="text-2xl font-bold text-slate-900">{{ __('app.assistant.title') }}</h1>
        <p class="text-slate-500 text-sm">
            {{ now()->translatedFormat('l, d M Y') }} @if($clinic) &middot; {{ $clinic->name }} @endif
        </p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        {{-- Pull the queue again. A link to the current URL, not location.reload():
             it keeps the selected date and re-requests cleanly instead of
             replaying whatever navigation landed here. --}}
        <a href="{{ request()->fullUrl() }}"
           class="inline-flex items-center gap-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-medium px-4 py-2 rounded-lg"
           title="{{ __('app.refresh') }}">
            <span aria-hidden="true">🔄</span> {{ __('app.refresh') }}
        </a>

        {{-- Ticket-printer status. Rendered from the current heartbeat, then
             re-checked periodically by the poller at the bottom of this view. --}}
        @php $printerOn = $clinic && $clinic->hasConnectedPrinter(); @endphp
        <span id="printer-status"
              class="inline-flex items-center gap-1.5 text-sm font-medium px-3 py-2 rounded-lg border
                     {{ $printerOn ? 'bg-emerald-50 border-emerald-300 text-emerald-800' : 'bg-slate-100 border-slate-300 text-slate-500' }}"
              title="{{ $printerOn ? __('app.ticket.printer_online_hint') : __('app.ticket.printer_offline_hint') }}">
            <span id="printer-dot"
                  class="w-2 h-2 rounded-full {{ $printerOn ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
            <span id="printer-text">🖨️ {{ $printerOn ? __('app.ticket.printer_online') : __('app.ticket.printer_offline') }}</span>
        </span>

        {{-- Mobile app only: open the native Bluetooth printer screen via the Flutter JS bridge --}}
        @if (str_contains(request()->userAgent() ?? '', 'MostashfaOnApp'))
        <button type="button"
                onclick="if (window.flutter_inappwebview && window.flutter_inappwebview.callHandler) { window.flutter_inappwebview.callHandler('mostashfaon', JSON.stringify({action: 'connect_printer'})); }"
                class="bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
            🖨️ {{ __('app.ticket.connect_printer') }}
        </button>
        @endif
        {{-- Notifications bell: patients requesting an appointment with this doctor
             via the Rosheta platform. Badge = number of requests awaiting the desk. --}}
        <details data-menu class="relative">
            <summary class="list-none cursor-pointer select-none relative inline-flex items-center justify-center w-10 h-10 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-lg"
                     title="{{ __('app.bell.title') }}">
                🔔
                @if ($pendingRequests->isNotEmpty())
                    <span class="absolute -top-1 -end-1 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold leading-none">
                        {{ $pendingRequests->count() > 99 ? '99+' : $pendingRequests->count() }}
                    </span>
                @endif
            </summary>
            <div class="absolute start-0 md:start-auto md:end-0 mt-2 w-72 max-w-[calc(100vw-2rem)] bg-white border border-slate-200 rounded-xl shadow-lg py-1 z-50 text-start">
                <div class="px-4 py-2 border-b border-slate-100 flex items-center justify-between">
                    <span class="text-sm font-semibold text-slate-700">🔔 {{ __('app.bell.title') }}</span>
                    @if ($pendingRequests->isNotEmpty())
                        <span class="text-xs text-red-600 bg-red-50 px-2 py-0.5 rounded-full">{{ __('app.bell.count_new', ['count' => $pendingRequests->count()]) }}</span>
                    @endif
                </div>
                @forelse ($pendingRequests->take(8) as $req)
                    <a href="#pending-panel"
                       class="block px-4 py-2.5 hover:bg-teal-50 border-b border-slate-50 last:border-0">
                        <div class="flex items-start gap-2">
                            <span class="text-base">🏥</span>
                            <div class="min-w-0">
                                <div class="text-sm text-slate-800 leading-snug">
                                    <span class="font-semibold">{{ $req->client->name }}</span>
                                    {{ __('app.bell.wants_appointment') }}
                                </div>
                                <div class="text-xs text-slate-400">
                                    {{ $req->typeLabel() }}@if ($req->scheduled_at) &middot; {{ $req->scheduled_at->translatedFormat('d M') }} · {{ $req->scheduled_at->format('H:i') }}@endif
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="px-4 py-6 text-center text-sm text-slate-400">{{ __('app.bell.empty') }}</div>
                @endforelse
                @if ($pendingRequests->isNotEmpty())
                    <a href="#pending-panel" class="block px-4 py-2 text-center text-sm font-medium text-teal-700 hover:bg-teal-50 border-t border-slate-100">
                        {{ __('app.bell.view_all') }}
                    </a>
                @endif
            </div>
        </details>
        <button type="button" onclick="toggle('broadcast-modal')"
                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
            📣 {{ __('app.notify.button') }}
        </button>
        <button onclick="toggle('new-appointment')"
                class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
            {{ __('app.appointment.new') }}
        </button>
    </div>
</div>

{{-- New appointment form --}}
<div id="new-appointment" class="hidden bg-white rounded-xl shadow-sm p-5 mb-6">
    <h2 class="font-semibold text-slate-800 mb-4">{{ __('app.appointment.heading') }}</h2>
    <form method="POST" action="{{ route('practice.assistant.appointments.store') }}" class="space-y-4">
        @csrf

        {{-- Existing vs new patient toggle --}}
        <div class="flex gap-4 text-sm">
            <label class="flex items-center gap-1.5">
                <input type="radio" name="patient_mode" value="existing" checked
                       onclick="document.getElementById('ap-existing').classList.remove('hidden');document.getElementById('ap-new').classList.add('hidden');">
                {{ __('app.appointment.existing_patient') }}
            </label>
            <label class="flex items-center gap-1.5">
                <input type="radio" name="patient_mode" value="new"
                       onclick="document.getElementById('ap-new').classList.remove('hidden');document.getElementById('ap-existing').classList.add('hidden');">
                {{ __('app.appointment.new_patient') }}
            </label>
        </div>

        {{-- Existing patient picker: type to filter the list by name or phone.
             The whole list is already in the page, so filtering stays instant
             and needs no round trip. --}}
        <div id="ap-existing">
            <label for="ap-patient-search" class="block text-xs text-slate-500 mb-1">{{ __('app.table.patient') }}</label>
            <div class="relative w-full md:w-1/2">
                <input type="text" id="ap-patient-search" autocomplete="off"
                       placeholder="{{ __('app.appointment.search_patient') }}"
                       role="combobox" aria-expanded="false" aria-controls="ap-patient-list" aria-autocomplete="list"
                       class="w-full border rounded ps-2 pe-8 py-1.5 text-sm">
                <button type="button" id="ap-patient-clear"
                        class="hidden absolute inset-y-0 end-0 px-2 text-lg leading-none text-slate-400 hover:text-slate-600"
                        aria-label="{{ __('app.appointment.clear_patient') }}">&times;</button>
                <input type="hidden" name="client_id" id="ap-patient-id" value="{{ old('client_id') }}">
                <ul id="ap-patient-list" role="listbox"
                    class="hidden absolute z-30 mt-1 w-full max-h-56 overflow-y-auto rounded-lg border border-slate-200 bg-white text-sm shadow-lg"></ul>
            </div>
        </div>

        {{-- New patient fields --}}
        <div id="ap-new" class="hidden grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs text-slate-500 mb-1">{{ __('app.appointment.patient_name') }}</label>
                <input type="text" name="new_patient_name" value="{{ old('new_patient_name') }}"
                       class="w-full border rounded px-2 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">{{ __('app.appointment.patient_phone') }}</label>
                <input type="text" name="new_patient_phone" value="{{ old('new_patient_phone') }}"
                       class="w-full border rounded px-2 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">{{ __('app.appointment.gender') }}</label>
                <select name="new_patient_gender" class="w-full border rounded px-2 py-1.5 text-sm">
                    <option value="male">{{ __('app.genders.male') }}</option>
                    <option value="female">{{ __('app.genders.female') }}</option>
                </select>
            </div>
        </div>

        {{-- Appointment details --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs text-slate-500 mb-1">{{ __('app.appointment.type') }}</label>
                <select name="type" class="w-full border rounded px-2 py-1.5 text-sm">
                    <option value="examination">{{ __('app.types.examination') }}</option>
                    <option value="consultation">{{ __('app.types.consultation') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">{{ __('app.appointment.date_time') }}</label>
                <input type="datetime-local" name="scheduled_at"
                       value="{{ old('scheduled_at', now()->format('Y-m-d\TH:i')) }}"
                       class="w-full border rounded px-2 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">{{ __('app.appointment.reason') }}</label>
                <input type="text" name="reason" value="{{ old('reason') }}"
                       class="w-full border rounded px-2 py-1.5 text-sm">
            </div>
        </div>

        <div class="flex gap-2">
            <button class="bg-emerald-600 text-white text-sm px-4 py-2 rounded-lg">{{ __('app.appointment.create') }}</button>
            <button type="button" onclick="toggle('new-appointment')" class="text-sm text-slate-500 px-3">{{ __('app.appointment.cancel') }}</button>
        </div>
    </form>
</div>

{{-- Now serving: prominent display of the patient currently under examination --}}
<div class="flex justify-center mb-6">
    <div class="w-full md:w-1/2 bg-gradient-to-b from-amber-50 to-white border-2 border-amber-300 rounded-2xl shadow-sm p-6 text-center">
        <div class="text-xs font-semibold uppercase tracking-widest text-amber-600 mb-2">{{ __('app.assistant.now_serving') }}</div>
        @if ($current)
            @php $currentSort = $appointments->search(fn ($a) => $a->id === $current->id) + 1; @endphp
            <div class="text-6xl font-extrabold text-amber-700 leading-none">#{{ $currentSort }}</div>
            <div class="mt-2 text-lg font-semibold text-slate-900">{{ $current->client->name }}</div>
            <div class="text-sm text-slate-500">{{ $current->typeLabel() }} &middot; {{ $current->scheduled_at->format('H:i') }}</div>
        @else
            <div class="text-5xl font-extrabold text-slate-300 leading-none">—</div>
            <div class="mt-2 text-sm text-slate-400 italic">{{ __('app.assistant.no_under_examination') }}</div>
        @endif
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @foreach ([
        [__('app.assistant.stats.total'), $stats['total'], 'text-slate-900'],
        [__('app.assistant.stats.waiting'), $stats['waiting'], 'text-amber-600'],
        [__('app.assistant.stats.completed'), $stats['completed'], 'text-emerald-600'],
        [__('app.assistant.stats.cancelled'), $stats['cancelled'], 'text-red-600'],
    ] as [$label, $value, $color])
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="text-xs uppercase tracking-wide text-slate-400">{{ $label }}</div>
            <div class="text-2xl font-bold {{ $color }}">{{ $value }}</div>
        </div>
    @endforeach
</div>

{{-- Now serving / Next / Next-to-next --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <div class="bg-amber-50 border-2 border-amber-300 rounded-xl p-5">
        <div class="text-xs font-semibold uppercase text-amber-600 mb-1">{{ __('app.assistant.under_examination') }}</div>
        @if ($current)
            <div class="text-lg font-bold text-slate-900">#{{ $current->queue_number }} — {{ $current->client->name }}</div>
            <div class="text-sm text-slate-600">{{ $current->typeLabel() }} &middot; {{ $current->scheduled_at->format('H:i') }}</div>
        @else
            <div class="text-slate-400 italic">{{ __('app.assistant.no_under_examination') }}</div>
        @endif
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-5">
        <div class="text-xs font-semibold uppercase text-slate-400 mb-1">{{ __('app.assistant.next') }}</div>
        @if ($next)
            <div class="text-lg font-bold text-slate-900">#{{ $next->queue_number }} — {{ $next->client->name }}</div>
            <div class="text-sm text-slate-600">{{ $next->typeLabel() }} &middot; {{ $next->scheduled_at->format('H:i') }}</div>
        @else
            <div class="text-slate-400 italic">—</div>
        @endif
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-5">
        <div class="text-xs font-semibold uppercase text-slate-400 mb-1">{{ __('app.assistant.next_next') }}</div>
        @if ($nextNext)
            <div class="text-lg font-bold text-slate-900">#{{ $nextNext->queue_number }} — {{ $nextNext->client->name }}</div>
            <div class="text-sm text-slate-600">{{ $nextNext->typeLabel() }} &middot; {{ $nextNext->scheduled_at->format('H:i') }}</div>
        @else
            <div class="text-slate-400 italic">—</div>
        @endif
    </div>
</div>

{{-- Full queue for the selected day --}}
<div class="flex items-center justify-between mb-3">
    <h2 class="font-semibold text-slate-800">
        {{ $selectedDate->translatedFormat('l, d M Y') }}
        @if ($selectedDate->isToday())
            <span class="ms-2 text-xs font-medium text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">{{ __('app.calendar.today') }}</span>
        @endif
        <span class="ms-1 text-sm font-normal text-slate-400">· {{ $appointments->count() }} {{ __('app.clinic.appointments') }}</span>
    </h2>
    @unless ($selectedDate->isToday())
        <a href="{{ route('practice.assistant.dashboard') }}" class="text-sm text-indigo-600 hover:underline">↩ {{ __('app.calendar.today') }}</a>
    @endunless
</div>

{{-- No overflow-x-auto here: it would clip the row "Actions" dropdown. The
     table has no min-width, so it fits without horizontal scrolling. --}}
<div class="md:bg-white md:rounded-xl md:shadow-sm">
    <table class="w-full text-sm block md:table">
        <thead class="bg-slate-50 text-slate-500 text-left hidden md:table-header-group">
            <tr>
                <th class="px-4 py-3 w-12">{{ __('app.table.num') }}</th>
                <th class="px-4 py-3">{{ __('app.table.patient') }}</th>
                <th class="px-4 py-3">{{ __('app.table.type') }}</th>
                <th class="px-4 py-3">{{ __('app.table.time') }}</th>
                <th class="px-4 py-3">{{ __('app.table.amount') }}</th>
                <th class="px-4 py-3">{{ __('app.table.status') }}</th>
                <th class="px-4 py-3 text-end">{{ __('app.table.actions') }}</th>
            </tr>
        </thead>
        <tbody class="block md:table-row-group md:divide-y md:divide-slate-100">
            @forelse ($appointments as $appt)
                <tr class="block md:table-row rounded-xl shadow-sm md:shadow-none border md:border-x-0 md:border-t-0 border-slate-200 mb-3 md:mb-0 p-3 md:p-0 {{ $appt->status === 'under_examination' ? 'bg-amber-50' : 'bg-white md:bg-transparent' }}">
                    <td class="block md:table-cell md:px-4 md:py-3 font-bold text-slate-400">
                        <span class="md:hidden text-xs uppercase text-slate-400">{{ __('app.table.num') }}: </span>{{ $loop->iteration }}
                    </td>
                    <td class="block md:table-cell md:px-4 md:py-3">
                        {{-- Opens the patient's file: past visits, what they owe,
                             and collecting for visits never paid on the day. --}}
                        <a href="{{ route('practice.patients.show', $appt->client) }}"
                           class="font-semibold text-indigo-700 hover:text-indigo-900 hover:underline text-base md:text-sm">
                            {{ $appt->client->name }}
                        </a>
                        <div class="text-xs text-slate-400">{{ $appt->client->phone_number }}</div>
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
                    {{-- Money: what's due (visit fee + extras) and what's been taken. --}}
                    @php
                        $due = $appt->dueAmount();
                        $paid = $appt->collectedAmount();
                        $left = $appt->remainingAmount();
                    @endphp
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
                    <td class="block md:table-cell md:px-4 md:py-3 border-t border-slate-100 mt-2 pt-3 md:border-0 md:mt-0 md:pt-3">
                        {{-- All row actions in one menu, rather than a wall of icons. --}}
                        <div class="flex justify-start md:justify-end">
                            <details data-menu class="relative">
                                <summary class="list-none cursor-pointer select-none inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-sm text-slate-700">
                                    {{ __('app.table.actions') }}
                                    <span class="text-[10px] text-slate-400">▼</span>
                                </summary>
                                <div class="absolute start-0 md:start-auto md:end-0 mt-2 w-60 max-w-[calc(100vw-2rem)] bg-white border border-slate-200 rounded-xl shadow-lg py-1 z-30 text-start">

                                    {{-- Collect payment --}}
                                    <button type="button" onclick="toggle('collect-{{ $appt->id }}')"
                                            class="w-full flex items-center gap-2 px-4 py-2 text-sm text-emerald-700 hover:bg-emerald-50 text-start font-medium">
                                        <span class="w-5">💵</span> {{ __('app.collection.collect') }}
                                    </button>

                                    {{-- Edit the due amount (visit fee) --}}
                                    <button type="button" onclick="toggle('editfee-{{ $appt->id }}')"
                                            class="w-full flex items-center gap-2 px-4 py-2 text-sm text-indigo-700 hover:bg-indigo-50 text-start font-medium">
                                        <span class="w-5">✏️</span> {{ __('app.items.edit_fee') }}
                                    </button>

                                    {{-- Assign / edit medical insurance for this visit --}}
                                    <button type="button" onclick="toggle('insurance-{{ $appt->id }}')"
                                            class="w-full flex items-center gap-2 px-4 py-2 text-sm text-cyan-700 hover:bg-cyan-50 text-start font-medium">
                                        <span class="w-5">🛡</span> {{ __('app.insurance.assign') }}
                                    </button>

                                    {{-- Change visit type: re-prices from clinic settings --}}
                                    <div class="border-t border-slate-100 my-1"></div>
                                    <div class="px-4 py-1 text-[11px] uppercase tracking-wide text-slate-400">{{ __('app.appointment.change_type') }}</div>
                                    @foreach (['examination', 'consultation'] as $t)
                                        <form method="POST" action="{{ route('practice.appointments.type', $appt) }}">
                                            @csrf
                                            <input type="hidden" name="type" value="{{ $t }}">
                                            <button class="w-full flex items-center justify-between gap-2 px-4 py-2 text-sm text-start hover:bg-slate-50
                                                           {{ $appt->type === $t ? 'font-semibold text-indigo-700 bg-indigo-50/60' : 'text-slate-700' }}">
                                                <span>{{ __('app.types.'.$t) }}</span>
                                                @if ($appt->type === $t)<span class="text-indigo-600">✓</span>@endif
                                            </button>
                                        </form>
                                    @endforeach
                                    <div class="border-t border-slate-100 my-1"></div>

                                    {{-- View profile --}}
                                    <a href="{{ route('practice.patients.show', $appt->client) }}"
                                       class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                        <span class="w-5">👤</span> {{ __('app.assistant.view_profile') }}
                                    </a>

                                    {{-- Print queue ticket/receipt --}}
                                    <a href="{{ route('practice.appointments.ticket', $appt) }}" target="_blank"
                                       class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                        <span class="w-5">🧾</span> {{ __('app.ticket.print') }}
                                    </a>

                                    {{-- Print on the clinic's Bluetooth ticket printer (via staff mobile app) --}}
                                    <button type="button" onclick="printTicketBt({{ $appt->id }}, this)"
                                            class="w-full flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 text-start">
                                        <span class="w-5">🎫</span> {{ __('app.ticket.print_bt') }}
                                    </button>

                                    {{-- Edit patient file --}}
                                    <button type="button" onclick="toggle('edit-{{ $appt->id }}')"
                                            class="w-full flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 text-start">
                                        <span class="w-5">✏️</span> {{ __('app.patient.edit') }}
                                    </button>

                                    {{-- Upload attachment --}}
                                    <button type="button" onclick="toggle('upload-{{ $appt->id }}')"
                                            class="w-full flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 text-start">
                                        <span class="w-5">📎</span> {{ __('app.assistant.upload_attachment') }}
                                    </button>

                                    {{-- Print prescription --}}
                                    @if ($appt->prescriptions->isNotEmpty())
                                        <a href="{{ route('practice.prescriptions.print', $appt->prescriptions->last()) }}" target="_blank"
                                           class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                            <span class="w-5">🖨️</span> {{ __('app.assistant.print_prescription') }}
                                        </a>
                                    @else
                                        <span class="flex items-center gap-2 px-4 py-2 text-sm text-slate-300 cursor-not-allowed">
                                            <span class="w-5">🖨️</span> {{ __('app.assistant.no_prescription') }}
                                        </span>
                                    @endif

                                    {{-- Start examination (also lets a no-show patient who turned up be seen) --}}
                                    @if (in_array($appt->status, ['scheduled', 'escaped']))
                                        <div class="border-t border-slate-100 my-1"></div>
                                        <form method="POST" action="{{ route('practice.appointments.status', $appt) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="under_examination">
                                            <button class="w-full flex items-center gap-2 px-4 py-2 text-sm text-amber-700 hover:bg-amber-50 text-start">
                                                <span class="w-5">▶</span> {{ __('app.assistant.start_examination') }}
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Complete / Cancel: available for any non-final appointment, including no-shows --}}
                                    @if (! in_array($appt->status, ['completed', 'cancelled']))
                                        @if (! in_array($appt->status, ['scheduled', 'escaped']))
                                            <div class="border-t border-slate-100 my-1"></div>
                                        @endif
                                        {{-- Confirm a platform booking request into the day's schedule
                                             (moves it out of the pending/notifications list) --}}
                                        @if (in_array($appt->status, ['pending', 'confirmed']))
                                            <form method="POST" action="{{ route('practice.appointments.status', $appt) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="scheduled">
                                                <button class="w-full flex items-center gap-2 px-4 py-2 text-sm text-blue-700 hover:bg-blue-50 text-start">
                                                    <span class="w-5">📅</span> {{ __('app.assistant.confirm_booking') }}
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('practice.appointments.status', $appt) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="completed">
                                            <button class="w-full flex items-center gap-2 px-4 py-2 text-sm text-emerald-700 hover:bg-emerald-50 text-start">
                                                <span class="w-5">✔</span> {{ __('app.assistant.mark_completed') }}
                                            </button>
                                        </form>
                                        {{-- Escape (no-show: patient didn't attend and didn't cancel) --}}
                                        @if ($appt->status !== 'escaped')
                                            <form method="POST" action="{{ route('practice.appointments.status', $appt) }}"
                                                  onsubmit="return confirm('{{ __('app.assistant.confirm_escape') }}')">
                                                @csrf
                                                <input type="hidden" name="status" value="escaped">
                                                <button class="w-full flex items-center gap-2 px-4 py-2 text-sm text-orange-700 hover:bg-orange-50 text-start">
                                                    <span class="w-5">⏷</span> {{ __('app.assistant.escape') }}
                                                </button>
                                            </form>
                                        @endif
                                        {{-- Cancel --}}
                                        <form method="POST" action="{{ route('practice.appointments.status', $appt) }}"
                                              onsubmit="return confirm('{{ __('app.assistant.confirm_cancel') }}')">
                                            @csrf
                                            <input type="hidden" name="status" value="cancelled">
                                            <button class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-700 hover:bg-red-50 text-start">
                                                <span class="w-5">✖</span> {{ __('app.assistant.cancel') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </details>
                        </div>
                    </td>
                </tr>

                {{-- Collect payment. The amount defaults to what's still owed,
                     which on the first payment is the clinic's price for this
                     visit type — so the usual case is just "confirm". --}}
                <tr id="collect-{{ $appt->id }}" class="hidden">
                    <td colspan="7" class="block md:table-cell px-4 py-4 bg-emerald-50/50 rounded-xl md:rounded-none mb-3 md:mb-0">
                        <form method="POST" action="{{ route('practice.collections.store', $appt) }}"
                              class="flex flex-wrap items-end gap-3">
                            @csrf
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">{{ __('app.collection.amount') }} ({{ __('app.clinic.currency') }})</label>
                                <input type="number" name="amount" step="0.01" min="0.01" required
                                       value="{{ $left > 0 ? number_format($left, 2, '.', '') : number_format($due, 2, '.', '') }}"
                                       class="w-32 border rounded px-2 py-1.5 text-sm">
                            </div>
                            <div class="grow max-w-xs">
                                <label class="block text-xs text-slate-500 mb-1">{{ __('app.collection.note') }}</label>
                                <input type="text" name="note" class="w-full border rounded px-2 py-1.5 text-sm">
                            </div>
                            <button class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm px-4 py-2 rounded-lg">
                                {{ __('app.collection.submit') }}
                            </button>
                            <button type="button" onclick="toggle('collect-{{ $appt->id }}')"
                                    class="text-sm text-slate-500 px-2">{{ __('app.collection.cancel') }}</button>

                            <div class="w-full text-xs text-slate-500">
                                {{ __('app.collection.due') }}: <strong>{{ number_format($due, 2) }}</strong>
                                &middot; {{ __('app.collection.collected') }}: <strong>{{ number_format($paid, 2) }}</strong>
                                &middot; {{ __('app.collection.remaining') }}: <strong>{{ number_format($left, 2) }}</strong>
                            </div>
                        </form>

                        @if ($appt->collections->isNotEmpty())
                            <div class="mt-3 pt-3 border-t border-emerald-100">
                                <div class="text-xs font-semibold text-slate-500 mb-1">{{ __('app.collection.history') }}</div>
                                <ul class="space-y-1">
                                    @foreach ($appt->collections->sortBy('collected_at') as $col)
                                        <li class="flex items-center gap-2 text-xs text-slate-600">
                                            <span class="font-semibold text-slate-800">{{ number_format((float) $col->amount, 2) }} {{ __('app.clinic.currency') }}</span>
                                            <span class="text-slate-400">{{ $col->collected_at?->format('H:i') }}</span>
                                            @if ($col->collector)
                                                <span class="text-slate-400">{{ __('app.collection.by', ['name' => $col->collector->name]) }}</span>
                                            @endif
                                            @if ($col->note)<span class="text-slate-400 italic">— {{ $col->note }}</span>@endif
                                            <form method="POST" action="{{ route('practice.collections.destroy', $col) }}"
                                                  onsubmit="return confirm('{{ __('app.collection.delete_confirm') }}')">
                                                @csrf @method('DELETE')
                                                <button class="text-red-500 hover:text-red-700">✖</button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </td>
                </tr>

                {{-- Edit the visit fee (the due amount). Extras and payments
                     already taken are untouched; a discount below what's paid
                     surfaces a refund due. --}}
                <tr id="editfee-{{ $appt->id }}" class="hidden">
                    <td colspan="7" class="block md:table-cell px-4 py-4 bg-indigo-50/50 rounded-xl md:rounded-none mb-3 md:mb-0">
                        <form method="POST" action="{{ route('practice.appointments.price', $appt) }}"
                              class="flex flex-wrap items-end gap-3">
                            @csrf
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">{{ __('app.items.visit_fee') }} ({{ __('app.clinic.currency') }})</label>
                                <input type="number" name="price" step="0.01" min="0" required
                                       value="{{ number_format($appt->visitPrice(), 2, '.', '') }}"
                                       class="w-32 border rounded px-2 py-1.5 text-sm">
                            </div>
                            <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg">
                                {{ __('app.items.save_fee') }}
                            </button>
                            <button type="button" onclick="toggle('editfee-{{ $appt->id }}')"
                                    class="text-sm text-slate-500 px-2">{{ __('app.collection.cancel') }}</button>
                            <p class="w-full text-xs text-amber-600">{{ __('app.items.fee_reprice_warning') }}</p>
                        </form>
                    </td>
                </tr>

                {{-- Assign medical insurance: pick an existing company or add a
                     new one, and split the amount between patient and insurer. --}}
                <tr id="insurance-{{ $appt->id }}" class="hidden">
                    <td colspan="7" class="block md:table-cell px-4 py-4 bg-cyan-50/50 rounded-xl md:rounded-none mb-3 md:mb-0">
                        @php $ins = $appt->insurance; @endphp
                        <form method="POST" action="{{ route('practice.appointments.insurance.store', $appt) }}"
                              class="flex flex-wrap items-end gap-3">
                            @csrf
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">{{ __('app.insurance.company') }}</label>
                                <select name="insurance_company_id" class="w-52 border rounded px-2 py-1.5 text-sm">
                                    <option value="">— {{ __('app.insurance.select_company') }} —</option>
                                    @foreach ($insuranceCompanies as $co)
                                        <option value="{{ $co->id }}" @selected($ins && $ins->insurance_company_id === $co->id)>{{ $co->displayName() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">{{ __('app.insurance.or_new_company') }}</label>
                                <input type="text" name="new_company_name" placeholder="{{ __('app.insurance.new_company_name') }}"
                                       class="w-44 border rounded px-2 py-1.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">{{ __('app.insurance.patient_amount') }}</label>
                                <input type="number" name="patient_amount" step="0.01" min="0" required
                                       value="{{ $ins ? number_format((float) $ins->patient_amount, 2, '.', '') : '' }}"
                                       class="w-28 border rounded px-2 py-1.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">{{ __('app.insurance.insurance_amount') }}</label>
                                <input type="number" name="insurance_amount" step="0.01" min="0" required
                                       value="{{ $ins ? number_format((float) $ins->insurance_amount, 2, '.', '') : '' }}"
                                       class="w-28 border rounded px-2 py-1.5 text-sm">
                            </div>
                            <button class="bg-cyan-600 hover:bg-cyan-700 text-white text-sm px-4 py-2 rounded-lg">
                                {{ __('app.insurance.save') }}
                            </button>
                            <button type="button" onclick="toggle('insurance-{{ $appt->id }}')"
                                    class="text-sm text-slate-500 px-2">{{ __('app.insurance.cancel') }}</button>
                        </form>

                        @if ($ins)
                            <div class="mt-3 pt-3 border-t border-cyan-100 flex items-center gap-3 text-xs text-slate-600">
                                <span>{{ __('app.insurance.current') }}:
                                    <strong>{{ $ins->insuranceCompany?->displayName() }}</strong>
                                    · {{ __('app.insurance.patient_amount') }} {{ number_format((float) $ins->patient_amount, 2) }}
                                    · {{ __('app.insurance.insurance_amount') }} {{ number_format((float) $ins->insurance_amount, 2) }}
                                </span>
                                <form method="POST" action="{{ route('practice.appointments.insurance.destroy', $appt) }}"
                                      onsubmit="return confirm('{{ __('app.insurance.remove_confirm') }}')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:text-red-800">✖ {{ __('app.insurance.remove') }}</button>
                                </form>
                            </div>
                        @endif
                    </td>
                </tr>

                {{-- Upload modal --}}
                <tr id="upload-{{ $appt->id }}" class="hidden">
                    <td colspan="7" class="block md:table-cell px-4 py-4 bg-slate-50 rounded-xl md:rounded-none mb-3 md:mb-0">
                        <form method="POST" action="{{ route('practice.attachments.store', $appt) }}" enctype="multipart/form-data"
                              class="flex flex-wrap items-end gap-3">
                            @csrf
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">{{ __('app.assistant.upload.title') }}</label>
                                <input type="text" name="title" placeholder="{{ __('app.assistant.upload.title_placeholder') }}"
                                       class="border rounded px-2 py-1 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">{{ __('app.assistant.upload.type') }}</label>
                                <select name="type" class="border rounded px-2 py-1 text-sm">
                                    <option value="report">{{ __('app.assistant.upload.report') }}</option>
                                    <option value="lab">{{ __('app.assistant.upload.lab') }}</option>
                                    <option value="scan">{{ __('app.assistant.upload.scan') }}</option>
                                    <option value="other">{{ __('app.assistant.upload.other') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">{{ __('app.assistant.upload.file') }}</label>
                                <input type="file" name="file" required class="text-sm">
                            </div>
                            <button class="bg-blue-600 text-white text-sm px-3 py-1.5 rounded">{{ __('app.assistant.upload.submit') }}</button>
                            <button type="button" onclick="toggle('upload-{{ $appt->id }}')"
                                    class="text-sm text-slate-500">{{ __('app.assistant.upload.cancel') }}</button>
                        </form>
                    </td>
                </tr>

                {{-- Edit patient file --}}
                <tr id="edit-{{ $appt->id }}" class="hidden">
                    <td colspan="7" class="block md:table-cell px-4 py-4 bg-indigo-50/50 rounded-xl md:rounded-none mb-3 md:mb-0">
                        @php $ep = $appt->client; @endphp
                        <form method="POST" action="{{ route('practice.patients.update', $ep) }}" class="space-y-3">
                            @csrf @method('PUT')
                            <h3 class="font-semibold text-slate-800">{{ __('app.patient.edit_heading') }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1">{{ __('app.patient.name') }}</label>
                                    <input type="text" name="name" required value="{{ old('name', $ep->name) }}"
                                           class="w-full border rounded px-2 py-1.5 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1">{{ __('app.common.gender') }}</label>
                                    <select name="gender" class="w-full border rounded px-2 py-1.5 text-sm">
                                        <option value="male" @selected($ep->gender === 'male')>{{ __('app.genders.male') }}</option>
                                        <option value="female" @selected($ep->gender === 'female')>{{ __('app.genders.female') }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1">{{ __('app.patient.dob') }}</label>
                                    <input type="date" name="dob" value="{{ old('dob', $ep->dob?->format('Y-m-d')) }}"
                                           class="w-full border rounded px-2 py-1.5 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1">{{ __('app.common.phone') }}</label>
                                    <input type="text" name="phone_number" value="{{ old('phone_number', $ep->phone_number) }}"
                                           class="w-full border rounded px-2 py-1.5 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1">{{ __('app.common.email') }}</label>
                                    <input type="email" name="email" value="{{ old('email', $ep->email) }}"
                                           class="w-full border rounded px-2 py-1.5 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1">{{ __('app.common.national_id') }}</label>
                                    <input type="text" name="national_id" value="{{ old('national_id', $ep->national_id) }}"
                                           class="w-full border rounded px-2 py-1.5 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1">{{ __('app.common.blood_type') }}</label>
                                    <input type="text" name="blood_type" value="{{ old('blood_type', $ep->blood_type) }}"
                                           class="w-full border rounded px-2 py-1.5 text-sm">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs text-slate-500 mb-1">{{ __('app.common.address') }}</label>
                                    <input type="text" name="address" value="{{ old('address', $ep->address) }}"
                                           class="w-full border rounded px-2 py-1.5 text-sm">
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block text-xs text-slate-500 mb-1">{{ __('app.common.allergies') }} <span class="text-slate-400">({{ __('app.patient.one_per_line') }})</span></label>
                                    <textarea name="allergies" rows="2"
                                              class="w-full border rounded px-2 py-1.5 text-sm">{{ old('allergies', implode("\n", (array) $ep->allergies)) }}</textarea>
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block text-xs text-slate-500 mb-1">{{ __('app.common.chronic_diseases') }} <span class="text-slate-400">({{ __('app.patient.one_per_line') }})</span></label>
                                    <textarea name="chronic_diseases" rows="2"
                                              class="w-full border rounded px-2 py-1.5 text-sm">{{ old('chronic_diseases', implode("\n", (array) $ep->chronic_diseases)) }}</textarea>
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block text-xs text-slate-500 mb-1">{{ __('app.patient.medical_history') }}</label>
                                    <textarea name="medical_history" rows="2"
                                              class="w-full border rounded px-2 py-1.5 text-sm">{{ old('medical_history', $ep->medical_history) }}</textarea>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-lg">{{ __('app.patient.save') }}</button>
                                <button type="button" onclick="toggle('edit-{{ $appt->id }}')" class="text-sm text-slate-500 px-3">{{ __('app.patient.cancel') }}</button>
                            </div>
                        </form>
                    </td>
                </tr>
            @empty
                <tr class="block md:table-row">
                    <td colspan="7" class="block md:table-cell px-4 py-10 text-center text-slate-400 bg-white rounded-xl md:rounded-none">
                        {{ __('app.assistant.empty') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Month calendar: green = clinic open, badge = booked appointments. Click a day to view it. --}}
<div class="bg-white rounded-xl shadow-sm p-3 sm:p-5 mt-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-slate-800">{{ $calendar['month']->translatedFormat('F Y') }}</h2>
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('practice.assistant.dashboard', ['month' => $calendar['prev']]) }}"
               class="px-3 py-1 rounded border border-slate-300 hover:bg-slate-50">‹</a>
            <a href="{{ route('practice.assistant.dashboard') }}"
               class="px-3 py-1 rounded border border-slate-300 hover:bg-slate-50">{{ __('app.calendar.today') }}</a>
            <a href="{{ route('practice.assistant.dashboard', ['month' => $calendar['next']]) }}"
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
                <a href="{{ route('practice.assistant.dashboard', ['month' => $calendar['month']->format('Y-m'), 'date' => $day['date']->format('Y-m-d')]) }}"
                   class="relative block min-h-[46px] sm:min-h-[76px] rounded-md sm:rounded-lg border p-1 sm:p-1.5 transition hover:shadow-sm hover:border-indigo-300
                    {{ $day['isOpen'] ? 'bg-emerald-50 border-emerald-200' : ($closedWithAppts ? 'bg-red-50 border-red-200' : 'bg-slate-50 border-slate-100') }}
                    {{ $day['inMonth'] ? '' : 'opacity-40' }}
                    {{ $day['isSelected'] ? 'ring-2 ring-indigo-600 border-indigo-400' : ($day['isToday'] ? 'ring-2 ring-indigo-300' : '') }}">
                    <span class="absolute top-0.5 start-1 sm:top-1 sm:start-2 text-[10px] sm:text-xs {{ $day['isOpen'] ? 'text-emerald-700 font-semibold' : ($closedWithAppts ? 'text-red-700 font-semibold' : 'text-slate-400') }}">
                        {{ $day['date']->day }}
                    </span>
                    @if ($day['pending'] > 0)
                        <span class="absolute top-0.5 end-0.5 sm:top-1 sm:end-1 inline-flex items-center justify-center min-w-[14px] h-[14px] sm:min-w-[18px] sm:h-[18px] px-1 rounded-full bg-amber-500 text-white text-[8px] sm:text-[10px] font-bold leading-none"
                              title="{{ $day['pending'] }} {{ __('app.mostashfa.pending_title') }}">
                            {{ $day['pending'] }}
                        </span>
                    @endif
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
        <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full bg-amber-500"></span> {{ __('app.mostashfa.pending_title') }}</span>
    </div>
</div>

{{-- Pending booking requests from Mostashfa On — confirm or decline each --}}
@if ($pendingRequests->isNotEmpty())
<div id="pending-panel" class="bg-white rounded-xl shadow-sm border border-teal-200 mt-6 overflow-x-auto">
    <div class="flex items-center justify-between bg-teal-50 px-5 py-3 border-b border-teal-100">
        <div>
            <h2 class="font-semibold text-teal-800">
                🏥 {{ __('app.mostashfa.pending_title') }}
                <span id="pending-count" class="ms-1 inline-block text-xs font-medium text-teal-700 bg-teal-100 px-2 py-0.5 rounded-full">{{ $pendingRequests->count() }}</span>
            </h2>
            <p class="text-xs text-teal-600">{{ __('app.mostashfa.pending_subtitle') }}</p>
        </div>
    </div>
    <table class="w-full text-sm block md:table">
        <thead class="bg-slate-50 text-slate-500 text-left hidden md:table-header-group">
            <tr>
                <th class="px-4 py-2">{{ __('app.table.patient') }}</th>
                <th class="px-4 py-2">{{ __('app.table.type') }}</th>
                <th class="px-4 py-2">{{ __('app.mostashfa.requested_for') }}</th>
                <th class="px-4 py-2">{{ __('app.appointment.reason') }}</th>
                <th class="px-4 py-2 text-end">{{ __('app.table.actions') }}</th>
            </tr>
        </thead>
        @php $pendingLimit = 10; @endphp
        <tbody class="block md:table-row-group md:divide-y md:divide-slate-100">
            @foreach ($pendingRequests as $req)
                <tr @class([
                        'block md:table-row border md:border-x-0 md:border-t-0 border-slate-200 p-3 md:p-0 mb-3 md:mb-0 bg-white md:bg-transparent',
                        'hidden pending-extra' => $loop->iteration > $pendingLimit,
                    ])>
                    <td class="block md:table-cell md:px-4 md:py-3">
                        <div class="font-semibold text-slate-900">{{ $req->client->name }}</div>
                        @if ($req->client->phone_number)
                            <div class="text-xs text-slate-400">{{ $req->client->phone_number }}</div>
                        @endif
                    </td>
                    <td class="flex justify-between md:table-cell md:px-4 md:py-3 border-t border-slate-100 mt-2 pt-2 md:border-0 md:mt-0 md:pt-0">
                        <span class="md:hidden text-xs uppercase text-slate-400">{{ __('app.table.type') }}</span>
                        <span>{{ $req->typeLabel() }}</span>
                    </td>
                    <td class="flex justify-between md:table-cell md:px-4 md:py-3 text-slate-600">
                        <span class="md:hidden text-xs uppercase text-slate-400">{{ __('app.mostashfa.requested_for') }}</span>
                        <span>
                        @if ($req->scheduled_at)
                            {{ $req->scheduled_at->translatedFormat('d M') }} &middot; {{ $req->scheduled_at->format('H:i') }}
                        @else
                            —
                        @endif
                        </span>
                    </td>
                    <td class="flex justify-between md:table-cell md:px-4 md:py-3 text-slate-500">
                        <span class="md:hidden text-xs uppercase text-slate-400">{{ __('app.appointment.reason') }}</span>
                        <span>{{ $req->reason }}</span>
                    </td>
                    <td class="block md:table-cell md:px-4 md:py-3 border-t border-slate-100 mt-2 pt-3 md:border-0 md:mt-0 md:pt-3">
                        <div class="flex items-center justify-start md:justify-end gap-2">
                            <form method="POST" action="{{ route('practice.assistant.pending.confirm') }}">
                                @csrf
                                <input type="hidden" name="appointment_id" value="{{ $req->id }}">
                                <button class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium">
                                    ✔ {{ __('app.mostashfa.confirm') }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('practice.assistant.pending.cancel') }}"
                                  onsubmit="return confirm('{{ __('app.mostashfa.confirm_decline') }}')">
                                @csrf
                                <input type="hidden" name="appointment_id" value="{{ $req->id }}">
                                <button class="px-3 py-1.5 rounded-lg bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium">
                                    ✖ {{ __('app.mostashfa.decline') }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if ($pendingRequests->count() > $pendingLimit)
        <div class="border-t border-teal-100 px-5 py-3 text-center">
            <button type="button"
                    onclick="document.querySelectorAll('.pending-extra').forEach(el => el.classList.remove('hidden')); this.parentElement.remove();"
                    class="text-sm font-medium text-teal-700 hover:text-teal-900 hover:underline">
                {{ __('app.mostashfa.show_all', ['count' => $pendingRequests->count()]) }}
            </button>
        </div>
    @endif
</div>
@endif

@include('clinic.partials.broadcast-modal')

{{-- Existing-patient combobox: filters the in-page patient list by name or by
     phone. Phone matching compares digits only, so "0100 123" still finds
     "01001234567" however the number was typed in. --}}
@push('scripts')
<script>
    (function () {
        var PATIENTS = @json($patients->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'phone' => $p->phone_number])->values());
        var NO_MATCHES = @json(__('app.appointment.no_patient_matches'));

        var search = document.getElementById('ap-patient-search');
        var list = document.getElementById('ap-patient-list');
        var hidden = document.getElementById('ap-patient-id');
        var clear = document.getElementById('ap-patient-clear');
        if (!search || !list || !hidden) return;

        var matches = [];
        var active = -1;

        function digits(value) { return String(value || '').replace(/\D+/g, ''); }

        function label(p) { return p.phone ? p.name + ' — ' + p.phone : p.name; }

        function find(query) {
            var text = query.trim().toLowerCase();
            if (!text) return PATIENTS.slice(0, 50);

            var number = digits(text);
            return PATIENTS.filter(function (p) {
                if (String(p.name || '').toLowerCase().indexOf(text) !== -1) return true;
                return number !== '' && digits(p.phone).indexOf(number) !== -1;
            }).slice(0, 50);
        }

        function close() {
            list.classList.add('hidden');
            search.setAttribute('aria-expanded', 'false');
            active = -1;
        }

        function highlight() {
            Array.prototype.forEach.call(list.children, function (li, i) {
                li.classList.toggle('bg-emerald-50', i === active);
                if (i === active) li.scrollIntoView({ block: 'nearest' });
            });
        }

        function render(query) {
            matches = find(query);
            list.innerHTML = '';

            if (!matches.length) {
                var empty = document.createElement('li');
                empty.className = 'px-3 py-2 text-slate-400';
                empty.textContent = NO_MATCHES;
                list.appendChild(empty);
            } else {
                matches.forEach(function (p, i) {
                    var li = document.createElement('li');
                    li.className = 'cursor-pointer px-3 py-2 hover:bg-emerald-50';
                    li.setAttribute('role', 'option');
                    li.textContent = label(p);
                    // mousedown, not click: blur would close the list first.
                    li.addEventListener('mousedown', function (e) { e.preventDefault(); pick(i); });
                    list.appendChild(li);
                });
            }

            active = -1;
            list.classList.remove('hidden');
            search.setAttribute('aria-expanded', 'true');
        }

        function pick(index) {
            var p = matches[index];
            if (!p) return;
            hidden.value = p.id;
            search.value = label(p);
            clear.classList.remove('hidden');
            close();
        }

        function reset() {
            hidden.value = '';
            search.value = '';
            clear.classList.add('hidden');
            close();
        }

        search.addEventListener('input', function () {
            // Typing after a pick means the assistant is choosing again.
            hidden.value = '';
            clear.classList.add('hidden');
            render(search.value);
        });

        search.addEventListener('focus', function () { render(search.value); });
        search.addEventListener('blur', close);

        search.addEventListener('keydown', function (e) {
            var open = !list.classList.contains('hidden');

            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                if (!open) { render(search.value); return; }
                e.preventDefault();
                if (!matches.length) return;
                active = e.key === 'ArrowDown'
                    ? (active + 1) % matches.length
                    : (active <= 0 ? matches.length : active) - 1;
                highlight();
            } else if (e.key === 'Enter') {
                // Never let Enter submit the form while the list is open.
                if (open && active >= 0) { e.preventDefault(); pick(active); }
                else if (open) { e.preventDefault(); close(); }
            } else if (e.key === 'Escape') {
                if (open) { e.preventDefault(); close(); }
            }
        });

        clear.addEventListener('click', function () { reset(); search.focus(); });

        // Restore the pick after a validation round trip.
        if (hidden.value) {
            var current = PATIENTS.filter(function (p) { return String(p.id) === String(hidden.value); })[0];
            if (current) {
                search.value = label(current);
                clear.classList.remove('hidden');
            }
        }
    })();
</script>
@endpush

@if ($errors->any() && old('scheduled_at'))
    {{-- Re-open the new-appointment form so the validation errors make sense. --}}
    @push('scripts')
    <script>
        document.getElementById('new-appointment')?.classList.remove('hidden');
        @if (old('new_patient_name'))
            document.getElementById('ap-new')?.classList.remove('hidden');
            document.getElementById('ap-existing')?.classList.add('hidden');
        @endif
    </script>
    @endpush
@endif

{{-- Re-check the ticket-printer connection. The staff app heartbeats every few
     minutes and hasConnectedPrinter() allows a 10-minute window, so a 30s poll
     is well inside it. --}}
@push('scripts')
<script>
    (function () {
        var URL = @json(route('practice.assistant.printer.status'));
        var ON = @json(__('app.ticket.printer_online'));
        var OFF = @json(__('app.ticket.printer_offline'));
        var ON_HINT = @json(__('app.ticket.printer_online_hint'));
        var OFF_HINT = @json(__('app.ticket.printer_offline_hint'));
        var LAST_SEEN = @json(__('app.ticket.printer_last_seen', ['time' => ':time']));

        var box = document.getElementById('printer-status');
        var dot = document.getElementById('printer-dot');
        var text = document.getElementById('printer-text');
        if (!box) return;

        var BASE = 'inline-flex items-center gap-1.5 text-sm font-medium px-3 py-2 rounded-lg border ';

        function render(connected, lastSeen) {
            box.className = BASE + (connected
                ? 'bg-emerald-50 border-emerald-300 text-emerald-800'
                : 'bg-slate-100 border-slate-300 text-slate-500');
            dot.className = 'w-2 h-2 rounded-full ' + (connected ? 'bg-emerald-500' : 'bg-slate-400');
            text.textContent = '🖨️ ' + (connected ? ON : OFF);
            var hint = connected ? ON_HINT : OFF_HINT;
            if (lastSeen) { hint += ' — ' + LAST_SEEN.replace(':time', lastSeen); }
            box.title = hint;
        }

        function poll() {
            fetch(URL, { headers: { 'Accept': 'application/json' }, cache: 'no-store' })
                .then(function (r) { return r.json(); })
                .then(function (d) { render(!!d.connected, d.last_seen); })
                .catch(function () { /* keep the last known state */ });
        }

        setInterval(poll, 30000);
        // Catch up immediately when the tab is focused again.
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) poll();
        });
    })();
</script>
@endpush

{{-- Send a queue ticket to the clinic's Bluetooth printer (FCM → staff mobile app). --}}
@push('scripts')
<script>
    function printTicketBt(appointmentId, btn) {
        btn.disabled = true;
        fetch(`{{ url('practice/assistant/appointments') }}/${appointmentId}/print-ticket`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
        .then(res => res.ok ? res.json() : Promise.reject(res))
        .then(() => {
            btn.textContent = '✅';
            setTimeout(() => { btn.textContent = '🎫'; btn.disabled = false; }, 2000);
        })
        .catch(() => {
            btn.disabled = false;
            alert(@json(__('app.ticket.print_bt_failed')));
        });
    }
</script>
@endpush

{{-- Confirm / decline pending requests without a full page reload. --}}
@push('scripts')
<script>
    document.addEventListener('submit', function (e) {
        const form = e.target.closest('form');
        const panel = document.getElementById('pending-panel');
        if (!form || !panel || !panel.contains(form)) return;
        if (e.defaultPrevented) return; // decline dialog was cancelled

        e.preventDefault();
        const row = form.closest('tr');
        const buttons = form.querySelectorAll('button');
        buttons.forEach(b => b.disabled = true);

        fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: new FormData(form),
        })
        .then(res => res.ok ? res.json() : Promise.reject(res))
        .then(data => {
            if (row) row.remove();
            const badge = document.getElementById('pending-count');
            if (badge && typeof data.remaining !== 'undefined') badge.textContent = data.remaining;
            if (data.remaining === 0) panel.remove();
        })
        .catch(() => {
            buttons.forEach(b => b.disabled = false);
            alert(@json(__('app.mostashfa.request_gone')));
        });
    });
</script>
@endpush
@endsection
