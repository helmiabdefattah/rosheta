@extends('clinic.layouts.app')

@php use App\Enums\StockMovementType; $inp = 'w-full border rounded-lg px-3 py-2 text-sm'; @endphp

@section('content')
    @include('clinic.dental-lab.partials._tabs', ['active' => 'stock'])

    {{-- Export the current stock levels. --}}
    <form method="GET" class="flex flex-wrap items-center justify-end gap-2 mb-4">
        @include('clinic.dental-lab.partials._export-btn', ['action' => route('practice.dental-lab.stock.export'), 'format' => 'pdf', 'label' => __('app.dental.export.stock_pdf')])
        @include('clinic.dental-lab.partials._export-btn', ['action' => route('practice.dental-lab.stock.export'), 'format' => 'excel', 'label' => __('app.dental.export.stock_excel')])
    </form>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Add material --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="card p-5">
                <h2 class="font-bold text-slate-800 mb-3">{{ __('app.dental.stock.add') }}</h2>
                <form method="POST" action="{{ route('practice.dental-lab.stock.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-sm font-semibold text-slate-700">{{ __('app.dental.stock.name') }}</label>
                        {{-- Pick a material from the catalog, or add a new one. --}}
                        <select id="mat-select" class="{{ $inp }}">
                            <option value="">{{ __('app.dental.stock.choose') }}</option>
                            @foreach($materials as $m)
                                <option value="{{ $m->id }}" data-name="{{ $m->displayName() }}" data-unit="{{ $m->default_unit }}">{{ $m->displayName() }}</option>
                            @endforeach
                            <option value="__new__">➕ {{ __('app.dental.stock.new_material') }}</option>
                        </select>
                        <input type="hidden" name="dental_item_id" id="mat-item-id" value="">
                        {{-- The actual submitted name: filled from the choice, or typed for a new material. --}}
                        <input type="text" name="name" id="mat-name" value="{{ old('name') }}" class="{{ $inp }} mt-2 hidden" placeholder="{{ __('app.dental.stock.new_material') }}">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">{{ __('app.dental.stock.unit') }}</label>
                            <select name="unit" id="mat-unit" required class="{{ $inp }}">
                                @foreach(__('app.dental.units') as $code => $label)
                                    <option value="{{ $code }}" @selected(old('unit', 'piece') === $code)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">{{ __('app.dental.stock.opening') }}</label>
                            <input type="number" step="0.01" min="0" name="opening_quantity" value="{{ old('opening_quantity', 0) }}" class="{{ $inp }}">
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700">{{ __('app.dental.stock.reorder') }}</label>
                        <input type="number" step="0.01" min="0" name="reorder_level" value="{{ old('reorder_level') }}" class="{{ $inp }}">
                    </div>
                    <button class="btn btn-primary btn-block">{{ __('app.dental.actions.add') }}</button>
                </form>
                <p class="text-xs text-slate-400 mt-3">{{ __('app.dental.stock.set_hint') }}</p>
            </div>
        </div>

        {{-- Stock items + movements --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="card p-5">
                <h2 class="font-bold text-slate-800 mb-3">{{ __('app.dental.stock.title') }}</h2>
                @forelse($items as $item)
                    <details class="border-b border-slate-100 py-3">
                        <summary class="flex items-center justify-between cursor-pointer list-none gap-3">
                            <span class="min-w-0">
                                <span class="font-medium text-slate-800">{{ $item->name }}</span>
                                @if($item->isLow())
                                    <span class="ms-2 px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-xs font-semibold">{{ __('app.dental.stock.low') }}</span>
                                @endif
                            </span>
                            <span class="text-sm text-slate-600 whitespace-nowrap">
                                <b class="{{ $item->isLow() ? 'text-red-600' : 'text-slate-900' }}">{{ rtrim(rtrim(number_format($item->quantity_on_hand, 2), '0'), '.') }}</b>
                                {{ $item->unit }}
                            </span>
                        </summary>

                        <div class="mt-3 grid sm:grid-cols-3 gap-3">
                            {{-- Stock in: add received / purchased materials directly --}}
                            <form method="POST" action="{{ route('practice.dental-lab.stock.adjust', $item) }}" class="space-y-2 bg-emerald-50 rounded-lg p-3">
                                @csrf
                                <input type="hidden" name="type" value="{{ \App\Enums\StockMovementType::In->value }}">
                                <div class="text-xs font-bold uppercase text-emerald-700">{{ __('app.dental.stock.stock_in') }}</div>
                                <input type="number" step="0.01" min="0.01" name="quantity" placeholder="{{ __('app.dental.stock.qty') }}" required class="{{ $inp }}">
                                <input type="text" name="reason" placeholder="{{ __('app.dental.stock.reason') }}" class="{{ $inp }}">
                                <button class="btn btn-complete btn-sm btn-block">+ {{ __('app.dental.stock.stock_in') }}</button>
                            </form>

                            {{-- Out / stock-take --}}
                            <form method="POST" action="{{ route('practice.dental-lab.stock.adjust', $item) }}" class="space-y-2 bg-slate-50 rounded-lg p-3">
                                @csrf
                                <div class="text-xs font-bold uppercase text-slate-400">{{ __('app.dental.stock.out_adjust') }}</div>
                                <select name="type" class="{{ $inp }}">
                                    @foreach(StockMovementType::cases() as $t)
                                        @continue($t === StockMovementType::In)
                                        <option value="{{ $t->value }}">{{ $t->label() }}</option>
                                    @endforeach
                                </select>
                                <input type="number" step="0.01" min="0.01" name="quantity" placeholder="{{ __('app.dental.stock.qty') }}" required class="{{ $inp }}">
                                <input type="text" name="reason" placeholder="{{ __('app.dental.stock.reason') }}" class="{{ $inp }}">
                                <button class="btn btn-secondary btn-sm btn-block">{{ __('app.dental.stock.adjust') }}</button>
                            </form>

                            {{-- Edit --}}
                            <form method="POST" action="{{ route('practice.dental-lab.stock.update', $item) }}" class="space-y-2 bg-slate-50 rounded-lg p-3">
                                @csrf @method('PUT')
                                <div class="text-xs font-bold uppercase text-slate-400">{{ __('app.dental.actions.edit') }}</div>
                                <input type="text" name="name" value="{{ $item->name }}" required class="{{ $inp }}">
                                <div class="grid grid-cols-2 gap-2">
                                    <select name="unit" required class="{{ $inp }}">
                                        @foreach(__('app.dental.units') as $code => $label)
                                            <option value="{{ $code }}" @selected($item->unit === $code)>{{ $label }}</option>
                                        @endforeach
                                        @unless(array_key_exists($item->unit, (array) __('app.dental.units')))
                                            <option value="{{ $item->unit }}" selected>{{ $item->unit }}</option>
                                        @endunless
                                    </select>
                                    <input type="number" step="0.01" min="0" name="reorder_level" value="{{ $item->reorder_level }}" class="{{ $inp }}" placeholder="{{ __('app.dental.stock.reorder') }}">
                                </div>
                                <select name="dental_item_id" class="{{ $inp }}">
                                    <option value="">{{ __('app.dental.stock.link_none') }}</option>
                                    @foreach($materials as $m)
                                        <option value="{{ $m->id }}" @selected($item->dental_item_id == $m->id)>{{ $m->displayName() }}</option>
                                    @endforeach
                                </select>
                                <label class="flex items-center gap-2 text-sm text-slate-700">
                                    <input type="checkbox" name="is_active" value="1" @checked($item->is_active) class="w-4 h-4 rounded"> {{ __('app.dental.common.active') }}
                                </label>
                                <button class="btn btn-secondary btn-sm">{{ __('app.dental.actions.save') }}</button>
                            </form>
                        </div>
                    </details>
                @empty
                    <p class="text-sm text-slate-400 py-6 text-center">{{ __('app.dental.stock.empty') }}</p>
                @endforelse
            </div>

            {{-- Received patient orders: prosthetics back from the lab, ready to deliver. --}}
            <div class="card p-5">
                <div class="flex items-center justify-between mb-1">
                    <h2 class="font-bold text-slate-800">{{ __('app.dental.stock.received_title') }}</h2>
                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">{{ $receivedOrders->count() }}</span>
                </div>
                <p class="text-xs text-slate-400 mb-3">{{ __('app.dental.stock.received_hint') }}</p>
                @if($receivedOrders->isEmpty())
                    <p class="text-sm text-slate-400 py-4 text-center">{{ __('app.dental.stock.received_empty') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-slate-500">
                                <tr>
                                    <th class="py-2 text-start font-semibold">#</th>
                                    <th class="py-2 text-start font-semibold">{{ __('app.dental.order.patient') }}</th>
                                    <th class="py-2 text-start font-semibold">{{ __('app.dental.order.lab') }}</th>
                                    <th class="py-2 text-start font-semibold">{{ __('app.dental.stock.received_items') }}</th>
                                    <th class="py-2 text-start font-semibold">{{ __('app.dental.order.received_at') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($receivedOrders as $order)
                                    <tr class="hover:bg-slate-50/70 cursor-pointer" onclick="window.location='{{ route('practice.dental-lab.orders.show', $order) }}'">
                                        <td class="py-2 font-semibold text-slate-800">{{ $order->id }}</td>
                                        <td class="py-2 text-slate-700">{{ $order->patient?->name ?? __('app.dental.order.no_patient') }}</td>
                                        <td class="py-2 text-slate-600">{{ $order->lab?->name ?? '—' }}</td>
                                        <td class="py-2 text-slate-600">{{ $order->items->pluck('name')->filter()->implode('، ') ?: '—' }}</td>
                                        <td class="py-2 text-emerald-700 whitespace-nowrap">{{ $order->received_at?->translatedFormat('d M Y') ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Movements ledger --}}
            <div class="card p-5">
                <h2 class="font-bold text-slate-800 mb-3">{{ __('app.dental.stock.movements') }}</h2>
                @if($movements->isEmpty())
                    <p class="text-sm text-slate-400 py-4">{{ __('app.dental.stock.movements_empty') }}</p>
                @else
                    <table class="min-w-full text-sm">
                        <thead class="text-slate-500 text-start">
                            <tr>
                                <th class="py-2 text-start font-semibold">{{ __('app.dental.stock.name') }}</th>
                                <th class="py-2 text-start font-semibold">{{ __('app.dental.stock.lab') }}</th>
                                <th class="py-2 text-start font-semibold">{{ __('app.dental.stock.type') }}</th>
                                <th class="py-2 text-start font-semibold">{{ __('app.dental.stock.qty') }}</th>
                                <th class="py-2 text-start font-semibold">{{ __('app.dental.stock.reason') }}</th>
                                <th class="py-2 text-start font-semibold">{{ __('app.dental.stock.when') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($movements as $m)
                                <tr>
                                    <td class="py-2 text-slate-700">{{ $m->stockItem?->name ?? '—' }}</td>
                                    <td class="py-2 text-slate-600">{{ $m->source?->lab?->name ?? '—' }}</td>
                                    <td class="py-2"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $m->type->color() }}">{{ $m->type->label() }}</span></td>
                                    <td class="py-2 font-mono {{ $m->quantity < 0 ? 'text-red-600' : 'text-emerald-700' }}">{{ $m->quantity > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($m->quantity, 2), '0'), '.') }}</td>
                                    <td class="py-2 text-slate-500">{{ $m->reason ?: '—' }}</td>
                                    <td class="py-2 text-slate-500 whitespace-nowrap">{{ $m->happened_at?->translatedFormat('d M, H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        var sel = document.getElementById('mat-select');
        if (!sel) return;
        var itemId = document.getElementById('mat-item-id');
        var name = document.getElementById('mat-name');
        var unit = document.getElementById('mat-unit');

        sel.addEventListener('change', function () {
            var opt = sel.selectedOptions[0];
            var v = sel.value;
            if (v === '__new__') {
                itemId.value = '';
                name.classList.remove('hidden');
                name.required = true;
                name.value = '';
                name.focus();
            } else if (v === '') {
                itemId.value = '';
                name.classList.add('hidden');
                name.required = false;
                name.value = '';
            } else {
                // An existing catalog material: carry its id + name (hidden input
                // still submits) and prefill the unit.
                itemId.value = v;
                name.classList.add('hidden');
                name.required = false;
                name.value = opt.getAttribute('data-name') || '';
                if (opt.getAttribute('data-unit')) unit.value = opt.getAttribute('data-unit');
            }
        });
    })();
    </script>
    @endpush
@endsection
