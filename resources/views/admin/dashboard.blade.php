@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard')
@section('page-title', app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard')
@section('page-description', app()->getLocale() === 'ar' ? 'نظرة عامة على أداء النظام وإحصائيات سريعة' : 'Overview of system performance and quick statistics')

@php
    $isAr = app()->getLocale() === 'ar';

    // One source of truth for the overview tiles: label, value, destination,
    // accent colour and icon. Rendering them in a loop keeps every card
    // identical in structure (and makes the whole card a link, not just a
    // small "View" control). Dynamic colour classes resolve at render time
    // and are picked up by the Tailwind CDN because they appear verbatim in
    // the emitted HTML.
    $cards = [
        ['ar' => 'الأدوية', 'en' => 'Medicines', 'value' => number_format($stats['medicines']), 'route' => route('admin.medicines.index'), 'color' => 'blue',
         'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'],
        ['ar' => 'المستخدمون', 'en' => 'Users', 'value' => number_format($stats['users']), 'route' => route('admin.users.index'), 'color' => 'emerald',
         'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ['ar' => 'الصيدليات', 'en' => 'Pharmacies', 'value' => number_format($stats['pharmacies']), 'route' => route('admin.pharmacies.index'), 'color' => 'purple',
         'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
        ['ar' => 'المعامل', 'en' => 'Laboratories', 'value' => number_format($stats['laboratories']), 'route' => route('admin.laboratories.index'), 'color' => 'cyan',
         'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547'],
        ['ar' => 'الأطباء', 'en' => 'Doctors', 'value' => number_format($stats['doctors']), 'route' => route('admin.doctors.index'), 'color' => 'teal',
         'icon' => 'M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['ar' => 'العيادات', 'en' => 'Clinics', 'value' => number_format($stats['clinics']), 'route' => route('admin.clinics.index'), 'color' => 'sky',
         'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
        ['ar' => 'طلبات العملاء', 'en' => 'Requests', 'value' => number_format($stats['client_requests']), 'route' => route('admin.client-requests.index'), 'color' => 'red',
         'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        ['ar' => 'الطلبات', 'en' => 'Orders', 'value' => number_format($stats['orders']), 'route' => route('admin.orders.index'), 'color' => 'indigo',
         'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
        ['ar' => 'العروض', 'en' => 'Offers', 'value' => number_format($stats['offers']), 'route' => route('admin.offers.index'), 'color' => 'amber',
         'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['ar' => 'نقاط المكافآت', 'en' => 'Bonus Points', 'value' => '—', 'route' => route('admin.bonus-points.index'), 'color' => 'yellow',
         'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0L15 6l3.951.573c.97.141 1.36 1.333.657 2.01l-2.857 2.786.674 3.936c.166.968-.852 1.705-1.722 1.249L12 15.347l-3.703 1.207c-.87.456-1.888-.281-1.722-1.249l.674-3.936L4.392 8.583c-.703-.677-.313-1.869.657-2.01L9 6l2.049-3.073z'],
        ['ar' => 'العملاء', 'en' => 'Clients', 'value' => number_format($stats['clients']), 'route' => route('admin.clients.index'), 'color' => 'teal',
         'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
        ['ar' => 'المنظمات الخيرية', 'en' => 'Charitable Organizations', 'value' => number_format($stats['charitable_organizations']), 'route' => route('admin.charitable-organizations.index'), 'color' => 'rose',
         'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
    ];
@endphp

@section('content')
<div class="space-y-8">
    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        @foreach($cards as $card)
            @php $c = $card['color']; @endphp
            <a href="{{ $card['route'] }}"
               class="group relative block overflow-hidden rounded-2xl bg-white p-6 shadow-sm border border-slate-100 transition-all duration-300 hover:shadow-lg hover:-translate-y-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-{{ $c }}-400 focus-visible:ring-offset-2">
                <span aria-hidden="true" class="absolute top-0 end-0 w-32 h-32 bg-{{ $c }}-50 rounded-bl-full -me-8 -mt-8 transition-transform duration-500 group-hover:scale-110"></span>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-{{ $c }}-100/80 rounded-xl flex items-center justify-center text-{{ $c }}-600 shadow-sm transition-colors duration-300 group-hover:bg-{{ $c }}-600 group-hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"></path>
                            </svg>
                        </div>
                        <svg class="w-5 h-5 text-slate-300 transition-all duration-200 group-hover:text-{{ $c }}-500 {{ $isAr ? 'rotate-180 group-hover:-translate-x-0.5' : 'group-hover:translate-x-0.5' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $isAr ? $card['ar'] : $card['en'] }}</h3>
                    <p class="mt-1 text-3xl font-black text-slate-800 tracking-tight">{{ $card['value'] }}</p>
                </div>
            </a>
        @endforeach
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Requests -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    {{ $isAr ? 'طلبات حديثة' : 'Recent Requests' }}
                </h3>
                <a href="{{ route('admin.client-requests.index') }}" class="group text-sm font-medium text-teal-600 hover:text-teal-700 inline-flex items-center gap-1">
                    {{ $isAr ? 'عرض الكل' : 'View All' }}
                    <svg class="w-3.5 h-3.5 transition-transform {{ $isAr ? 'rotate-180 group-hover:-translate-x-0.5' : 'group-hover:translate-x-0.5' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>
            <div class="flex-1">
                @if($recentRequests->count() > 0)
                    <div class="divide-y divide-slate-100">
                        @foreach($recentRequests as $request)
                            <div class="p-4 hover:bg-slate-50 transition-colors flex items-center justify-between gap-3">
                                <div class="flex items-center gap-4 min-w-0">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm shrink-0">
                                        {{ strtoupper(substr($request->client->name ?? '?', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-900 truncate">{{ $request->client->name ?? 'N/A' }}</p>
                                        <p class="text-xs text-slate-500 truncate">{{ $request->type }}</p>
                                    </div>
                                </div>
                                <div class="text-end shrink-0">
                                     <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $request->status === 'pending' ? 'bg-amber-100 text-amber-800' : ($request->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-800') }}">
                                        {{ $request->status }}
                                    </span>
                                    <p class="text-[10px] text-slate-400 mt-1">{{ $request->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-12 text-center text-slate-400">
                        <svg class="w-12 h-12 mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <p>{{ $isAr ? 'لا توجد طلبات حديثة' : 'No recent requests' }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    {{ $isAr ? 'طلبات حديثة' : 'Recent Orders' }}
                </h3>
                <a href="{{ route('admin.orders.index') }}" class="group text-sm font-medium text-teal-600 hover:text-teal-700 inline-flex items-center gap-1">
                    {{ $isAr ? 'عرض الكل' : 'View All' }}
                    <svg class="w-3.5 h-3.5 transition-transform {{ $isAr ? 'rotate-180 group-hover:-translate-x-0.5' : 'group-hover:translate-x-0.5' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>
            <div class="flex-1">
                @if($recentOrders->count() > 0)
                    <div class="divide-y divide-slate-100">
                        @foreach($recentOrders as $order)
                            <div class="p-4 hover:bg-slate-50 transition-colors flex items-center justify-between gap-3">
                                <div class="flex items-center gap-4 min-w-0">
                                     <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold shrink-0">
                                         #
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-900 truncate">{{ $isAr ? 'طلب' : 'Order' }} #{{ $order->id }}</p>
                                        <p class="text-xs text-slate-500 truncate">{{ $order->pharmacy->name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="text-end shrink-0">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-800' }}">
                                        {{ $order->status }}
                                    </span>
                                    <p class="text-[10px] text-slate-400 mt-1">{{ $order->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                 @else
                    <div class="flex flex-col items-center justify-center py-12 text-center text-slate-400">
                        <svg class="w-12 h-12 mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        <p>{{ $isAr ? 'لا توجد طلبات حديثة' : 'No recent orders' }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
