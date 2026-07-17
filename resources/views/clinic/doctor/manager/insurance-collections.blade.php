@extends('clinic.layouts.app')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-slate-900">➕ {{ __('app.manager.insurance_collections') }}</h1>
    <a href="{{ route('practice.doctor.manager.index') }}"
       class="text-sm font-medium px-4 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700">
        ← {{ __('app.manager.title') }}
    </a>
</div>

{{-- Add a payout received from an insurer --}}
<div class="bg-white rounded-xl shadow-sm p-5 mb-5">
    <h2 class="font-semibold text-slate-800 mb-4">{{ __('app.manager.add_collection') }}</h2>
    <form method="POST" action="{{ route('practice.doctor.manager.insurance-collections.store') }}"
          class="flex flex-wrap items-end gap-3">
        @csrf
        <div>
            <label class="block text-xs text-slate-500 mb-1">{{ __('app.insurance.company') }}</label>
            <select name="insurance_company_id" class="w-52 border rounded px-2 py-1.5 text-sm">
                <option value="">— {{ __('app.insurance.select_company') }} —</option>
                @foreach ($companies as $co)
                    <option value="{{ $co->id }}">{{ $co->displayName() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">{{ __('app.insurance.or_new_company') }}</label>
            <input type="text" name="new_company_name" placeholder="{{ __('app.insurance.new_company_name') }}"
                   class="w-44 border rounded px-2 py-1.5 text-sm">
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">{{ __('app.report.amount') }} ({{ __('app.clinic.currency') }})</label>
            <input type="number" name="amount" step="0.01" min="0.01" required class="w-32 border rounded px-2 py-1.5 text-sm">
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">{{ __('app.report.date') }}</label>
            <input type="date" name="collected_on" value="{{ now()->toDateString() }}" required class="border rounded px-2 py-1.5 text-sm">
        </div>
        <div class="grow max-w-xs">
            <label class="block text-xs text-slate-500 mb-1">{{ __('app.collection.note') }}</label>
            <input type="text" name="note" class="w-full border rounded px-2 py-1.5 text-sm">
        </div>
        <button class="bg-cyan-600 hover:bg-cyan-700 text-white text-sm px-4 py-2 rounded-lg">{{ __('app.manager.save_collection') }}</button>
    </form>
    @error('insurance_company_id')<p class="text-red-600 text-xs mt-2">{{ $message }}</p>@enderror
</div>

{{-- Filter + list --}}
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

<div class="bg-cyan-50 border border-cyan-200 rounded-xl p-4 mb-4 flex items-center justify-between">
    <span class="text-cyan-800 font-medium">{{ __('app.report.total_collected') }}</span>
    <span class="text-2xl font-bold text-cyan-900">{{ number_format($total, 2) }} {{ __('app.clinic.currency') }}</span>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500">
            <tr>
                <th class="px-4 py-3 text-start">{{ __('app.report.date') }}</th>
                <th class="px-4 py-3 text-start">{{ __('app.insurance.company') }}</th>
                <th class="px-4 py-3 text-start">{{ __('app.report.amount') }}</th>
                <th class="px-4 py-3 text-start">{{ __('app.collection.by_label') }}</th>
                <th class="px-4 py-3 text-start">{{ __('app.collection.note') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($collections as $col)
                <tr>
                    <td class="px-4 py-3 whitespace-nowrap">{{ $col->collected_on?->format('Y-m-d') }}</td>
                    <td class="px-4 py-3 text-cyan-800">{{ $col->insuranceCompany?->displayName() ?? '—' }}</td>
                    <td class="px-4 py-3 font-semibold text-slate-800">{{ number_format((float) $col->amount, 2) }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $col->creator?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $col->note ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">{{ __('app.report.empty') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
