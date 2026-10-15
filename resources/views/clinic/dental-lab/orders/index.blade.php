@extends('clinic.layouts.app')

@section('content')
    @include('clinic.dental-lab.partials._tabs', ['active' => 'orders'])

    {{-- At-a-glance: how many orders, how many open, and the two balances. --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="card p-4">
            <div class="text-xs text-slate-400">{{ __('app.dental.stats.orders') }}</div>
            <div class="text-2xl font-black text-slate-900">{{ $stats['count'] }}</div>
        </div>
        <div class="card p-4">
            <div class="text-xs text-slate-400">{{ __('app.dental.stats.open') }}</div>
            <div class="text-2xl font-black text-amber-700">{{ $stats['open'] }}</div>
        </div>
        <div class="card p-4">
            <div class="text-xs text-slate-400">{{ __('app.dental.stats.owed_lab') }}</div>
            <div class="text-2xl font-black {{ $stats['owed_lab'] > 0 ? 'text-red-600' : 'text-slate-900' }}">{{ number_format($stats['owed_lab'], 2) }}</div>
        </div>
        <div class="card p-4">
            <div class="text-xs text-slate-400">{{ __('app.dental.stats.due_patient') }}</div>
            <div class="text-2xl font-black {{ $stats['due_patient'] > 0 ? 'text-amber-700' : 'text-slate-900' }}">{{ number_format($stats['due_patient'], 2) }}</div>
        </div>
    </div>

    <h2 class="font-bold text-slate-800 mb-3">{{ __('app.dental.order.title') }}</h2>

    {{-- Filters --}}
    <form method="GET" class="card p-3 mb-4 flex flex-wrap items-center gap-2">
        <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="{{ __('app.dental.filter.search_ph') }}"
               class="flex-1 min-w-[12rem] border rounded-lg px-3 py-2 text-sm">
        <select name="status" class="border rounded-lg px-3 py-2 text-sm">
            <option value="">{{ __('app.dental.filter.all_statuses') }}</option>
            @foreach(\App\Enums\DentalLabOrderStatus::cases() as $st)
                <option value="{{ $st->value }}" @selected($filters['status'] === $st->value)>{{ $st->label() }}</option>
            @endforeach
        </select>
        <select name="dental_lab_id" class="border rounded-lg px-3 py-2 text-sm">
            <option value="">{{ __('app.dental.filter.all_labs') }}</option>
            @foreach($labs as $lab)
                <option value="{{ $lab->id }}" @selected((string) $filters['dental_lab_id'] === (string) $lab->id)>{{ $lab->name }}</option>
            @endforeach
        </select>
        <select name="level" class="border rounded-lg px-3 py-2 text-sm">
            <option value="">{{ __('app.dental.filter.all_types') }}</option>
            <option value="patient" @selected($filters['level'] === 'patient')>{{ __('app.dental.filter.patient_items') }}</option>
            <option value="clinic" @selected($filters['level'] === 'clinic')>{{ __('app.dental.filter.clinic_items') }}</option>
        </select>
        <button class="btn btn-primary btn-sm">{{ __('app.dental.filter.apply') }}</button>
        @if($filters['q'] !== '' || $filters['status'] !== '' || $filters['dental_lab_id'] !== '' || $filters['level'] !== '')
            <a href="{{ route('practice.dental-lab.orders.index') }}" class="btn btn-secondary btn-sm">{{ __('app.dental.filter.clear') }}</a>
        @endif
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 text-start font-semibold">#</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('app.dental.order.lab') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('app.dental.order.patient') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('app.dental.order.status') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('app.dental.order.total') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('app.dental.order.payment') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('app.dental.order.sent') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('app.dental.order.received_at') }}</th>
                        <th class="px-4 py-3 text-end font-semibold">{{ __('app.table.action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        @php $pay = $order->paymentStatus(); @endphp
                        <tr class="hover:bg-slate-50/70 cursor-pointer" onclick="window.location='{{ route('practice.dental-lab.orders.show', $order) }}'">
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $order->id }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $order->lab?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $order->patient?->name ?? __('app.dental.order.no_patient') }}</td>
                            {{-- Inline status change --}}
                            <td class="px-4 py-3" onclick="event.stopPropagation()">
                                <form method="POST" action="{{ route('practice.dental-lab.orders.status', $order) }}">
                                    @csrf
                                    <select name="status" onchange="this.form.submit()"
                                            class="border rounded-lg px-2 py-1 text-xs font-medium {{ $order->status->color() }}">
                                        @foreach(\App\Enums\DentalLabOrderStatus::cases() as $st)
                                            <option value="{{ $st->value }}" @selected($order->status === $st)>{{ $st->label() }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ number_format($order->total(), 2) }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $pay->color() }}">{{ $pay->label() }}</span></td>
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $order->created_at?->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap {{ $order->received_at ? 'text-emerald-700' : 'text-slate-400' }}">{{ $order->received_at?->translatedFormat('d M Y') ?? '—' }}</td>
                            {{-- Add a lab payment, in a modal --}}
                            <td class="px-4 py-3 text-end whitespace-nowrap" onclick="event.stopPropagation()">
                                <button type="button" class="btn btn-secondary btn-sm"
                                        data-pay-url="{{ route('practice.dental-lab.orders.payments.store', $order) }}"
                                        data-pay-order="{{ $order->id }}"
                                        data-pay-balance="{{ number_format($order->balance(), 2, '.', '') }}">
                                    💵 {{ __('app.dental.payment.add') }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-4 py-12 text-center text-slate-400">{{ __('app.dental.order.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>

    {{-- Record-payment modal (lab payment), reused across rows. --}}
    <div id="pay-modal" class="fixed inset-0 z-[100] items-center justify-center p-3">
        <div class="absolute inset-0 bg-slate-900/50" data-pay-close></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-slate-800">{{ __('app.dental.payment.add') }} <span id="pay-order-label" class="text-slate-400 text-sm"></span></h3>
                <button type="button" data-pay-close class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
            </div>
            <form id="pay-form" method="POST" action="" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-sm font-semibold text-slate-700">{{ __('app.dental.payment.amount') }}</label>
                        <input type="number" step="0.01" min="0.01" name="amount" id="pay-amount" required class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700">{{ __('app.dental.payment.date') }}</label>
                        <input type="date" name="paid_at" value="{{ now()->toDateString() }}" class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">{{ __('app.dental.payment.method') }}</label>
                    <select name="method" class="w-full border rounded-lg px-3 py-2 text-sm">
                        <option value="">{{ __('app.dental.payment.method') }}</option>
                        @foreach(__('app.dental.payment.methods') as $m)<option value="{{ $m }}">{{ $m }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">{{ __('app.dental.payment.note') }}</label>
                    <input type="text" name="note" class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="flex gap-2 pt-1">
                    <button class="btn btn-primary btn-block">{{ __('app.dental.payment.add') }}</button>
                    <button type="button" data-pay-close class="btn btn-secondary">{{ __('app.dental.actions.cancel') }}</button>
                </div>
            </form>
        </div>
    </div>

    @push('styles')
    <style>#pay-modal { display: none; } #pay-modal.open { display: flex; }</style>
    @endpush
    @push('scripts')
    <script>
    (function () {
        var modal = document.getElementById('pay-modal');
        var form = document.getElementById('pay-form');
        var amount = document.getElementById('pay-amount');
        var label = document.getElementById('pay-order-label');
        if (!modal || !form) return;

        function close() { modal.classList.remove('open'); document.body.style.overflow = ''; }

        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-pay-url]');
            if (btn) {
                form.action = btn.getAttribute('data-pay-url');
                var bal = parseFloat(btn.getAttribute('data-pay-balance')) || 0;
                amount.value = bal > 0 ? bal.toFixed(2) : '';
                label.textContent = '#' + btn.getAttribute('data-pay-order');
                modal.classList.add('open');
                document.body.style.overflow = 'hidden';
                setTimeout(function () { amount.focus(); }, 50);
                return;
            }
            if (e.target.closest('[data-pay-close]')) close();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('open')) close();
        });
    })();
    </script>
    @endpush
@endsection
