@extends('clinic.layouts.app')

@php($cur = __('app.expenses.currency'))

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">{{ $expense->title }}</h1>
        <p class="text-slate-500 text-sm">
            {{ $expense->category?->icon }} {{ $expense->category?->name ?? __('app.expenses.f.uncategorised') }}
            · {{ $expense->expense_date?->format('Y-m-d') }}
        </p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('practice.doctor.expenses.index') }}" class="btn btn-secondary btn-sm">← {{ __('app.expenses.title') }}</a>
        <a href="{{ route('practice.doctor.expenses.edit', $expense) }}" class="btn btn-secondary btn-sm">✏️ {{ __('app.expenses.edit') }}</a>
        <form method="POST" action="{{ route('practice.doctor.expenses.destroy', $expense) }}" onsubmit="return confirm('{{ __('app.dental.actions.confirm_delete') }}')">
            @csrf @method('DELETE')
            <button class="btn btn-danger btn-sm">🗑 {{ __('app.expenses.f.delete') }}</button>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    {{-- Summary --}}
    <div class="card p-5 space-y-3">
        <div class="flex items-center justify-between">
            <span class="text-slate-500 text-sm">{{ __('app.expenses.f.amount') }}</span>
            <span class="font-semibold">{{ number_format((float) $expense->amount, 2) }} {{ $cur }}</span>
        </div>
        @if ((float) $expense->tax_amount > 0)
            <div class="flex items-center justify-between">
                <span class="text-slate-500 text-sm">{{ __('app.expenses.f.tax') }}</span>
                <span>{{ number_format((float) $expense->tax_amount, 2) }} {{ $cur }}</span>
            </div>
        @endif
        <div class="flex items-center justify-between border-t pt-2">
            <span class="text-slate-500 text-sm">{{ __('app.expenses.total') }}</span>
            <span class="font-bold text-lg">{{ number_format($expense->gross(), 2) }} {{ $cur }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-slate-500 text-sm">{{ __('app.expenses.paid') }}</span>
            <span class="text-emerald-700">{{ number_format($expense->paidTotal(), 2) }} {{ $cur }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-slate-500 text-sm">{{ __('app.expenses.outstanding') }}</span>
            <span class="text-red-700 font-semibold">{{ number_format($expense->balance(), 2) }} {{ $cur }}</span>
        </div>
        <div class="pt-1">
            <span class="inline-block text-xs px-2 py-1 rounded-full {{ $expense->status->color() }}">{{ $expense->status->label() }}</span>
        </div>
        @if ($expense->reference_no)
            <p class="text-xs text-slate-400">{{ __('app.expenses.f.reference') }}: {{ $expense->reference_no }}</p>
        @endif
        @if ($expense->description)
            <p class="text-sm text-slate-600 border-t pt-2 whitespace-pre-line">{{ $expense->description }}</p>
        @endif
    </div>

    {{-- Payments ledger + add --}}
    <div class="card p-5 lg:col-span-2">
        <h2 class="font-semibold text-slate-800 mb-3">{{ __('app.expenses.payments') }}</h2>

        @if ($expense->payments->isEmpty())
            <p class="text-sm text-slate-400 mb-4">{{ __('app.expenses.no_payments') }}</p>
        @else
            <div class="overflow-x-auto mb-4">
                <table class="w-full text-sm">
                    <thead class="text-slate-500 text-start">
                        <tr class="border-b">
                            <th class="py-2 text-start">{{ __('app.expenses.p.date') }}</th>
                            <th class="py-2 text-start">{{ __('app.expenses.p.amount') }}</th>
                            <th class="py-2 text-start">{{ __('app.expenses.p.method') }}</th>
                            <th class="py-2 text-start">{{ __('app.expenses.p.note') }}</th>
                            <th class="py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($expense->payments->sortByDesc('paid_at') as $p)
                            <tr class="border-b last:border-0">
                                <td class="py-2">{{ $p->paid_at?->format('Y-m-d') }}</td>
                                <td class="py-2">{{ number_format((float) $p->amount, 2) }} {{ $cur }}</td>
                                <td class="py-2">{{ $p->method?->label() ?? '—' }}</td>
                                <td class="py-2 text-slate-500">{{ $p->note ?: '—' }}</td>
                                <td class="py-2 text-end">
                                    <form method="POST" action="{{ route('practice.doctor.expenses.payments.destroy', $p) }}" onsubmit="return confirm('{{ __('app.dental.actions.confirm_delete') }}')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 hover:underline text-xs">{{ __('app.expenses.f.delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($expense->balance() > 0)
            <form method="POST" action="{{ route('practice.doctor.expenses.payments.store', $expense) }}" class="grid grid-cols-1 sm:grid-cols-4 gap-2 items-end border-t pt-4">
                @csrf
                <div>
                    <label class="block text-xs text-slate-500 mb-1">{{ __('app.expenses.p.amount') }}</label>
                    <input type="number" step="0.01" min="0.01" max="{{ $expense->balance() }}" name="amount" value="{{ $expense->balance() }}" required class="w-full rounded-lg border px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">{{ __('app.expenses.p.date') }}</label>
                    <input type="date" name="paid_at" value="{{ now()->format('Y-m-d') }}" required class="w-full rounded-lg border px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">{{ __('app.expenses.p.method') }}</label>
                    <select name="method" class="w-full rounded-lg border px-3 py-2">
                        <option value="">—</option>
                        @foreach (\App\Enums\ExpensePaymentMethod::cases() as $m)
                            <option value="{{ $m->value }}">{{ $m->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button class="btn btn-complete btn-block">{{ __('app.expenses.add_payment') }}</button>
                </div>
            </form>
        @endif

        {{-- Receipts --}}
        <h2 class="font-semibold text-slate-800 mt-6 mb-3">{{ __('app.expenses.f.receipts') }}</h2>
        @php($receipts = $expense->getMedia('receipts'))
        @if ($receipts->isEmpty())
            <p class="text-sm text-slate-400">{{ __('app.expenses.no_receipts') }}</p>
        @else
            <div class="flex flex-wrap gap-3">
                @foreach ($receipts as $media)
                    <a href="{{ $media->getUrl() }}" target="_blank" class="block w-24 h-24 rounded-lg border bg-slate-50 overflow-hidden flex items-center justify-center text-3xl hover:ring-2 hover:ring-indigo-300">
                        @if (str_starts_with((string) $media->mime_type, 'image/'))
                            <img src="{{ $media->getUrl() }}" alt="" class="w-full h-full object-cover">
                        @else
                            📄
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
