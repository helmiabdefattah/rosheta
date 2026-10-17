@extends('clinic.layouts.app')

@php($cur = __('app.expenses.currency'))

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">🎯 {{ __('app.budgets.title') }}</h1>
        <p class="text-slate-500 text-sm">{{ $monthLabel }}</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('practice.doctor.expenses.index') }}" class="btn btn-secondary btn-sm">← {{ __('app.expenses.title') }}</a>
        <form method="GET" class="flex items-center gap-2">
            <input type="month" name="month" value="{{ \Illuminate\Support\Str::of($month)->substr(0, 7) }}" class="rounded-lg border px-3 py-2 text-sm">
            <button class="btn btn-secondary btn-sm">{{ __('app.expenses.f.apply') }}</button>
        </form>
    </div>
</div>

@php($barColor = fn ($pct) => $pct === null ? 'bg-slate-300' : ($pct >= 100 ? 'bg-red-500' : ($pct >= 80 ? 'bg-amber-500' : 'bg-emerald-500')))

<form method="POST" action="{{ route('practice.doctor.budgets.store') }}" class="card overflow-hidden">
    @csrf
    <input type="hidden" name="month" value="{{ $month }}">

    {{-- Overall --}}
    <div class="p-5 border-b bg-slate-50/60">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="font-semibold text-slate-800">{{ __('app.budgets.overall') }}</div>
            <div class="flex items-center gap-4">
                <div class="text-sm text-slate-500">{{ __('app.budgets.actual') }}: <span class="font-semibold text-slate-800">{{ number_format($overall['actual'], 2) }} {{ $cur }}</span></div>
                <div class="flex items-center gap-2">
                    <label class="text-sm text-slate-500">{{ __('app.budgets.budget') }}</label>
                    <input type="number" step="0.01" min="0" name="overall" value="{{ $overall['budget'] ?: '' }}" class="w-32 rounded-lg border px-3 py-2">
                </div>
            </div>
        </div>
        <div class="mt-3 h-2 w-full rounded-full bg-slate-200 overflow-hidden">
            <div class="h-full {{ $barColor($overall['pct']) }}" style="width: {{ $overall['pct'] ?? 0 }}%"></div>
        </div>
    </div>

    {{-- Per category --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="py-3 px-4 text-start">{{ __('app.expenses.f.category') }}</th>
                    <th class="py-3 px-4 text-end">{{ __('app.budgets.actual') }}</th>
                    <th class="py-3 px-4 text-end">{{ __('app.budgets.budget') }}</th>
                    <th class="py-3 px-4 text-end">{{ __('app.budgets.variance') }}</th>
                    <th class="py-3 px-4 w-40">{{ __('app.budgets.usage') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr class="border-t">
                        <td class="py-3 px-4 font-medium text-slate-800">{{ $row['category']->icon }} {{ $row['category']->name }}</td>
                        <td class="py-3 px-4 text-end">{{ number_format($row['actual'], 2) }}</td>
                        <td class="py-3 px-4 text-end">
                            <input type="number" step="0.01" min="0" name="budgets[{{ $row['category']->id }}]" value="{{ $row['budget'] ?: '' }}" class="w-28 rounded-lg border px-2 py-1 text-end">
                        </td>
                        <td class="py-3 px-4 text-end {{ $row['variance'] < 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ number_format($row['variance'], 2) }}</td>
                        <td class="py-3 px-4">
                            <div class="h-2 w-full rounded-full bg-slate-200 overflow-hidden">
                                <div class="h-full {{ $barColor($row['pct']) }}" style="width: {{ $row['pct'] ?? 0 }}%"></div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t">
        <button class="btn btn-primary">{{ __('app.budgets.save') }}</button>
    </div>
</form>
@endsection
