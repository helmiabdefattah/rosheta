@extends('clinic.layouts.app')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-slate-900">🧾 {{ __('app.manager.patients_report') }}</h1>
    <a href="{{ route('practice.doctor.manager.index') }}"
       class="text-sm font-medium px-4 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700">
        ← {{ __('app.manager.title') }}
    </a>
</div>

<form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-4 flex flex-wrap items-end gap-3">
    <div class="grow min-w-[12rem]">
        <label class="block text-xs text-slate-500 mb-1">{{ __('app.report.search') }}</label>
        <input type="search" name="search" value="{{ $search }}"
               placeholder="{{ __('app.report.search_patient_placeholder') }}"
               class="w-full border rounded px-2 py-1.5 text-sm">
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">{{ __('app.report.from') }}</label>
        <input type="date" name="from" value="{{ $from }}" class="border rounded px-2 py-1.5 text-sm">
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">{{ __('app.report.to') }}</label>
        <input type="date" name="to" value="{{ $to }}" class="border rounded px-2 py-1.5 text-sm">
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">{{ __('app.report.status') }}</label>
        <select name="status" class="border rounded px-2 py-1.5 text-sm">
            <option value="">{{ __('app.report.all') }}</option>
            @foreach ($statuses as $s)
                <option value="{{ $s }}" @selected($status === $s)>{{ __('app.statuses.'.$s) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">{{ __('app.insurance.company') }}</label>
        <select name="insurance_company_id" class="border rounded px-2 py-1.5 text-sm">
            <option value="">{{ __('app.report.all') }}</option>
            @foreach ($companies as $co)
                <option value="{{ $co->id }}" @selected((string) $companyId === (string) $co->id)>{{ $co->displayName() }}</option>
            @endforeach
        </select>
    </div>
    <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg">{{ __('app.report.apply') }}</button>
</form>

{{-- Totals --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
    <div class="bg-white border border-slate-200 rounded-xl p-4">
        <div class="text-xs text-slate-500">{{ __('app.report.total_due') }}</div>
        <div class="text-xl font-bold text-slate-800">{{ number_format($totals['due'], 2) }}</div>
    </div>
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
        <div class="text-xs text-emerald-700">{{ __('app.report.total_collected') }}</div>
        <div class="text-xl font-bold text-emerald-900">{{ number_format($totals['collected'], 2) }}</div>
    </div>
    <div class="bg-cyan-50 border border-cyan-200 rounded-xl p-4">
        <div class="text-xs text-cyan-700">{{ __('app.report.total_insurance') }}</div>
        <div class="text-xl font-bold text-cyan-900">{{ number_format($totals['insurance'], 2) }}</div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500">
            <tr>
                <th class="px-4 py-3 text-start">{{ __('app.report.date') }}</th>
                <th class="px-4 py-3 text-start">{{ __('app.report.patient') }}</th>
                <th class="px-4 py-3 text-start">{{ __('app.report.type') }}</th>
                <th class="px-4 py-3 text-start">{{ __('app.report.due') }}</th>
                <th class="px-4 py-3 text-start">{{ __('app.report.collected') }}</th>
                <th class="px-4 py-3 text-start">{{ __('app.insurance.company') }}</th>
                <th class="px-4 py-3 text-start">{{ __('app.report.insurance_amount') }}</th>
                <th class="px-4 py-3 text-start">{{ __('app.report.status') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($appointments as $appt)
                <tr>
                    <td class="px-4 py-3 whitespace-nowrap">{{ $appt->scheduled_at?->format('Y-m-d H:i') }}</td>
                    <td class="px-4 py-3">
                        @if ($appt->client)
                            <a href="{{ route('practice.patients.show', $appt->client) }}"
                               class="font-medium text-indigo-600 hover:text-indigo-800 hover:underline">
                                {{ $appt->client->name }}
                            </a>
                        @else
                            <span class="font-medium text-slate-800">—</span>
                        @endif
                        @if ($appt->client?->phone_number)
                            <div class="text-xs text-slate-400" dir="ltr">📞 {{ $appt->client->phone_number }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3">{{ $appt->typeLabel() }}</td>
                    <td class="px-4 py-3">{{ number_format($appt->dueAmount(), 2) }}</td>
                    <td class="px-4 py-3">{{ number_format($appt->collectedAmount(), 2) }}</td>
                    <td class="px-4 py-3 text-cyan-800">{{ $appt->insurance?->insuranceCompany?->displayName() ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $appt->insurance ? number_format((float) $appt->insurance->insurance_amount, 2) : '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-1 rounded-full bg-slate-100 text-slate-700">{{ $appt->statusLabel() }}</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-4 py-10 text-center text-slate-400">{{ __('app.report.empty') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
