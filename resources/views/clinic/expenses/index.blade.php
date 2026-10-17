@extends('clinic.layouts.app')

@php($cur = __('app.expenses.currency'))

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">💸 {{ __('app.expenses.title') }}</h1>
        <p class="text-slate-500 text-sm">@if ($clinic) {{ $clinic->name }} @endif</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <a href="{{ route('practice.doctor.vendors.index') }}" class="btn btn-secondary btn-sm">🏢 {{ __('app.vendors.title') }}</a>
        <a href="{{ route('practice.doctor.setup.expense-categories') }}" class="btn btn-secondary btn-sm">🏷 {{ __('app.expenses.categories') }}</a>
        <a href="{{ route('practice.doctor.expenses.export', array_merge(request()->query(), ['format' => 'excel'])) }}" class="btn btn-secondary btn-sm">⬇ CSV</a>
        <a href="{{ route('practice.doctor.expenses.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="btn btn-secondary btn-sm">⬇ PDF</a>
        <a href="{{ route('practice.doctor.expenses.create') }}" class="btn btn-primary btn-sm">➕ {{ __('app.expenses.new') }}</a>
    </div>
</div>

{{-- KPI strip --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <div class="card p-4">
        <div class="text-xs text-slate-500">{{ __('app.expenses.total') }}</div>
        <div class="text-xl font-bold text-slate-900">{{ number_format($totals['gross'], 2) }} <span class="text-sm font-medium text-slate-400">{{ $cur }}</span></div>
    </div>
    <div class="card p-4">
        <div class="text-xs text-slate-500">{{ __('app.expenses.paid') }}</div>
        <div class="text-xl font-bold text-emerald-700">{{ number_format($totals['paid'], 2) }} <span class="text-sm font-medium text-slate-400">{{ $cur }}</span></div>
    </div>
    <div class="card p-4">
        <div class="text-xs text-slate-500">{{ __('app.expenses.outstanding') }}</div>
        <div class="text-xl font-bold text-red-700">{{ number_format($totals['outstanding'], 2) }} <span class="text-sm font-medium text-slate-400">{{ $cur }}</span></div>
    </div>
    <div class="card p-4">
        <div class="text-xs text-slate-500">{{ __('app.expenses.count') }}</div>
        <div class="text-xl font-bold text-slate-900">{{ number_format($totals['count']) }}</div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="card p-4 mb-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-3 items-end">
    <div>
        <label class="block text-xs text-slate-500 mb-1">{{ __('app.expenses.f.from') }}</label>
        <input type="date" name="from" value="{{ $from }}" class="w-full rounded-lg border px-3 py-2">
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">{{ __('app.expenses.f.to') }}</label>
        <input type="date" name="to" value="{{ $to }}" class="w-full rounded-lg border px-3 py-2">
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">{{ __('app.expenses.f.category') }}</label>
        <select name="category" class="w-full rounded-lg border px-3 py-2">
            <option value="">{{ __('app.expenses.all') }}</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" @selected((string) $categoryId === (string) $cat->id)>{{ $cat->icon }} {{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">{{ __('app.expenses.f.vendor') }}</label>
        <select name="vendor" class="w-full rounded-lg border px-3 py-2">
            <option value="">{{ __('app.expenses.all') }}</option>
            @foreach ($vendors as $v)
                <option value="{{ $v->id }}" @selected((string) $vendorId === (string) $v->id)>{{ $v->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">{{ __('app.expenses.f.status') }}</label>
        <select name="status" class="w-full rounded-lg border px-3 py-2">
            <option value="">{{ __('app.expenses.all') }}</option>
            @foreach ($statuses as $s)
                <option value="{{ $s->value }}" @selected($status === $s->value)>{{ $s->label() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">{{ __('app.expenses.f.search') }}</label>
        <input type="text" name="search" value="{{ $search }}" class="w-full rounded-lg border px-3 py-2">
    </div>
    <div class="flex gap-2">
        <button class="btn btn-primary btn-block">{{ __('app.expenses.f.apply') }}</button>
        <a href="{{ route('practice.doctor.expenses.index') }}" class="btn btn-secondary">{{ __('app.expenses.f.reset') }}</a>
    </div>
</form>

{{-- Table --}}
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="py-3 px-4 text-start">{{ __('app.expenses.f.date') }}</th>
                    <th class="py-3 px-4 text-start">{{ __('app.expenses.f.title') }}</th>
                    <th class="py-3 px-4 text-start">{{ __('app.expenses.f.category') }}</th>
                    <th class="py-3 px-4 text-end">{{ __('app.expenses.total') }}</th>
                    <th class="py-3 px-4 text-end">{{ __('app.expenses.outstanding') }}</th>
                    <th class="py-3 px-4 text-start">{{ __('app.expenses.f.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($expenses as $expense)
                    <tr class="border-t hover:bg-slate-50 cursor-pointer" onclick="window.location='{{ route('practice.doctor.expenses.show', $expense) }}'">
                        <td class="py-3 px-4 whitespace-nowrap">{{ $expense->expense_date?->format('Y-m-d') }}</td>
                        <td class="py-3 px-4 font-medium text-slate-800">{{ $expense->title }}</td>
                        <td class="py-3 px-4 text-slate-500">{{ $expense->category?->icon }} {{ $expense->category?->name ?? __('app.expenses.f.uncategorised') }}</td>
                        <td class="py-3 px-4 text-end">{{ number_format($expense->gross(), 2) }}</td>
                        <td class="py-3 px-4 text-end {{ $expense->balance() > 0 ? 'text-red-700' : 'text-slate-400' }}">{{ number_format($expense->balance(), 2) }}</td>
                        <td class="py-3 px-4"><span class="inline-block text-xs px-2 py-1 rounded-full {{ $expense->status->color() }}">{{ $expense->status->label() }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-10 text-center text-slate-400">{{ __('app.expenses.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $expenses->links() }}</div>
@endsection
