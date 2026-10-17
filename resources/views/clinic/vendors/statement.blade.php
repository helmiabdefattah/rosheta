@extends('clinic.layouts.app')

@php($cur = __('app.expenses.currency'))

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">{{ $vendor->name }}</h1>
        <p class="text-slate-500 text-sm">{{ $vendor->type?->label() }} @if ($vendor->phone) · {{ $vendor->phone }} @endif</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <a href="{{ route('practice.doctor.vendors.index') }}" class="btn btn-secondary btn-sm">← {{ __('app.vendors.title') }}</a>
        <a href="{{ route('practice.doctor.vendors.statement.export', ['vendor' => $vendor, 'format' => 'excel']) }}" class="btn btn-secondary btn-sm">⬇ CSV</a>
        <a href="{{ route('practice.doctor.vendors.statement.export', ['vendor' => $vendor, 'format' => 'pdf']) }}" class="btn btn-secondary btn-sm">⬇ PDF</a>
    </div>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <div class="card p-4">
        <div class="text-xs text-slate-500">{{ __('app.vendors.opening') }}</div>
        <div class="text-lg font-bold">{{ number_format((float) $vendor->opening_balance, 2) }} <span class="text-xs text-slate-400">{{ $cur }}</span></div>
    </div>
    <div class="card p-4">
        <div class="text-xs text-slate-500">{{ __('app.expenses.total') }}</div>
        <div class="text-lg font-bold">{{ number_format($vendor->totalBilled(), 2) }} <span class="text-xs text-slate-400">{{ $cur }}</span></div>
    </div>
    <div class="card p-4">
        <div class="text-xs text-slate-500">{{ __('app.expenses.paid') }}</div>
        <div class="text-lg font-bold text-emerald-700">{{ number_format($vendor->totalPaid(), 2) }} <span class="text-xs text-slate-400">{{ $cur }}</span></div>
    </div>
    <div class="card p-4">
        <div class="text-xs text-slate-500">{{ __('app.vendors.balance') }}</div>
        <div class="text-lg font-bold text-red-700">{{ number_format($vendor->balance(), 2) }} <span class="text-xs text-slate-400">{{ $cur }}</span></div>
    </div>
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="py-3 px-4 text-start">{{ __('app.expenses.f.date') }}</th>
                    <th class="py-3 px-4 text-start">{{ __('app.expenses.f.title') }}</th>
                    <th class="py-3 px-4 text-end">{{ __('app.expenses.total') }}</th>
                    <th class="py-3 px-4 text-end">{{ __('app.expenses.paid') }}</th>
                    <th class="py-3 px-4 text-end">{{ __('app.expenses.outstanding') }}</th>
                    <th class="py-3 px-4 text-start">{{ __('app.expenses.f.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($expenses as $expense)
                    <tr class="border-t hover:bg-slate-50 cursor-pointer" onclick="window.location='{{ route('practice.doctor.expenses.show', $expense) }}'">
                        <td class="py-3 px-4 whitespace-nowrap">{{ $expense->expense_date?->format('Y-m-d') }}</td>
                        <td class="py-3 px-4 font-medium text-slate-800">{{ $expense->title }}</td>
                        <td class="py-3 px-4 text-end">{{ number_format($expense->gross(), 2) }}</td>
                        <td class="py-3 px-4 text-end text-emerald-700">{{ number_format($expense->paidTotal(), 2) }}</td>
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
@endsection
