@extends('clinic.layouts.app')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-slate-900">💵 {{ __('app.manager.collections') }}</h1>
    <a href="{{ route('practice.doctor.manager.index') }}"
       class="text-sm font-medium px-4 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700">
        ← {{ __('app.manager.title') }}
    </a>
</div>

{{-- Period filter --}}
<form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-4 flex flex-wrap items-end gap-3">
    <div>
        <label class="block text-xs text-slate-500 mb-1">{{ __('app.report.from') }}</label>
        <input type="date" name="from" value="{{ $from }}" class="border rounded px-2 py-1.5 text-sm">
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">{{ __('app.report.to') }}</label>
        <input type="date" name="to" value="{{ $to }}" class="border rounded px-2 py-1.5 text-sm">
    </div>
    <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg">{{ __('app.report.apply') }}</button>
</form>

{{-- Total --}}
<div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-4 flex items-center justify-between">
    <span class="text-emerald-800 font-medium">{{ __('app.report.total_collected') }}</span>
    <span class="text-2xl font-bold text-emerald-900">{{ number_format($total, 2) }} {{ __('app.clinic.currency') }}</span>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-start">
            <tr>
                <th class="px-4 py-3 text-start">{{ __('app.report.date') }}</th>
                <th class="px-4 py-3 text-start">{{ __('app.report.patient') }}</th>
                <th class="px-4 py-3 text-start">{{ __('app.report.amount') }}</th>
                <th class="px-4 py-3 text-start">{{ __('app.collection.by_label') }}</th>
                <th class="px-4 py-3 text-start">{{ __('app.collection.note') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($collections as $col)
                <tr>
                    <td class="px-4 py-3 whitespace-nowrap">{{ $col->collected_at?->format('Y-m-d H:i') }}</td>
                    <td class="px-4 py-3">{{ $col->appointment?->client?->name ?? '—' }}</td>
                    <td class="px-4 py-3 font-semibold text-slate-800">{{ number_format((float) $col->amount, 2) }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $col->collector?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $col->note ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">{{ __('app.report.empty') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
