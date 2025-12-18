@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard')
@section('page-title', app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard')
@section('page-description', app()->getLocale() === 'ar' ? 'نظرة عامة على أداء النظام وإحصائيات سريعة' : 'Overview of system performance and quick statistics')

@section('content')
<div class="space-y-8">
    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Medicines -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow duration-300 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110 duration-500"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-blue-100/80 rounded-xl flex items-center justify-center text-blue-600 shadow-sm group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded-full">+12%</span>
                </div>
                <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'الأدوية' : 'Medicines' }}</h3>
                <div class="flex items-end justify-between mt-1">
                    <p class="text-3xl font-black text-slate-800 tracking-tight">{{ number_format($stats['medicines']) }}</p>
                    <a href="{{ route('admin.medicines.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                        {{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }} 
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Users -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow duration-300 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110 duration-500"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-emerald-100/80 rounded-xl flex items-center justify-center text-emerald-600 shadow-sm group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'المستخدمون' : 'Users' }}</h3>
                <div class="flex items-end justify-between mt-1">
                    <p class="text-3xl font-black text-slate-800 tracking-tight">{{ number_format($stats['users']) }}</p>
                    <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                         {{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Pharmacies -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow duration-300 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-purple-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110 duration-500"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-purple-100/80 rounded-xl flex items-center justify-center text-purple-600 shadow-sm group-hover:bg-purple-600 group-hover:text-white transition-colors duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'الصيدليات' : 'Pharmacies' }}</h3>
                <div class="flex items-end justify-between mt-1">
                    <p class="text-3xl font-black text-slate-800 tracking-tight">{{ number_format($stats['pharmacies']) }}</p>
                    <a href="{{ route('admin.pharmacies.index') }}" class="text-sm font-semibold text-purple-600 hover:text-purple-700 flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                         {{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Laboratories -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow duration-300 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110 duration-500"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-cyan-100/80 rounded-xl flex items-center justify-center text-cyan-600 shadow-sm group-hover:bg-cyan-600 group-hover:text-white transition-colors duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'المعامل' : 'Laboratories' }}</h3>
                <div class="flex items-end justify-between mt-1">
                    <p class="text-3xl font-black text-slate-800 tracking-tight">{{ number_format($stats['laboratories']) }}</p>
                    <a href="{{ route('admin.laboratories.index') }}" class="text-sm font-semibold text-cyan-600 hover:text-cyan-700 flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                         {{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Requests -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow duration-300 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-red-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110 duration-500"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-red-100/80 rounded-xl flex items-center justify-center text-red-600 shadow-sm group-hover:bg-red-600 group-hover:text-white transition-colors duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'طلبات العملاء' : 'Requests' }}</h3>
                <div class="flex items-end justify-between mt-1">
                    <p class="text-3xl font-black text-slate-800 tracking-tight">{{ number_format($stats['client_requests']) }}</p>
                    <a href="{{ route('admin.client-requests.index') }}" class="text-sm font-semibold text-red-600 hover:text-red-700 flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                         {{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Orders -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow duration-300 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110 duration-500"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-indigo-100/80 rounded-xl flex items-center justify-center text-indigo-600 shadow-sm group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                       <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'الطلبات' : 'Orders' }}</h3>
                <div class="flex items-end justify-between mt-1">
                    <p class="text-3xl font-black text-slate-800 tracking-tight">{{ number_format($stats['orders']) }}</p>
                    <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                         {{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Offers -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow duration-300 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-amber-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110 duration-500"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-amber-100/80 rounded-xl flex items-center justify-center text-amber-600 shadow-sm group-hover:bg-amber-600 group-hover:text-white transition-colors duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'العروض' : 'Offers' }}</h3>
                <div class="flex items-end justify-between mt-1">
                    <p class="text-3xl font-black text-slate-800 tracking-tight">{{ number_format($stats['offers']) }}</p>
                    <a href="{{ route('admin.offers.index') }}" class="text-sm font-semibold text-amber-600 hover:text-amber-700 flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                         {{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>
        </div>
        
         <!-- Clients -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow duration-300 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-teal-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110 duration-500"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-teal-100/80 rounded-xl flex items-center justify-center text-teal-600 shadow-sm group-hover:bg-teal-600 group-hover:text-white transition-colors duration-300">
                         <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'العملاء' : 'Clients' }}</h3>
                <div class="flex items-end justify-between mt-1">
                    <p class="text-3xl font-black text-slate-800 tracking-tight">{{ number_format($stats['clients']) }}</p>
                    <a href="{{ route('admin.clients.index') }}" class="text-sm font-semibold text-teal-600 hover:text-teal-700 flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                         {{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Requests -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-lg font-bold text-slate-900">{{ app()->getLocale() === 'ar' ? 'طلبات حديثة' : 'Recent Requests' }}</h3>
                <a href="{{ route('admin.client-requests.index') }}" class="text-sm font-medium text-primary-600 hover:text-primary-700 hover:underline">
                    {{ app()->getLocale() === 'ar' ? 'عرض الكل' : 'View All' }}
                </a>
            </div>
            <div class="p-0 flex-1">
                @if($recentRequests->count() > 0)
                    <div class="divide-y divide-slate-100">
                        @foreach($recentRequests as $request)
                            <div class="p-4 hover:bg-slate-50 transition-colors flex items-center justify-between group">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                                        {{ substr($request->client->name ?? '?', 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $request->client->name ?? 'N/A' }}</p>
                                        <p class="text-xs text-slate-500">{{ $request->type }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
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
                        <p>{{ app()->getLocale() === 'ar' ? 'لا توجد طلبات حديثة' : 'No recent requests' }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-lg font-bold text-slate-900">{{ app()->getLocale() === 'ar' ? 'طلبات حديثة' : 'Recent Orders' }}</h3>
                <a href="{{ route('admin.orders.index') }}" class="text-sm font-medium text-primary-600 hover:text-primary-700 hover:underline">
                    {{ app()->getLocale() === 'ar' ? 'عرض الكل' : 'View All' }}
                </a>
            </div>
            <div class="p-0 flex-1">
                @if($recentOrders->count() > 0)
                    <div class="divide-y divide-slate-100">
                        @foreach($recentOrders as $order)
                            <div class="p-4 hover:bg-slate-50 transition-colors flex items-center justify-between group">
                                <div class="flex items-center gap-4">
                                     <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">
                                         #
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900">Order #{{ $order->id }}</p>
                                        <p class="text-xs text-slate-500">{{ $order->pharmacy->name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
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
                        <p>{{ app()->getLocale() === 'ar' ? 'لا توجد طلبات حديثة' : 'No recent orders' }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
