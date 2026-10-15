@extends('clinic.layouts.app')

@section('content')
    @include('clinic.dental-lab.partials._tabs', ['active' => 'labs'])

    <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
        <div class="flex items-center gap-3">
            <a href="{{ route('practice.dental-lab.labs.index') }}" class="btn btn-secondary btn-sm">← {{ __('app.dental.actions.back') }}</a>
            <h2 class="font-bold text-slate-800">{{ __('app.dental.lab.statement') }} · {{ $lab->name }}</h2>
        </div>
        <a href="{{ route('practice.dental-lab.orders.create') }}" class="btn btn-primary btn-sm">+ {{ __('app.dental.order.add') }}</a>
    </div>

    {{-- Account summary between the clinic and this lab --}}
    <div class="grid sm:grid-cols-3 gap-4 mb-6">
        <div class="card p-4">
            <div class="text-xs text-slate-400">{{ __('app.dental.lab.ordered_total') }}</div>
            <div class="text-2xl font-black text-slate-900">{{ number_format($totals['ordered'], 2) }}</div>
        </div>
        <div class="card p-4">
            <div class="text-xs text-slate-400">{{ __('app.dental.lab.paid_total') }}</div>
            <div class="text-2xl font-black text-emerald-700">{{ number_format($totals['paid'], 2) }}</div>
        </div>
        <div class="card p-4">
            <div class="text-xs text-slate-400">{{ __('app.dental.lab.balance_due') }}</div>
            <div class="text-2xl font-black {{ $totals['balance'] > 0 ? 'text-red-600' : 'text-slate-900' }}">{{ number_format($totals['balance'], 2) }}</div>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 text-start font-semibold">#</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('app.dental.order.created_at') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('app.dental.order.patient') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('app.dental.order.status') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('app.dental.order.total') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('app.dental.order.paid') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('app.dental.order.balance') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50/70 cursor-pointer" onclick="window.location='{{ route('practice.dental-lab.orders.show', $order) }}'">
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $order->id }}</td>
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $order->created_at?->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $order->patient?->name ?? __('app.dental.order.no_patient') }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $order->status->color() }}">{{ $order->status->label() }}</span></td>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ number_format($order->total(), 2) }}</td>
                            <td class="px-4 py-3 text-emerald-700">{{ number_format($order->paidTotal(), 2) }}</td>
                            <td class="px-4 py-3 font-medium {{ $order->balance() > 0 ? 'text-red-600' : 'text-slate-500' }}">{{ number_format($order->balance(), 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-slate-400">{{ __('app.dental.lab.statement_empty') }}</td></tr>
                    @endforelse
                </tbody>
                @if($orders->isNotEmpty())
                    <tfoot>
                        <tr class="bg-slate-50 font-bold text-slate-900">
                            <td class="px-4 py-3" colspan="4">{{ __('app.dental.lab.statement') }}</td>
                            <td class="px-4 py-3">{{ number_format($totals['ordered'], 2) }}</td>
                            <td class="px-4 py-3 text-emerald-700">{{ number_format($totals['paid'], 2) }}</td>
                            <td class="px-4 py-3 {{ $totals['balance'] > 0 ? 'text-red-600' : '' }}">{{ number_format($totals['balance'], 2) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endsection
