@extends('clinic.layouts.app')

@php($cur = __('app.expenses.currency'))

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">🔁 {{ __('app.recurring.title') }}</h1>
        <p class="text-slate-500 text-sm">{{ __('app.recurring.subtitle') }}</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('practice.doctor.expenses.index') }}" class="btn btn-secondary btn-sm">← {{ __('app.expenses.title') }}</a>
        <form method="POST" action="{{ route('practice.doctor.recurring.generate') }}">
            @csrf
            <button class="btn btn-primary btn-sm">⚡ {{ __('app.recurring.generate_now') }}</button>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    {{-- Add template --}}
    <form method="POST" action="{{ route('practice.doctor.recurring.store') }}" class="card p-5 space-y-3">
        @csrf
        <h2 class="font-semibold text-slate-800">{{ __('app.recurring.new') }}</h2>
        @include('clinic.recurring.partials.fields', ['template' => null])
        <button class="btn btn-primary btn-block">{{ __('app.expenses.f.save') }}</button>
    </form>

    {{-- List --}}
    <div class="card overflow-hidden lg:col-span-2">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="py-3 px-4 text-start">{{ __('app.expenses.f.title') }}</th>
                        <th class="py-3 px-4 text-start">{{ __('app.recurring.interval') }}</th>
                        <th class="py-3 px-4 text-end">{{ __('app.expenses.f.amount') }}</th>
                        <th class="py-3 px-4 text-start">{{ __('app.recurring.next_run') }}</th>
                        <th class="py-3 px-4"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($templates as $t)
                        <tr class="border-t">
                            <td class="py-3 px-4 font-medium text-slate-800">
                                {{ $t->title }}
                                @unless ($t->is_active)<span class="ms-1 text-xs text-slate-400">({{ __('app.recurring.paused') }})</span>@endunless
                                <div class="text-xs text-slate-400">{{ $t->category?->name ?? __('app.expenses.f.uncategorised') }}@if ($t->vendor) · {{ $t->vendor->name }} @endif</div>
                            </td>
                            <td class="py-3 px-4 text-slate-500">{{ $t->interval?->label() }}</td>
                            <td class="py-3 px-4 text-end">{{ number_format((float) $t->amount + (float) $t->tax_amount, 2) }} {{ $cur }}</td>
                            <td class="py-3 px-4 whitespace-nowrap">{{ $t->next_run_on?->format('Y-m-d') }}</td>
                            <td class="py-3 px-4 text-end whitespace-nowrap">
                                <a href="{{ route('practice.doctor.recurring.edit', $t) }}" class="text-indigo-600 hover:underline text-xs">{{ __('app.vendors.edit') }}</a>
                                <form method="POST" action="{{ route('practice.doctor.recurring.destroy', $t) }}" class="inline" onsubmit="return confirm('{{ __('app.dental.actions.confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline text-xs ms-2">{{ __('app.expenses.f.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-10 text-center text-slate-400">{{ __('app.recurring.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
