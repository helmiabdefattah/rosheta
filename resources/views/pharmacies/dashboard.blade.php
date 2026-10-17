@extends('pharmacies.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'لوحة تحكم الصيدلية' : 'Pharmacy Dashboard')
@section('page-description', app()->getLocale() === 'ar' ? 'نظرة عامة على الصيدلية والإحصائيات' : 'Overview of pharmacy and statistics')

@php
    $statCards = [
        ['ar' => 'طلبات الأدوية المعلقة', 'en' => 'Pending Medicine Requests', 'value' => $stats['pending_requests'], 'color' => 'amber',
         'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        ['ar' => 'إجمالي عروض الصيدلية', 'en' => 'Total Pharmacy Offers', 'value' => $stats['total_offers'], 'color' => 'blue',
         'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 011 12V7a4 4 0 014-4z'],
        ['ar' => 'العروض المقبولة', 'en' => 'Accepted Offers', 'value' => $stats['accepted_offers'], 'color' => 'emerald',
         'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['ar' => 'مستخدمي الصيدلية', 'en' => 'Pharmacy Users', 'value' => $stats['total_users'], 'color' => 'purple',
         'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
    ];
@endphp

@section('content')
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        @foreach($statCards as $card)
            @php $c = $card['color']; @endphp
            <div class="group bg-white rounded-xl shadow-sm border-s-4 border-{{ $c }}-500 p-5 flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-slate-600 truncate">{{ app()->getLocale() === 'ar' ? $card['ar'] : $card['en'] }}</p>
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

    <!-- Recent Pending Medicine Requests -->
    <div class="bg-white rounded-xl shadow-sm mb-6 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                {{ app()->getLocale() === 'ar' ? 'طلبات الأدوية المعلقة' : 'Recent Pending Medicine Requests' }}
            </h2>
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
                <div class="px-6 py-14 text-center">
                    <svg class="w-12 h-12 mx-auto text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <p class="mt-3 text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا توجد طلبات معلقة حالياً' : 'No pending requests at the moment' }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection


