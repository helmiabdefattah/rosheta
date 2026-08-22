@extends('clinic.layouts.app')

@section('content')
@php $p = $patient; @endphp

<a href="{{ url()->previous() }}" class="text-sm text-indigo-600 hover:underline">{{ __('app.common.back') }}</a>

<div class="bg-white rounded-xl shadow-sm p-6 mt-2 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $p->name }}</h1>
            <p class="text-slate-500 text-sm">{{ $p->gender ? __('app.genders.'.$p->gender) : '—' }} &middot; {{ $p->age ?? '—' }} {{ __('app.common.yrs') }}</p>
        </div>
        <span class="self-start text-xs bg-red-50 text-red-700 px-3 py-1 rounded-full">{{ __('app.patient.allergies_label', ['value' => filled($p->allergies) ? implode('، ', (array) $p->allergies) : __('app.common.none')]) }}</span>
    </div>
    <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4 text-sm">
        <div><dt class="text-slate-400">{{ __('app.common.phone') }}</dt><dd>{{ $p->phone_number ?? '—' }}</dd></div>
        <div><dt class="text-slate-400">{{ __('app.common.email') }}</dt><dd>{{ $p->email ?? '—' }}</dd></div>
        <div><dt class="text-slate-400">{{ __('app.common.national_id') }}</dt><dd>{{ $p->national_id ?? '—' }}</dd></div>
        <div><dt class="text-slate-400">{{ __('app.common.blood_type') }}</dt><dd>{{ $p->blood_type ?? '—' }}</dd></div>
        <div class="col-span-2"><dt class="text-slate-400">{{ __('app.common.address') }}</dt><dd>{{ $p->address ?? '—' }}</dd></div>
        <div class="col-span-2"><dt class="text-slate-400">{{ __('app.common.chronic_diseases') }}</dt><dd>{{ filled($p->chronic_diseases) ? implode('، ', (array) $p->chronic_diseases) : '—' }}</dd></div>
        <div class="col-span-2"><dt class="text-slate-400">{{ __('app.patient.medical_history') }}</dt><dd>{{ $p->medical_history ?? '—' }}</dd></div>
    </dl>
</div>

{{-- Money across every visit: what each one came to, what was taken, and what
     is still owed — including visits never collected on the day. --}}
<div class="bg-white rounded-xl shadow-sm p-5 mb-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h2 class="font-semibold text-slate-800">💵 {{ __('app.patient.collections_heading') }}</h2>
        <div class="flex flex-wrap items-center gap-4 text-sm">
            <span class="text-slate-500">{{ __('app.collection.due') }}:
                <strong class="text-slate-800">{{ number_format($totals['due'], 2) }}</strong></span>
            <span class="text-slate-500">{{ __('app.collection.collected') }}:
                <strong class="text-emerald-700">{{ number_format($totals['collected'], 2) }}</strong></span>
            <span class="px-3 py-1 rounded-full font-semibold
                {{ $totals['outstanding'] > 0 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                {{ __('app.patient.outstanding') }}: {{ number_format($totals['outstanding'], 2) }} {{ __('app.clinic.currency') }}
            </span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-left">
                <tr>
                    <th class="px-3 py-2">{{ __('app.patient.visit') }}</th>
                    <th class="px-3 py-2">{{ __('app.table.type') }}</th>
                    <th class="px-3 py-2 text-end">{{ __('app.collection.due') }}</th>
                    <th class="px-3 py-2 text-end">{{ __('app.collection.collected') }}</th>
                    <th class="px-3 py-2">{{ __('app.table.status') }}</th>
                    <th class="px-3 py-2 text-end">{{ __('app.table.action') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($appointments as $a)
                    @php
                        $aDue = $a->dueAmount();
                        $aPaid = $a->collectedAmount();
                        $aLeft = $a->remainingAmount();
                        $cancelled = $a->status === 'cancelled';
                    @endphp
                    <tr class="{{ $aLeft > 0 && ! $cancelled ? 'bg-amber-50/40' : '' }}">
                        <td class="px-3 py-2 whitespace-nowrap">
                            <div class="text-slate-800">{{ $a->scheduled_at->translatedFormat('d M Y') }}</div>
                            <div class="text-xs text-slate-400">{{ $a->scheduled_at->format('H:i') }} @if ($a->clinic) &middot; {{ $a->clinic->name }} @endif</div>
                        </td>
                        <td class="px-3 py-2">
                            <div>{{ $a->typeLabel() }}</div>
                            @if ($a->items->isNotEmpty())
                                <div class="text-xs text-slate-400">
                                    + {{ $a->items->map(fn ($i) => $i->name.($i->quantity > 1 ? '×'.$i->quantity : ''))->join(', ') }}
                                </div>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-end whitespace-nowrap">{{ number_format($aDue, 2) }}</td>
                        <td class="px-3 py-2 text-end whitespace-nowrap text-emerald-700">{{ number_format($aPaid, 2) }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            @if ($cancelled)
                                <span class="text-xs px-2 py-1 rounded-full bg-slate-100 text-slate-500">{{ $a->statusLabel() }}</span>
                            @elseif ($aLeft <= 0 && $aDue > 0)
                                <span class="text-xs px-2 py-1 rounded-full bg-emerald-100 text-emerald-700">✔ {{ __('app.collection.settled') }}</span>
                            @elseif ($aPaid > 0)
                                <span class="text-xs px-2 py-1 rounded-full bg-amber-100 text-amber-800">{{ __('app.collection.partial') }} — {{ number_format($aLeft, 2) }}</span>
                            @elseif ($aDue > 0)
                                <span class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-700">{{ __('app.collection.unpaid') }}</span>
                            @else
                                <span class="text-xs text-slate-300">{{ __('app.collection.no_price') }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-end whitespace-nowrap">
                            @if (! $cancelled && $aLeft > 0)
                                <button type="button" onclick="toggle('pc-{{ $a->id }}')"
                                        class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs px-3 py-1.5 rounded-lg">
                                    {{ __('app.collection.collect') }}
                                </button>
                            @endif
                        </td>
                    </tr>

                    {{-- Collect for this visit, defaulting to what's still owed. --}}
                    @if (! $cancelled && $aLeft > 0)
                        <tr id="pc-{{ $a->id }}" class="hidden">
                            <td colspan="6" class="px-3 py-3 bg-emerald-50/60">
                                <form method="POST" action="{{ route('practice.collections.store', $a) }}"
                                      class="flex flex-wrap items-end gap-3">
                                    @csrf
                                    <div>
                                        <label class="block text-xs text-slate-500 mb-1">{{ __('app.collection.amount') }} ({{ __('app.clinic.currency') }})</label>
                                        <input type="number" name="amount" step="0.01" min="0.01" required
                                               value="{{ number_format($aLeft, 2, '.', '') }}"
                                               class="w-32 border rounded px-2 py-1.5 text-sm">
                                    </div>
                                    <div class="grow max-w-xs">
                                        <label class="block text-xs text-slate-500 mb-1">{{ __('app.collection.note') }}</label>
                                        <input type="text" name="note" class="w-full border rounded px-2 py-1.5 text-sm">
                                    </div>
                                    <button class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm px-4 py-2 rounded-lg">
                                        {{ __('app.collection.submit') }}
                                    </button>
                                    <button type="button" onclick="toggle('pc-{{ $a->id }}')"
                                            class="text-sm text-slate-500 px-2">{{ __('app.collection.cancel') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endif

                    {{-- What's already been taken for this visit. --}}
                    @if ($a->collections->isNotEmpty())
                        <tr>
                            <td colspan="6" class="px-3 pb-3 pt-0">
                                <ul class="flex flex-wrap gap-x-4 gap-y-1">
                                    @foreach ($a->collections->sortBy('collected_at') as $col)
                                        <li class="text-xs text-slate-500">
                                            <span class="font-semibold text-slate-700">{{ number_format((float) $col->amount, 2) }}</span>
                                            &middot; {{ $col->collected_at?->translatedFormat('d M, H:i') }}
                                            @if ($col->collector) &middot; {{ __('app.collection.by', ['name' => $col->collector->name]) }} @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr><td colspan="6" class="px-3 py-6 text-center text-slate-400 italic">{{ __('app.patient.no_appointments') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Appointments --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h2 class="font-semibold text-slate-800 mb-3">{{ __('app.patient.appointments') }}</h2>
        <ul class="divide-y divide-slate-100 text-sm">
            @forelse ($appointments as $a)
                <li class="py-2 flex justify-between">
                    <span>{{ $a->scheduled_at->translatedFormat('d M Y, H:i') }} &middot; {{ $a->typeLabel() }}</span>
                    <span class="text-xs text-slate-500">{{ $a->statusLabel() }}</span>
                </li>
            @empty
                <li class="py-2 text-slate-400 italic">{{ __('app.patient.no_appointments') }}</li>
            @endforelse
        </ul>
    </div>

    {{-- Attachments --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h2 class="font-semibold text-slate-800 mb-3">{{ __('app.patient.attachments') }}</h2>
        <ul class="divide-y divide-slate-100 text-sm">
            @forelse ($p->attachments as $att)
                <li class="py-2 flex justify-between items-center">
                    <a href="{{ $att->url }}" target="_blank" class="text-indigo-600 hover:underline">📎 {{ $att->title ?? $att->file_name }}</a>
                    <form method="POST" action="{{ route('practice.attachments.destroy', $att) }}" onsubmit="return confirm('{{ __('app.common.confirm_delete') }}')">
                        @csrf @method('DELETE')
                        <button class="text-red-500 text-xs hover:underline">{{ __('app.common.delete') }}</button>
                    </form>
                </li>
            @empty
                <li class="py-2 text-slate-400 italic">{{ __('app.patient.no_attachments') }}</li>
            @endforelse
        </ul>
    </div>

    {{-- Prescriptions --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h2 class="font-semibold text-slate-800 mb-3">{{ __('app.patient.prescriptions') }}</h2>
        <ul class="divide-y divide-slate-100 text-sm">
            @forelse ($p->prescriptions as $rx)
                <li class="py-3">
                    <div class="flex justify-between items-center gap-2 flex-wrap">
                        <span class="font-mono text-xs bg-slate-100 px-2 py-0.5 rounded">{{ $rx->code }}</span>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('practice.prescriptions.print', ['prescription' => $rx, 'auto' => 1]) }}" target="_blank"
                               class="bg-purple-100 text-purple-700 px-3 py-1 rounded text-xs">🖨️ {{ __('app.print.print_pdf') }}</a>
                            <a href="{{ route('practice.prescriptions.pdf', ['prescription' => $rx, 'download' => 1]) }}"
                               class="bg-rose-100 text-rose-700 px-3 py-1 rounded text-xs">⬇️ {{ __('app.print.download_pdf') }}</a>
                            <button type="button" onclick="printRxThermal(this, {{ $rx->id }})"
                                    class="bg-teal-100 text-teal-700 px-3 py-1 rounded text-xs">🧾 {{ __('app.print.print_thermal') }}</button>
                        </div>
                    </div>
                    @if ($rx->items->isNotEmpty())
                        <ul class="mt-2 ps-1 space-y-0.5 text-xs text-slate-600">
                            @foreach ($rx->items as $it)
                                <li>
                                    <span class="font-medium text-slate-800">{{ $loop->iteration }}. {{ $it->medicine_name }}</span>
                                    @if ($it->substitute_name)
                                        <span class="text-teal-700">↔ {{ __('app.print.substitute') }}: {{ $it->substitute_name }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @empty
                <li class="py-2 text-slate-400 italic">{{ __('app.patient.no_prescriptions') }}</li>
            @endforelse
        </ul>
    </div>
</div>

@push('scripts')
<script>
    // Send a saved prescription to the clinic's Bluetooth thermal printer.
    function printRxThermal(btn, id) {
        const original = btn.innerHTML;
        btn.disabled = true;
        fetch(`{{ url('practice/prescriptions') }}/${id}/print-thermal`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
        .then(res => res.ok ? res.json() : Promise.reject(res))
        .then(() => {
            btn.innerHTML = '✅';
            setTimeout(() => { btn.innerHTML = original; btn.disabled = false; }, 2500);
        })
        .catch(() => {
            btn.disabled = false;
            alert(@json(__('app.print.thermal_failed')));
        });
    }
</script>
@endpush

{{-- Medical history: the patient's examinations across all visits & doctors. --}}
<div class="bg-white rounded-xl shadow-sm p-5 mt-6">
    <h2 class="font-semibold text-slate-800 mb-4">📋 {{ __('app.examine.history_section') }}</h2>
    <div class="space-y-3">
        @forelse ($p->diagnoses as $d)
            <div class="border border-slate-100 rounded-lg p-4">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-2 text-sm">
                    <span class="font-medium text-slate-700">👨‍⚕️ {{ $d->doctor?->name ?? '—' }}</span>
                    <span class="text-xs text-slate-400">
                        {{ optional($d->appointment?->scheduled_at ?? $d->created_at)->translatedFormat('d M Y') }}
                    </span>
                </div>
                <dl class="text-sm space-y-1.5">
                    <div>
                        <dt class="text-xs text-slate-400">{{ __('app.examine.history_diagnosis') }}</dt>
                        <dd class="text-slate-700 whitespace-pre-line">{{ $d->diagnosis }}</dd>
                    </div>
                    @if ($d->treatment_plan)
                        <div>
                            <dt class="text-xs text-slate-400">{{ __('app.examine.treatment_plan') }}</dt>
                            <dd class="text-slate-700 whitespace-pre-line">{{ $d->treatment_plan }}</dd>
                        </div>
                    @endif
                    @if ($d->notes)
                        <div>
                            <dt class="text-xs text-slate-400">{{ __('app.common.notes') }}</dt>
                            <dd class="text-slate-600 whitespace-pre-line">{{ $d->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        @empty
            <p class="text-sm text-slate-400 italic">{{ __('app.examine.no_history') }}</p>
        @endforelse
    </div>
</div>

{{-- Lab & radiology results (same data as client/test-results) --}}
<div class="mt-6">
    @include('clinic.partials.lab-results')
</div>
@endsection
