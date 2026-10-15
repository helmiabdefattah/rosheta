{{--
    Sub-navigation for the Dental Lab module. Expects: $active (orders|labs|catalog|stock).
--}}
@php
    $tabs = [
        'orders' => ['route' => 'practice.dental-lab.orders.index', 'label' => __('app.dental.tabs.orders'), 'icon' => '📋'],
        'labs' => ['route' => 'practice.dental-lab.labs.index', 'label' => __('app.dental.tabs.labs'), 'icon' => '🏭'],
        'catalog' => ['route' => 'practice.dental-lab.items.index', 'label' => __('app.dental.tabs.catalog'), 'icon' => '🗂️'],
        'stock' => ['route' => 'practice.dental-lab.stock.index', 'label' => __('app.dental.tabs.stock'), 'icon' => '📦'],
    ];
    $active = $active ?? 'orders';
@endphp

<div class="mb-6">
    <div class="flex items-center justify-between gap-3 mb-1 flex-wrap">
        <div class="flex items-center gap-3">
            <span class="text-2xl" aria-hidden="true">🦷</span>
            <div>
                <h1 class="text-xl font-bold text-slate-900">{{ __('app.dental.title') }}</h1>
                <p class="text-sm text-slate-500">{{ __('app.dental.subtitle') }}</p>
            </div>
        </div>
        {{-- Always-available primary action: start a new order. --}}
        <a href="{{ route('practice.dental-lab.orders.create') }}" class="btn btn-primary btn-sm">+ {{ __('app.dental.order.add') }}</a>
    </div>
    <nav class="mt-4 flex gap-1 border-b border-slate-200 overflow-x-auto">
        @foreach($tabs as $key => $tab)
            <a href="{{ route($tab['route']) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold whitespace-nowrap border-b-2 -mb-px transition-colors
                      {{ $active === $key ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' }}">
                <span aria-hidden="true">{{ $tab['icon'] }}</span> {{ $tab['label'] }}
            </a>
        @endforeach
    </nav>
</div>
