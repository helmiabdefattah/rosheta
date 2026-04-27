@extends('admin.layouts.admin')

@php
    $l = app()->getLocale() === 'ar';
@endphp

@section('title', 'Orders')
@section('page-title', $l ? 'الطلبات' : 'Orders')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow duration-300 max-lg:overflow-visible lg:overflow-hidden">
    <div class="p-4 sm:p-6 border-b border-slate-100">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-col sm:flex-row gap-3 sm:items-end sm:justify-between">
            <div class="flex-1 w-full min-w-0">
                <label for="orders-search" class="block text-xs font-medium text-slate-500 mb-1">{{ $l ? 'بحث' : 'Search' }}</label>
                <input type="search" name="search" id="orders-search" value="{{ request('search') }}"
                       placeholder="{{ $l ? 'رقم الطلب، طلب العميل، العميل، الصيدلية، الحالة…' : 'Order ID, request ID, client, pharmacy, status…' }}"
                       class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary">
            </div>
            <div class="flex gap-2 w-full sm:w-auto">
                <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:opacity-90">
                    {{ $l ? 'بحث' : 'Search' }}
                </button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm text-center">{{ $l ? 'مسح' : 'Clear' }}</a>
                @endif
            </div>
        </form>
    </div>

    @if($orders->count() === 0)
        <div class="p-10 text-center text-slate-500">
            {{ $l ? 'لا توجد طلبات.' : 'No orders found.' }}
        </div>
    @else
        <div class="lg:hidden space-y-3 p-4">
            @foreach($orders as $order)
                @php
                    $statusColors = [
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'delivered' => 'bg-green-100 text-green-800',
                        'delivering' => 'bg-blue-100 text-blue-800',
                    ];
                    $statusColor = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800';
                    $provider = $order->pharmacy->name ?? $order->laboratory->name ?? '—';
                @endphp
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $l ? 'رقم الطلب' : 'Order ID' }}</p>
                            <p class="text-lg font-bold text-slate-900">#{{ $order->id }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColor }}">{{ ucfirst($order->status) }}</span>
                    </div>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'طلب العميل' : 'Request' }}</dt>
                            <dd class="text-slate-800 font-medium text-end">{{ $order->request->id ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'العميل' : 'Client' }}</dt>
                            <dd class="text-slate-800 text-end">{{ $order->request->client->name ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'الصيدلية / المعمل' : 'Pharmacy / Lab' }}</dt>
                            <dd class="text-slate-800 text-end text-xs leading-relaxed max-w-[60%]">{{ $provider }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 pt-1">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'المبلغ' : 'Total' }}</dt>
                            <dd class="text-slate-900 font-semibold text-end">
                                @if($order->total_price)
                                    {{ $l ? 'ج.م' : 'EGP' }} {{ number_format($order->total_price, 2) }}
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                    </dl>
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        @include('admin.orders.actions', ['order' => $order])
                    </div>
                </div>
            @endforeach
        </div>

        <div class="hidden lg:block overflow-x-auto p-4 sm:p-6 pt-0">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'المعرف' : 'ID' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الطلب' : 'Request' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'العميل' : 'Client' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الصيدلية / المعمل' : 'Pharmacy / Lab' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الحالة' : 'Status' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'المبلغ' : 'Total' }}</th>
                        <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'إجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($orders as $order)
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'delivered' => 'bg-green-100 text-green-800',
                                'delivering' => 'bg-blue-100 text-blue-800',
                            ];
                            $statusColor = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800';
                            $provider = $order->pharmacy->name ?? $order->laboratory->name ?? '—';
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm text-slate-800 font-medium">#{{ $order->id }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $order->request->id ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $order->request->client->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700 max-w-xs truncate" title="{{ $provider }}">{{ $provider }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColor }}">{{ ucfirst($order->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-800 font-medium">
                                @if($order->total_price)
                                    {{ $l ? 'ج.م' : 'EGP' }} {{ number_format($order->total_price, 2) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-end">
                                @include('admin.orders.actions', ['order' => $order])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-4 sm:px-6 pb-6 border-t border-slate-100 pt-4">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
