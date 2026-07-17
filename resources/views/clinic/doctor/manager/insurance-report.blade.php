@extends('clinic.layouts.app')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-slate-900">🛡 {{ __('app.manager.insurance_report') }}</h1>
    <a href="{{ route('practice.doctor.manager.index') }}"
       class="text-sm font-medium px-4 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700">
        ← {{ __('app.manager.title') }}
    </a>
</div>

<form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-4 flex flex-wrap items-end gap-3">
    <div>
        <label class="block text-xs text-slate-500 mb-1">{{ __('app.report.from') }}</label>
        <input type="date" name="from" value="{{ $from }}" class="border rounded px-2 py-1.5 text-sm">
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">{{ __('app.report.to') }}</label>
        <input type="date" name="to" value="{{ $to }}" class="border rounded px-2 py-1.5 text-sm">
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
    <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4">
        <div class="text-xs text-indigo-700">{{ __('app.report.total_claimed') }}</div>
        <div class="text-xl font-bold text-indigo-900">{{ number_format($totals['claimed'], 2) }}</div>
    </div>
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
        <div class="text-xs text-emerald-700">{{ __('app.report.total_collected') }}</div>
        <div class="text-xl font-bold text-emerald-900">{{ number_format($totals['collected'], 2) }}</div>
    </div>
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
        <div class="text-xs text-amber-700">{{ __('app.report.total_pending') }}</div>
        <div class="text-xl font-bold text-amber-900">{{ number_format($totals['pending'], 2) }}</div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500">
            <tr>
                <th class="px-4 py-3 text-start">{{ __('app.insurance.company') }}</th>
                <th class="px-4 py-3 text-start">{{ __('app.report.claimed') }}</th>
                <th class="px-4 py-3 text-start">{{ __('app.report.collected') }}</th>
                <th class="px-4 py-3 text-start">{{ __('app.report.pending') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($rows as $row)
                <tr>
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $row['company']?->displayName() ?? '—' }}</td>
                    <td class="px-4 py-3">{{ number_format($row['claimed'], 2) }}</td>
                    <td class="px-4 py-3 text-emerald-700">{{ number_format($row['collected'], 2) }}</td>
                    <td class="px-4 py-3 font-semibold {{ $row['pending'] > 0 ? 'text-amber-700' : 'text-slate-500' }}">{{ number_format($row['pending'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">{{ __('app.report.empty') }}</td></tr>
            @endforelse
        </tbody>
        @if ($rows->isNotEmpty())
            <tfoot class="bg-slate-50 font-semibold text-slate-800">
                <tr>
                    <td class="px-4 py-3">{{ __('app.report.total') }}</td>
                    <td class="px-4 py-3">{{ number_format($totals['claimed'], 2) }}</td>
                    <td class="px-4 py-3">{{ number_format($totals['collected'], 2) }}</td>
                    <td class="px-4 py-3">{{ number_format($totals['pending'], 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>
@endsection
