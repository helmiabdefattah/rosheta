@extends('pharmacies.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'لوحة تحكم الصيدلية' : 'Pharmacy Dashboard')
@section('page-description', app()->getLocale() === 'ar' ? 'نظرة عامة على الصيدلية والإحصائيات' : 'Overview of pharmacy and statistics')

@section('content')
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Pending Requests -->
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600">{{ app()->getLocale() === 'ar' ? 'طلبات الأدوية المعلقة' : 'Pending Medicine Requests' }}</p>
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
                    <p class="text-sm font-medium text-slate-600">{{ app()->getLocale() === 'ar' ? 'إجمالي عروض الصيدلية' : 'Total Pharmacy Offers' }}</p>
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

        <!-- Pharmacy Users -->
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600">{{ app()->getLocale() === 'ar' ? 'مستخدمي الصيدلية' : 'Pharmacy Users' }}</p>
                    <p class="text-3xl font-bold text-slate-800 mt-2">{{ $stats['total_users'] }}</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <svg class="w-8 h-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Pending Medicine Requests -->
    <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'طلبات الأدوية المعلقة' : 'Recent Pending Medicine Requests' }}</h2>
        </div>
        <div class="p-2 sm:p-0">
            @if($recentRequests->count() > 0)
                @php $l = app()->getLocale() === 'ar'; @endphp

                {{-- Mobile: real card layout (no <table>); does not depend on mobile-stack-tables.css --}}
                <div class="lg:hidden space-y-3 px-1 pb-2">
                    @foreach($recentRequests as $request)
                        @php
                            $isForThisPharmacy = $request->model_type === 'App\Models\Pharmacy' && $request->model_id == $pharmacy->id;
                            $addrString = '-';
                            if ($request->address) {
                                $addrParts = [];
                                if (!empty($request->address->address)) $addrParts[] = $request->address->address;
                                if (!empty($request->address->area?->name)) $addrParts[] = $request->address->area?->name;
                                if (!empty($request->address->city?->name)) $addrParts[] = $request->address->city?->name;
                                if (!empty($request->address->city?->governorate?->name)) $addrParts[] = $request->address->city?->governorate?->name;
                                $addrString = implode(', ', $addrParts);
                            }
                        @endphp
                        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm {{ $isForThisPharmacy ? 'ring-2 ring-green-500/40 border-green-200' : '' }}">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $l ? 'رقم الطلب' : 'Request ID' }}</span>
                                    <span class="text-base font-bold text-slate-900">#{{ $request->id }}</span>
                                    @if($isForThisPharmacy)
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-600 text-white">{{ $l ? 'خاص بك' : 'For You' }}</span>
                                    @endif
                                </div>
                                <a href="{{ route('offers.create', ['request' => $request->id]) }}" class="shrink-0 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700">
                                    {{ $l ? 'إنشاء عرض' : 'Make Offer' }}
                                </a>
                            </div>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                                    <dt class="text-slate-500 shrink-0">{{ $l ? 'العميل' : 'Client' }}</dt>
                                    <dd class="text-slate-800 text-end font-medium">{{ $request->client->name ?? 'N/A' }}</dd>
                                </div>
                                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                                    <dt class="text-slate-500 shrink-0">{{ $l ? 'الهاتف' : 'Phone' }}</dt>
                                    <dd class="text-slate-800 text-end">{{ $request->client->phone_number ?? 'N/A' }}</dd>
                                </div>
                                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                                    <dt class="text-slate-500 shrink-0">{{ $l ? 'عدد الأدوية' : 'Medicines' }}</dt>
                                    <dd class="text-end">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $request->lines->where('item_type', 'medicine')->count() ?? 0 }} {{ $l ? 'دواء' : 'Medicine(s)' }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                                    <dt class="text-slate-500 shrink-0">{{ $l ? 'العنوان' : 'Address' }}</dt>
                                    <dd class="text-slate-700 text-end text-xs leading-relaxed max-w-[65%]">{{ $addrString }}</dd>
                                </div>
                                <div class="flex justify-between gap-3 pt-1">
                                    <dt class="text-slate-500 shrink-0">{{ $l ? 'تاريخ الإنشاء' : 'Created At' }}</dt>
                                    <dd class="text-slate-600 text-end text-xs">{{ $request->created_at->format('Y-m-d H:i') }}</dd>
                                </div>
                            </dl>
                        </div>
                    @endforeach
                </div>

                {{-- Desktop / large tablet: classic table --}}
                <div class="hidden lg:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'رقم الطلب' : 'Request ID' }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'العميل' : 'Client' }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الهاتف' : 'Phone' }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'عدد الأدوية' : 'Medicines Count' }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'العنوان' : 'Address' }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'تاريخ الإنشاء' : 'Created At' }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الإجراءات' : 'Actions' }}</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($recentRequests as $request)
                        @php
                            $isForThisPharmacy = $request->model_type === 'App\Models\Pharmacy' && $request->model_id == $pharmacy->id;
                            $addrString = '-';
                            if ($request->address) {
                                $addrParts = [];
                                if (!empty($request->address->address)) $addrParts[] = $request->address->address;
                                if (!empty($request->address->area?->name)) $addrParts[] = $request->address->area?->name;
                                if (!empty($request->address->city?->name)) $addrParts[] = $request->address->city?->name;
                                if (!empty($request->address->city?->governorate?->name)) $addrParts[] = $request->address->city?->governorate?->name;
                                $addrString = implode(', ', $addrParts);
                            }
                        @endphp
                        <tr class="hover:bg-gray-50 {{ $isForThisPharmacy ? 'bg-green-50 border-l-4 border-green-500' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-sm font-semibold text-slate-800">#{{ $request->id }}</span>
                                    @if($isForThisPharmacy)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-600 text-white">
                                            {{ $l ? 'خاص بك' : 'For You' }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-slate-600">{{ $request->client->name ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-slate-600">{{ $request->client->phone_number ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $request->lines->where('item_type', 'medicine')->count() ?? 0 }} {{ $l ? 'دواء' : 'Medicine(s)' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-slate-600">{{ $addrString }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-slate-600">{{ $request->created_at->format('Y-m-d H:i') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('offers.create', ['request' => $request->id]) }}" class="inline-block px-3 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
                                    {{ $l ? 'إنشاء عرض' : 'Make Offer' }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <p class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا توجد طلبات معلقة حالياً' : 'No pending requests at the moment' }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection


