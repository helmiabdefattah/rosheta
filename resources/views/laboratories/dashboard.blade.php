@extends('laboratories.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'لوحة تحكم المعمل' : 'Laboratory Dashboard')

@section('page-description', app()->getLocale() === 'ar' ? 'نظرة عامة على المعمل والإحصائيات' : 'Overview of laboratory and statistics')

@php
    $l = app()->getLocale() === 'ar';

    // One definition per stat tile keeps the four cards perfectly consistent.
    $cards = [
        ['ar' => 'الطلبات المعلقة', 'en' => 'Pending Requests', 'value' => $stats['pending_requests'], 'color' => 'amber',
         'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        ['ar' => 'إجمالي العروض', 'en' => 'Total Offers', 'value' => $stats['total_offers'], 'color' => 'blue',
         'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 011 12V7a4 4 0 014-4z'],
        ['ar' => 'العروض المقبولة', 'en' => 'Accepted Offers', 'value' => $stats['accepted_offers'], 'color' => 'emerald',
         'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['ar' => 'إجمالي المستخدمين', 'en' => 'Total Users', 'value' => $stats['total_users'], 'color' => 'purple',
         'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
    ];
@endphp

@section('content')
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        @foreach($cards as $card)
            @php $c = $card['color']; @endphp
            <div class="group bg-white rounded-xl shadow-sm border-s-4 border-{{ $c }}-500 p-5 flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-slate-600 truncate">{{ $l ? $card['ar'] : $card['en'] }}</p>
                    <p class="mt-2 text-3xl font-bold text-slate-800">{{ $card['value'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-{{ $c }}-100 text-{{ $c }}-600 flex items-center justify-center shrink-0 transition-colors duration-300 group-hover:bg-{{ $c }}-600 group-hover:text-white">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                    </svg>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Recent Pending Requests -->
    <div class="bg-white rounded-xl shadow-sm mb-6 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-3">
            <h2 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ $l ? 'الطلبات المعلقة الأخيرة' : 'Recent Pending Requests' }}
            </h2>
            <a href="{{ route('laboratories.requests.index') }}" class="group text-teal-600 hover:text-teal-700 text-sm font-medium inline-flex items-center gap-1">
                {{ $l ? 'عرض الكل' : 'View All' }}
                <svg class="w-3.5 h-3.5 transition-transform {{ $l ? 'rotate-180 group-hover:-translate-x-0.5' : 'group-hover:translate-x-0.5' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="overflow-x-auto lg:overflow-x-visible p-2 sm:p-0">
            @if($recentRequests->count() > 0)
                <table class="min-w-full divide-y divide-gray-200 stack-table-mobile">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'رقم الطلب' : 'Request ID' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'العميل' : 'Client' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الهاتف' : 'Phone' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'عدد الفحوصات' : 'Tests Count' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'زيارة منزلية' : 'Home Visit' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'العنوان' : 'Address' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'تاريخ الإنشاء' : 'Created At' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الإجراءات' : 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentRequests as $request)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap" data-label="{{ $l ? 'رقم الطلب' : 'Request ID' }}">
                                    <span class="text-sm font-semibold text-slate-800">#{{ $request->id }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap" data-label="{{ $l ? 'العميل' : 'Client' }}">
                                    <span class="text-sm text-slate-600">{{ $request->client->name ?? 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap" data-label="{{ $l ? 'الهاتف' : 'Phone' }}">
                                    <span class="text-sm text-slate-600">{{ $request->client->phone_number ?? 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap" data-label="{{ $l ? 'عدد الفحوصات' : 'Tests Count' }}">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $request->lines->where('item_type', 'test')->count() ?? 0 }} {{ $l ? 'فحص' : 'Test(s)' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap" data-label="{{ $l ? 'زيارة منزلية' : 'Home Visit' }}">
                                    @php
                                        $isHomeVisit = !is_null($request->client_address_id);
                                    @endphp
                                    @if($isHomeVisit)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                            {{ $l ? 'نعم' : 'Yes' }}
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-slate-100 text-slate-800">
                                            {{ $l ? 'لا' : 'No' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4" data-label="{{ $l ? 'العنوان' : 'Address' }}">
                                    @if($request->address)
                                        @php
                                            $addrParts = [];
                                            if (!empty($request->address->address)) $addrParts[] = $request->address->address;
                                            if (!empty($request->address->area?->name)) $addrParts[] = $request->address->area?->name;
                                            if (!empty($request->address->city?->name)) $addrParts[] = $request->address->city?->name;
                                            if (!empty($request->address->city?->governorate?->name)) $addrParts[] = $request->address->city?->governorate?->name;
                                            $addrString = implode(', ', $addrParts);
                                        @endphp
                                        <span class="text-sm text-slate-600">{{ $addrString }}</span>
                                    @else
                                        <span class="text-sm text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap" data-label="{{ $l ? 'تاريخ الإنشاء' : 'Created At' }}">
                                    <span class="text-sm text-slate-600">{{ $request->created_at->format('Y-m-d H:i') }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium stack-td-actions" data-label="{{ $l ? 'الإجراءات' : 'Actions' }}">
                                    <a href="{{ route('offers.create', ['request' => $request->id]) }}" class="inline-block px-3 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 text-center w-full sm:w-auto transition-colors">
                                        {{ $l ? 'إنشاء عرض' : 'Make Offer' }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="px-6 py-14 text-center">
                    <svg class="w-12 h-12 mx-auto text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <p class="mt-3 text-slate-500">{{ $l ? 'لا توجد طلبات معلقة حالياً' : 'No pending requests at the moment' }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection
