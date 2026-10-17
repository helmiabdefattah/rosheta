@extends('clinic.layouts.app')

@php($cur = __('app.expenses.currency'))

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">🏢 {{ __('app.vendors.title') }}</h1>
        <p class="text-slate-500 text-sm">@if ($clinic) {{ $clinic->name }} @endif</p>
    </div>
    <a href="{{ route('practice.doctor.expenses.index') }}" class="btn btn-secondary btn-sm">← {{ __('app.expenses.title') }}</a>
</div>

<div class="grid grid-cols-3 gap-3 mb-5">
    <div class="card p-4">
        <div class="text-xs text-slate-500">{{ __('app.expenses.total') }}</div>
        <div class="text-lg font-bold text-slate-900">{{ number_format($totals['billed'], 2) }} <span class="text-xs text-slate-400">{{ $cur }}</span></div>
    </div>
    <div class="card p-4">
        <div class="text-xs text-slate-500">{{ __('app.expenses.paid') }}</div>
        <div class="text-lg font-bold text-emerald-700">{{ number_format($totals['paid'], 2) }} <span class="text-xs text-slate-400">{{ $cur }}</span></div>
    </div>
    <div class="card p-4">
        <div class="text-xs text-slate-500">{{ __('app.vendors.balance') }}</div>
        <div class="text-lg font-bold text-red-700">{{ number_format($totals['balance'], 2) }} <span class="text-xs text-slate-400">{{ $cur }}</span></div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    {{-- Add vendor --}}
    <form method="POST" action="{{ route('practice.doctor.vendors.store') }}" class="card p-5 space-y-3">
        @csrf
        <h2 class="font-semibold text-slate-800">{{ __('app.vendors.new') }}</h2>
        @include('clinic.vendors.partials.fields', ['vendor' => null, 'types' => $types])
        <button class="btn btn-primary btn-block">{{ __('app.expenses.f.save') }}</button>
    </form>

    {{-- List --}}
    <div class="card overflow-hidden lg:col-span-2">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="py-3 px-4 text-start">{{ __('app.vendors.f.name') }}</th>
                        <th class="py-3 px-4 text-start">{{ __('app.vendors.f.type') }}</th>
                        <th class="py-3 px-4 text-end">{{ __('app.vendors.balance') }}</th>
                        <th class="py-3 px-4"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vendors as $vendor)
                        <tr class="border-t">
                            <td class="py-3 px-4 font-medium text-slate-800">
                                {{ $vendor->name }}
                                @unless ($vendor->is_active)<span class="ms-1 text-xs text-slate-400">({{ __('app.vendors.inactive') }})</span>@endunless
                                @if ($vendor->phone)<div class="text-xs text-slate-400">{{ $vendor->phone }}</div>@endif
                            </td>
                            <td class="py-3 px-4 text-slate-500">{{ $vendor->type?->label() }}</td>
                            <td class="py-3 px-4 text-end {{ $vendor->balance() > 0 ? 'text-red-700 font-semibold' : 'text-slate-400' }}">{{ number_format($vendor->balance(), 2) }}</td>
                            <td class="py-3 px-4 text-end whitespace-nowrap">
                                <a href="{{ route('practice.doctor.vendors.statement', $vendor) }}" class="text-indigo-600 hover:underline text-xs">{{ __('app.vendors.statement') }}</a>
                                <a href="{{ route('practice.doctor.vendors.edit', $vendor) }}" class="text-slate-500 hover:underline text-xs ms-2">{{ __('app.vendors.edit') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-10 text-center text-slate-400">{{ __('app.vendors.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
