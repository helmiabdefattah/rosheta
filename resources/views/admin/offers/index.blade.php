@extends('admin.layouts.admin')

@php
    $l = app()->getLocale() === 'ar';
@endphp

@section('title', 'Offers')
@section('page-title', $l ? 'العروض' : 'Offers')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow duration-300 max-lg:overflow-visible lg:overflow-hidden">
    <div class="p-4 sm:p-6 border-b border-slate-100">
        <form method="GET" action="{{ route('admin.offers.index') }}" class="flex flex-col sm:flex-row gap-3 sm:items-end sm:justify-between">
            <div class="flex-1 w-full min-w-0">
                <label for="offers-search" class="block text-xs font-medium text-slate-500 mb-1">{{ $l ? 'بحث' : 'Search' }}</label>
                <input type="search" name="search" id="offers-search" value="{{ request('search') }}"
                       placeholder="{{ $l ? 'رقم العرض، العميل، المزود، الحالة…' : 'Offer ID, client, provider, status…' }}"
                       class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary">
            </div>
            <div class="flex gap-2 w-full sm:w-auto">
                <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:opacity-90">
                    {{ $l ? 'بحث' : 'Search' }}
                </button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.offers.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm text-center">{{ $l ? 'مسح' : 'Clear' }}</a>
                @endif
            </div>
        </form>
    </div>

    @if($offers->count() === 0)
        <div class="p-10 text-center text-slate-500">
            {{ $l ? 'لا توجد عروض.' : 'No offers found.' }}
        </div>
    @else
        {{-- Mobile: cards --}}
        <div class="lg:hidden space-y-3 p-4">
            @foreach($offers as $offer)
                @php
                    $statusColors = [
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'accepted' => 'bg-green-100 text-green-800',
                        'rejected' => 'bg-red-100 text-red-800',
                    ];
                    $statusColor = $statusColors[$offer->status] ?? 'bg-gray-100 text-gray-800';
                    $provider = $offer->request_type === 'test'
                        ? ($offer->laboratory->name ?? '—')
                        : ($offer->pharmacy->name ?? '—');
                @endphp
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $l ? 'رقم العرض' : 'Offer ID' }}</p>
                            <p class="text-lg font-bold text-slate-900">#{{ $offer->id }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColor }}">{{ ucfirst($offer->status) }}</span>
                    </div>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'الطلب' : 'Request' }}</dt>
                            <dd class="text-slate-800 font-medium text-end">{{ $offer->request->id ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'العميل' : 'Client' }}</dt>
                            <dd class="text-slate-800 text-end">{{ $offer->request->client->name ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'المزود' : 'Provider' }}</dt>
                            <dd class="text-slate-800 text-end text-xs leading-relaxed max-w-[60%]">{{ $provider }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 pt-1">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'المبلغ' : 'Total' }}</dt>
                            <dd class="text-slate-900 font-semibold text-end">
                                @if($offer->total_price)
                                    {{ $l ? 'ج.م' : 'EGP' }} {{ number_format($offer->total_price, 2) }}
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                    </dl>
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        @include('admin.offers.actions', ['offer' => $offer])
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Desktop: table --}}
        <div class="hidden lg:block overflow-x-auto p-4 sm:p-6 pt-0">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'المعرف' : 'ID' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الطلب' : 'Request' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'العميل' : 'Client' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'المزود' : 'Provider' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الحالة' : 'Status' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'المبلغ' : 'Total' }}</th>
                        <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'إجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($offers as $offer)
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'accepted' => 'bg-green-100 text-green-800',
                                'rejected' => 'bg-red-100 text-red-800',
                            ];
                            $statusColor = $statusColors[$offer->status] ?? 'bg-gray-100 text-gray-800';
                            $provider = $offer->request_type === 'test'
                                ? ($offer->laboratory->name ?? '—')
                                : ($offer->pharmacy->name ?? '—');
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm text-slate-800 font-medium">#{{ $offer->id }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $offer->request->id ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $offer->request->client->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700 max-w-xs truncate" title="{{ $provider }}">{{ $provider }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColor }}">{{ ucfirst($offer->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-800 font-medium">
                                @if($offer->total_price)
                                    {{ $l ? 'ج.م' : 'EGP' }} {{ number_format($offer->total_price, 2) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-end">
                                @include('admin.offers.actions', ['offer' => $offer])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-4 sm:px-6 pb-6 border-t border-slate-100 pt-4">
            {{ $offers->links() }}
        </div>
    @endif
</div>
@endsection
