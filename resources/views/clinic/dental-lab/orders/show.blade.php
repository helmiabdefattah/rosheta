@extends(request()->boolean('embed') ? 'clinic.layouts.embed' : 'clinic.layouts.app')

@php
    use App\Enums\DentalItemLevel;
    $pay = $order->paymentStatus();
    $next = $order->status->next();
    $inp = 'w-full border rounded-lg px-3 py-2 text-sm';
@endphp

@section('content')
    @unless(request()->boolean('embed'))
        @include('clinic.dental-lab.partials._tabs', ['active' => 'orders'])
    @endunless

    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-3">
            <a href="{{ route('practice.dental-lab.orders.index') }}" class="btn btn-secondary btn-sm">← {{ __('app.dental.actions.back') }}</a>
            <h2 class="font-bold text-slate-800">{{ __('app.dental.order.number', ['id' => $order->id]) }}</h2>
            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $order->status->color() }}">{{ $order->status->label() }}</span>
        </div>
        <div class="flex items-center gap-2">
            @unless($order->status->isFinal())
                <a href="{{ route('practice.dental-lab.orders.edit', request()->boolean('embed') ? [$order, 'embed' => 1] : $order) }}" class="btn btn-secondary btn-sm">{{ __('app.dental.actions.edit') }}</a>
            @endunless
            <form method="POST" action="{{ route('practice.dental-lab.orders.status', $order) }}" class="flex items-center gap-1">
                @csrf
                <label class="text-xs text-slate-500">{{ __('app.dental.order.change_status') }}</label>
                <select name="status" onchange="this.form.submit()" class="border rounded-lg px-2 py-1.5 text-sm">
                    @foreach(\App\Enums\DentalLabOrderStatus::cases() as $st)
                        <option value="{{ $st->value }}" @selected($order->status === $st)>{{ $st->label() }}</option>
                    @endforeach
                </select>
                <noscript><button class="btn btn-secondary btn-sm">{{ __('app.dental.actions.save') }}</button></noscript>
            </form>
            <form method="POST" action="{{ route('practice.dental-lab.orders.destroy', $order) }}"
                  onsubmit="return confirm('{{ __('app.dental.order.delete_confirm') }}')">
                @csrf @method('DELETE')
                <button class="btn btn-danger btn-sm">{{ __('app.dental.actions.delete') }}</button>
            </form>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Meta --}}
            <div class="card p-5 grid sm:grid-cols-2 gap-4 text-sm">
                <div><div class="text-xs text-slate-400">{{ __('app.dental.order.lab') }}</div><div class="font-medium text-slate-800">{{ $order->lab?->name ?? '—' }}</div></div>
                <div><div class="text-xs text-slate-400">{{ __('app.dental.order.patient') }}</div><div class="font-medium text-slate-800">{{ $order->patient?->name ?? __('app.dental.order.no_patient') }}</div></div>
                <div><div class="text-xs text-slate-400">{{ __('app.dental.order.sent') }}</div><div class="text-slate-700">{{ $order->created_at?->translatedFormat('d M Y, H:i') }}</div></div>
                <div><div class="text-xs text-slate-400">{{ __('app.dental.order.received_at') }}</div><div class="text-slate-700">{{ $order->received_at?->translatedFormat('d M Y, H:i') ?? '—' }}</div></div>
                @if($order->teeth_mode === 'order' && !empty($order->teeth))
                    <div class="sm:col-span-2"><div class="text-xs text-slate-400 mb-1">{{ __('app.dental.teeth.title') }}</div>
                        <div class="flex flex-wrap gap-1">
                            @foreach($order->teeth as $t)<span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 text-xs font-mono">{{ $t }}</span>@endforeach
                        </div>
                    </div>
                @endif
                @if($order->notes)
                    <div class="sm:col-span-2"><div class="text-xs text-slate-400">{{ __('app.dental.order.notes') }}</div><div class="text-slate-700 whitespace-pre-line">{{ $order->notes }}</div></div>
                @endif
            </div>

            {{-- Items --}}
            <div class="card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-4 py-3 text-start font-semibold">{{ __('app.dental.line.name') }}</th>
                                <th class="px-4 py-3 text-start font-semibold">{{ __('app.dental.line.teeth') }}</th>
                                <th class="px-4 py-3 text-start font-semibold">{{ __('app.dental.line.qty') }}</th>
                                <th class="px-4 py-3 text-start font-semibold">{{ __('app.dental.line.price') }}</th>
                                <th class="px-4 py-3 text-start font-semibold">{{ __('app.dental.line.total') }}</th>
                                <th class="px-4 py-3 text-start font-semibold">{{ __('app.dental.patient_account.operating_fee') }}</th>
                                <th class="px-4 py-3 text-start font-semibold">{{ __('app.dental.patient_account.patient_price') }}</th>
                                <th class="px-4 py-3 text-start font-semibold">{{ __('app.dental.stock.column') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-slate-800">{{ $item->name }}</div>
                                        <div class="text-xs text-slate-400">{{ $item->level->label() }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @php $teeth = $item->effectiveTeeth(); @endphp
                                        @if($teeth)
                                            <div class="flex flex-wrap gap-1">@foreach($teeth as $t)<span class="px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 text-xs font-mono">{{ $t }}</span>@endforeach</div>
                                        @else — @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-700">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }} {{ $item->unit }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ number_format($item->total(), 2) }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ number_format($item->operatingFee(), 2) }}</td>
                                    <td class="px-4 py-3 font-medium text-indigo-700">{{ number_format($item->patientTotal(), 2) }}</td>
                                    <td class="px-4 py-3">
                                        @if($item->tracksStock() && $order->status === \App\Enums\DentalLabOrderStatus::Received)
                                            <form method="POST" action="{{ route('practice.dental-lab.orders.items.toggle-used', [$order, $item]) }}">
                                                @csrf
                                                @if($item->isUsed())
                                                    <div class="flex items-center gap-2">
                                                        <span class="px-2 py-0.5 rounded-full bg-slate-200 text-slate-700 text-xs font-medium">✓ {{ __('app.dental.stock.used') }}</span>
                                                        <button class="text-xs text-indigo-600 hover:underline">{{ __('app.dental.stock.undo_used') }}</button>
                                                    </div>
                                                @else
                                                    <button class="btn btn-secondary btn-sm">{{ __('app.dental.stock.mark_used') }}</button>
                                                @endif
                                            </form>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-50 font-bold text-slate-900">
                                <td class="px-4 py-3" colspan="4">{{ __('app.dental.patient_account.lab_price') }}</td>
                                <td class="px-4 py-3">{{ number_format($order->total(), 2) }}</td>
                                <td class="px-4 py-3">{{ number_format($order->operatingFee(), 2) }}</td>
                                <td class="px-4 py-3 text-indigo-700">{{ number_format($order->patientCharge(), 2) }}</td>
                                <td class="px-4 py-3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Payments --}}
        <div class="space-y-6">
            {{-- Patient account (clinic receivable) — only when the order is for a patient --}}
            @if($order->patient)
                @php $ppay = $order->patientPaymentStatus(); @endphp
                <div class="card p-5 border-indigo-100">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-slate-800">{{ __('app.dental.patient_account.title') }}</h3>
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $ppay->color() }}">{{ $ppay->label() }}</span>
                    </div>
                    <p class="text-sm text-slate-500 mb-3">{{ $order->patient->name }}
                        · <a href="{{ route('practice.patients.show', $order->patient) }}" class="text-indigo-600 hover:underline">{{ __('app.dental.order.patient') }}</a>
                    </p>

                    <dl class="text-sm space-y-1 mb-4">
                        <div class="flex justify-between text-slate-500"><dt>{{ __('app.dental.patient_account.lab_price') }}</dt><dd>{{ number_format($order->total(), 2) }}</dd></div>
                        <div class="flex justify-between text-slate-500"><dt>{{ __('app.dental.patient_account.operating_fee') }}</dt><dd>{{ number_format($order->operatingFee(), 2) }}</dd></div>
                        <div class="flex justify-between border-t border-slate-100 pt-1"><dt class="text-slate-500">{{ __('app.dental.patient_account.patient_price') }}</dt><dd class="font-semibold text-slate-900">{{ number_format($order->patientCharge(), 2) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">{{ __('app.dental.patient_account.paid') }}</dt><dd class="font-semibold text-emerald-700">{{ number_format($order->patientPaidTotal(), 2) }}</dd></div>
                        <div class="flex justify-between border-t border-slate-100 pt-1"><dt class="text-slate-500">{{ __('app.dental.patient_account.balance') }}</dt><dd class="font-bold text-slate-900">{{ number_format($order->patientBalance(), 2) }}</dd></div>
                    </dl>

                    <form method="POST" action="{{ route('practice.dental-lab.orders.patient-payments.store', $order) }}" class="space-y-2 bg-indigo-50/60 rounded-lg p-3">
                        @csrf
                        <div class="text-xs font-bold uppercase text-slate-400">{{ __('app.dental.patient_account.add') }}</div>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" step="0.01" min="0.01" name="amount" value="{{ $order->patientBalance() > 0 ? number_format($order->patientBalance(), 2, '.', '') : '' }}" placeholder="{{ __('app.dental.payment.amount') }}" required class="{{ $inp }}">
                            <input type="date" name="paid_at" value="{{ now()->toDateString() }}" class="{{ $inp }}">
                        </div>
                        <select name="method" class="{{ $inp }}"><option value="">{{ __('app.dental.payment.method') }}</option>@foreach(__('app.dental.payment.methods') as $m)<option value="{{ $m }}">{{ $m }}</option>@endforeach</select>
                        <button class="btn btn-primary btn-sm btn-block">{{ __('app.dental.patient_account.add') }}</button>
                    </form>

                    <div class="mt-3 divide-y divide-slate-100">
                        @forelse($order->patientPayments->sortByDesc('paid_at') as $pp)
                            <div class="py-2 flex items-start justify-between gap-2">
                                <div>
                                    <div class="font-medium text-slate-800">{{ number_format($pp->amount, 2) }}@if($pp->method)<span class="text-xs text-slate-400"> · {{ $pp->method }}</span>@endif</div>
                                    <div class="text-xs text-slate-400">{{ $pp->paid_at?->translatedFormat('d M Y') }}@if($pp->creator) · {{ __('app.dental.payment.by') }} {{ $pp->creator->name }}@endif</div>
                                </div>
                                <form method="POST" action="{{ route('practice.dental-lab.patient-payments.destroy', $pp) }}" onsubmit="return confirm('{{ __('app.dental.actions.confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-500 hover:text-red-700 text-xs">✕</button>
                                </form>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400 py-2">{{ __('app.dental.payment.empty') }}</p>
                        @endforelse
                    </div>
                </div>
            @endif

            {{-- Lab payments (clinic → lab) --}}
            <div class="card p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-bold text-slate-800">{{ __('app.dental.payment.title') }}</h3>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $pay->color() }}">{{ $pay->label() }}</span>
                </div>

                <dl class="text-sm space-y-1 mb-4">
                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('app.dental.patient_account.lab_price') }}</dt><dd class="font-semibold text-slate-900">{{ number_format($order->total(), 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('app.dental.order.paid') }}</dt><dd class="font-semibold text-emerald-700">{{ number_format($order->paidTotal(), 2) }}</dd></div>
                    <div class="flex justify-between border-t border-slate-100 pt-1"><dt class="text-slate-500">{{ __('app.dental.order.balance') }}</dt><dd class="font-bold text-slate-900">{{ number_format($order->balance(), 2) }}</dd></div>
                </dl>

                <form method="POST" action="{{ route('practice.dental-lab.orders.payments.store', $order) }}" class="space-y-2 bg-slate-50 rounded-lg p-3">
                    @csrf
                    <div class="text-xs font-bold uppercase text-slate-400">{{ __('app.dental.payment.add') }}</div>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" step="0.01" min="0.01" name="amount" value="{{ $order->balance() > 0 ? number_format($order->balance(), 2, '.', '') : '' }}" placeholder="{{ __('app.dental.payment.amount') }}" required class="{{ $inp }}">
                        <input type="date" name="paid_at" value="{{ now()->toDateString() }}" class="{{ $inp }}">
                    </div>
                    <select name="method" class="{{ $inp }}"><option value="">{{ __('app.dental.payment.method') }}</option>@foreach(__('app.dental.payment.methods') as $m)<option value="{{ $m }}">{{ $m }}</option>@endforeach</select>
                    <input type="text" name="note" placeholder="{{ __('app.dental.payment.note') }}" class="{{ $inp }}">
                    <button class="btn btn-primary btn-sm btn-block">{{ __('app.dental.payment.add') }}</button>
                </form>

                <div class="mt-4 divide-y divide-slate-100">
                    @forelse($order->payments->sortByDesc('paid_at') as $payment)
                        <div class="py-2 flex items-start justify-between gap-2">
                            <div>
                                <div class="font-medium text-slate-800">{{ number_format($payment->amount, 2) }}
                                    @if($payment->method)<span class="text-xs text-slate-400">· {{ $payment->method }}</span>@endif
                                </div>
                                <div class="text-xs text-slate-400">
                                    {{ $payment->paid_at?->translatedFormat('d M Y') }}
                                    @if($payment->creator) · {{ __('app.dental.payment.by') }} {{ $payment->creator->name }}@endif
                                    @if($payment->note) · {{ $payment->note }}@endif
                                </div>
                            </div>
                            <form method="POST" action="{{ route('practice.dental-lab.payments.destroy', $payment) }}"
                                  onsubmit="return confirm('{{ __('app.dental.actions.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="text-red-500 hover:text-red-700 text-xs">✕</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400 py-2">{{ __('app.dental.payment.empty') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
