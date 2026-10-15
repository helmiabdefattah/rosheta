@extends('clinic.layouts.app')

@section('content')
    @include('clinic.dental-lab.partials._tabs', ['active' => 'labs'])

    <div class="flex items-center justify-between mb-4">
        <h2 class="font-bold text-slate-800">{{ __('app.dental.lab.title') }}</h2>
        <a href="{{ route('practice.dental-lab.labs.create') }}" class="btn btn-primary btn-sm">+ {{ __('app.dental.lab.add') }}</a>
    </div>

    {{-- Export orders / payments over a date range (defaults to this month). --}}
    <form method="GET" class="card p-4 mb-4">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label for="exp-lab" class="block text-xs text-slate-500 mb-1">{{ __('app.dental.order.lab') }}</label>
                <select name="dental_lab_id" id="exp-lab" class="border rounded-lg px-3 py-2 text-sm">
                    <option value="">{{ __('app.dental.filter.all_labs') }}</option>
                    @foreach($exportLabs as $l)
                        <option value="{{ $l->id }}" @selected((string) request('dental_lab_id') === (string) $l->id)>{{ $l->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="exp-from" class="block text-xs text-slate-500 mb-1">{{ __('app.dental.export.from') }}</label>
                <input type="date" name="from" id="exp-from" value="{{ request('from') }}" class="border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label for="exp-to" class="block text-xs text-slate-500 mb-1">{{ __('app.dental.export.to') }}</label>
                <input type="date" name="to" id="exp-to" value="{{ request('to') }}" class="border rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @include('clinic.dental-lab.partials._export-btn', ['action' => route('practice.dental-lab.exports.orders'), 'format' => 'pdf', 'label' => __('app.dental.export.orders_pdf')])
                @include('clinic.dental-lab.partials._export-btn', ['action' => route('practice.dental-lab.exports.orders'), 'format' => 'excel', 'label' => __('app.dental.export.orders_excel')])
                @include('clinic.dental-lab.partials._export-btn', ['action' => route('practice.dental-lab.exports.payments'), 'format' => 'pdf', 'label' => __('app.dental.export.payments_pdf')])
                @include('clinic.dental-lab.partials._export-btn', ['action' => route('practice.dental-lab.exports.payments'), 'format' => 'excel', 'label' => __('app.dental.export.payments_excel')])
            </div>
        </div>
        <p class="text-xs text-slate-400 mt-2">{{ __('app.dental.export.hint') }}</p>
    </form>

    <div class="card overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-slate-600 text-center">
                <tr>
                    <th class="px-4 py-3 text-center font-semibold">{{ __('app.dental.lab.name') }}</th>
                    <th class="px-4 py-3 text-center font-semibold">{{ __('app.dental.lab.phone') }}</th>
                    <th class="px-4 py-3 text-center font-semibold">{{ __('app.dental.lab.orders_count') }}</th>
                    <th class="px-4 py-3 text-center font-semibold">{{ __('app.dental.lab.status') }}</th>
                    <th class="px-4 py-3 text-center font-semibold">{{ __('app.table.action') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-center">
                @forelse($labs as $lab)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $lab->name }}</td>
                        <td class="px-4 py-3 text-slate-600" dir="ltr">{{ $lab->phone ?: '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $lab->orders_count }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $lab->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                {{ $lab->is_active ? __('app.dental.common.active') : __('app.dental.common.inactive') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <a href="{{ route('practice.dental-lab.labs.statement', $lab) }}" class="btn btn-secondary btn-sm">{{ __('app.dental.lab.account') }}</a>
                            <a href="{{ route('practice.dental-lab.labs.edit', $lab) }}" class="btn btn-secondary btn-sm">{{ __('app.dental.actions.edit') }}</a>
                            <form method="POST" action="{{ route('practice.dental-lab.labs.destroy', $lab) }}" class="inline"
                                  onsubmit="return confirm('{{ __('app.dental.actions.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm">{{ __('app.dental.actions.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-12 text-center text-slate-400">{{ __('app.dental.lab.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $labs->links() }}</div>
@endsection
