@extends('laboratories.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'لوحة تحكم المعمل' : 'Laboratory Dashboard')

@section('page-description', app()->getLocale() === 'ar' ? 'نظرة عامة على المعمل والإحصائيات' : 'Overview of laboratory and statistics')

@section('content')
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Pending Requests -->
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600">{{ app()->getLocale() === 'ar' ? 'الطلبات المعلقة' : 'Pending Requests' }}</p>
                    <p class="text-3xl font-bold text-slate-800 mt-2">{{ $stats['pending_requests'] }}</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Offers -->
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600">{{ app()->getLocale() === 'ar' ? 'إجمالي العروض' : 'Total Offers' }}</p>
                    <p class="text-3xl font-bold text-slate-800 mt-2">{{ $stats['total_offers'] }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 011 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Accepted Offers -->
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600">{{ app()->getLocale() === 'ar' ? 'العروض المقبولة' : 'Accepted Offers' }}</p>
                    <p class="text-3xl font-bold text-slate-800 mt-2">{{ $stats['accepted_offers'] }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <svg class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Users -->
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600">{{ app()->getLocale() === 'ar' ? 'إجمالي المستخدمين' : 'Total Users' }}</p>
                    <p class="text-3xl font-bold text-slate-800 mt-2">{{ $stats['total_users'] }}</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <svg class="w-8 h-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Pending Requests -->
    <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'الطلبات المعلقة الأخيرة' : 'Recent Pending Requests' }}</h2>
            <a href="{{ route('laboratories.requests.index') }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                {{ app()->getLocale() === 'ar' ? 'عرض الكل' : 'View All' }} →
            </a>
        </div>
        <div class="overflow-x-auto">
            @if($recentRequests->count() > 0)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'رقم الطلب' : 'Request ID' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'العميل' : 'Client' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'الهاتف' : 'Phone' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'عدد الفحوصات' : 'Tests Count' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'زيارة منزلية' : 'Home Visit' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'تاريخ الإنشاء' : 'Created At' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentRequests as $request)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-semibold text-slate-800">#{{ $request->id }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-slate-600">{{ $request->client->name ?? 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-slate-600">{{ $request->client->phone_number ?? 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $request->lines->where('item_type', 'test')->count() ?? 0 }} {{ app()->getLocale() === 'ar' ? 'فحص' : 'Test(s)' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $isHomeVisit = !is_null($request->client_address_id);
                                    @endphp
                                    @if($isHomeVisit)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                            {{ app()->getLocale() === 'ar' ? 'نعم' : 'Yes' }}
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-slate-100 text-slate-800">
                                            {{ app()->getLocale() === 'ar' ? 'لا' : 'No' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
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
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-slate-600">{{ $request->created_at->format('Y-m-d H:i') }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('offers.create', ['request' => $request->id]) }}" class="text-primary-600 hover:text-primary-900 mr-4">
                                        {{ app()->getLocale() === 'ar' ? 'إنشاء عرض' : 'Make Offer' }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="px-6 py-12 text-center">
                    <p class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا توجد طلبات معلقة حالياً' : 'No pending requests at the moment' }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection
