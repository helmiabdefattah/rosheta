@extends('clinic.layouts.app')

@php($cur = __('app.expenses.currency'))

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">📈 {{ __('app.finance.title') }}</h1>
        <p class="text-slate-500 text-sm">@if ($clinic) {{ $clinic->name }} @endif</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <form method="GET" class="flex items-center gap-2">
            <input type="date" name="from" value="{{ $from }}" class="rounded-lg border px-3 py-2 text-sm">
            <input type="date" name="to" value="{{ $to }}" class="rounded-lg border px-3 py-2 text-sm">
            <button class="btn btn-secondary btn-sm">{{ __('app.expenses.f.apply') }}</button>
        </form>
        <a href="{{ route('practice.doctor.finance.export', array_merge(request()->query(), ['format' => 'excel'])) }}" class="btn btn-secondary btn-sm">⬇ CSV</a>
        <a href="{{ route('practice.doctor.finance.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="btn btn-secondary btn-sm">⬇ PDF</a>
    </div>
</div>

{{-- KPI tiles --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
    <div class="card p-4">
        <div class="text-xs text-slate-500">{{ __('app.finance.income_total') }}</div>
        <div class="text-xl font-bold text-emerald-700">{{ number_format($income['total'], 2) }} <span class="text-sm text-slate-400">{{ $cur }}</span></div>
    </div>
    <div class="card p-4">
        <div class="text-xs text-slate-500">{{ __('app.finance.expenses_total') }}</div>
        <div class="text-xl font-bold text-red-700">{{ number_format($expenses['gross'], 2) }} <span class="text-sm text-slate-400">{{ $cur }}</span></div>
    </div>
    <div class="card p-4">
        <div class="text-xs text-slate-500">{{ __('app.finance.net') }}</div>
        <div class="text-xl font-bold {{ $net >= 0 ? 'text-emerald-700' : 'text-red-700' }}">{{ number_format($net, 2) }} <span class="text-sm text-slate-400">{{ $cur }}</span></div>
    </div>
    <div class="card p-4">
        <div class="text-xs text-slate-500">{{ __('app.finance.cash_paid') }}</div>
        <div class="text-xl font-bold text-slate-900">{{ number_format($expenses['paid'], 2) }} <span class="text-sm text-slate-400">{{ $cur }}</span></div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    {{-- Income breakdown --}}
    <div class="card p-5">
        <h2 class="font-semibold text-slate-800 mb-4">{{ __('app.finance.income') }}</h2>
        @php($incMax = max($income['patient'], $income['dental'], 0.01))
        @foreach ([['label' => __('app.finance.income_patient'), 'val' => $income['patient']], ['label' => __('app.finance.income_dental'), 'val' => $income['dental']]] as $line)
            <div class="mb-3">
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-slate-600">{{ $line['label'] }}</span>
                    <span class="font-medium">{{ number_format($line['val'], 2) }} {{ $cur }}</span>
                </div>
                <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full bg-emerald-500" style="width: {{ $line['val'] > 0 ? round($line['val'] / $incMax * 100) : 0 }}%"></div>
                </div>
            </div>
        @endforeach
        <div class="border-t pt-3 mt-3 flex justify-between font-semibold text-slate-800">
            <span>{{ __('app.finance.income_total') }}</span>
            <span>{{ number_format($income['total'], 2) }} {{ $cur }}</span>
        </div>
    </div>

    {{-- Expenses by category --}}
    <div class="card p-5">
        <h2 class="font-semibold text-slate-800 mb-4">{{ __('app.finance.by_category') }}</h2>
        @forelse ($byCategory as $cat)
            <div class="mb-3">
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-slate-600">{{ $cat['name'] }}</span>
                    <span class="font-medium">{{ number_format($cat['total'], 2) }} {{ $cur }}</span>
                </div>
                <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full bg-indigo-500" style="width: {{ $maxCategory > 0 ? round($cat['total'] / $maxCategory * 100) : 0 }}%"></div>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-400">{{ __('app.expenses.empty') }}</p>
        @endforelse
        <div class="border-t pt-3 mt-3 flex justify-between font-semibold text-slate-800">
            <span>{{ __('app.finance.expenses_total') }}</span>
            <span>{{ number_format($expenses['gross'], 2) }} {{ $cur }}</span>
        </div>
    </div>
</div>
@endsection
