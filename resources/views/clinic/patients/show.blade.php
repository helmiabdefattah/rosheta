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
                <li class="py-2 flex justify-between items-center">
                    <span class="font-mono text-xs bg-slate-100 px-2 py-0.5 rounded">{{ $rx->code }}</span>
                    <a href="{{ route('practice.prescriptions.print', $rx) }}" target="_blank" class="text-purple-600 text-xs hover:underline">{{ __('app.common.print') }}</a>
                </li>
            @empty
                <li class="py-2 text-slate-400 italic">{{ __('app.patient.no_prescriptions') }}</li>
            @endforelse
        </ul>
    </div>

    {{-- Diagnoses --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h2 class="font-semibold text-slate-800 mb-3">{{ __('app.patient.diagnoses') }}</h2>
        <ul class="divide-y divide-slate-100 text-sm">
            @forelse ($p->diagnoses as $d)
                <li class="py-2">
                    <div class="font-medium">{{ $d->diagnosis }}</div>
                    @if ($d->treatment_plan)<div class="text-slate-500 text-xs mt-0.5">{{ __('app.patient.plan', ['value' => $d->treatment_plan]) }}</div>@endif
                </li>
            @empty
                <li class="py-2 text-slate-400 italic">{{ __('app.patient.no_diagnoses') }}</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
