@extends('client.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard')

@section('page-title', app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard')
@section('page-description', app()->getLocale() === 'ar' ? 'نظرة عامة على طلباتك وطلباتك' : 'Overview of your requests and orders')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-left: 12px;
        padding-right: 20px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
        right: 8px;
    }
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #3b82f6;
        outline: 0;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    [dir="rtl"] .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-right: 12px;
        padding-left: 20px;
    }
    [dir="rtl"] .select2-container--default .select2-selection--single .select2-selection__arrow {
        left: 8px;
        right: auto;
    }
    #serviceProviderMap {
        height: 500px;
        width: 100%;
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
    }
    .filter-toggle {
        cursor: pointer;
        user-select: none;
    }
    .filter-content {
        transition: max-height 0.3s ease-out;
        overflow: hidden;
    }
    .filter-content.collapsed {
        max-height: 0;
    }
    .filter-content.expanded {
        max-height: 1000px;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto">

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">{{ app()->getLocale() === 'ar' ? 'إجمالي الطلبات' : 'Total Requests' }}</p>
                        <p class="text-2xl font-bold text-slate-900 mt-2">{{ $stats['total_requests'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="bi bi-file-text text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">{{ app()->getLocale() === 'ar' ? 'طلبات قيد الانتظار' : 'Pending Requests' }}</p>
                        <p class="text-2xl font-bold text-slate-900 mt-2">{{ $stats['pending_requests'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="bi bi-clock text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">{{ app()->getLocale() === 'ar' ? 'إجمالي الطلبات' : 'Total Orders' }}</p>
                        <p class="text-2xl font-bold text-slate-900 mt-2">{{ $stats['total_orders'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="bi bi-cart-check text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">{{ app()->getLocale() === 'ar' ? 'طلبات نشطة' : 'Active Orders' }}</p>
                        <p class="text-2xl font-bold text-slate-900 mt-2">{{ $stats['active_orders'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="bi bi-activity text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <a href="{{ route('client.test-results.index') }}" class="block group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">{{ app()->getLocale() === 'ar' ? 'نتائج التحاليل' : 'Test Results' }}</p>
                            <p class="text-2xl font-bold text-slate-900 mt-2 group-hover:text-primary transition-colors">{{ $stats['test_results'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-all">
                            <i class="bi bi-file-earmark-medical text-xl"></i>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Service Provider Search Section -->
        <div class="mb-8">
            {{-- Collapsible Filters Section --}}
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b filter-toggle" id="filterToggle">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">
                            {{ app()->getLocale() === 'ar' ? 'البحث عن مقدمي الخدمات' : 'Search Service Providers' }}
                        </h3>
                        <svg id="filterIcon" class="w-5 h-5 text-gray-600 transform transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
                <div class="filter-content collapsed" id="filterContent">
                    <div class="p-6">
                    <form method="GET" action="{{ route('client.dashboard') }}" class="space-y-4" id="filterForm">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            {{-- Service Provider Type (Required) --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ app()->getLocale() === 'ar' ? 'نوع مقدم الخدمة' : 'Service Provider Type' }}
                                    <span class="text-red-500">*</span>
                                </label>
                                <select name="provider_type" id="provider_type" class="w-full border rounded-md p-2" required>
                                    <option value="">{{ app()->getLocale() === 'ar' ? 'اختر النوع' : 'Select Type' }}</option>
                                    <option value="laboratory" @selected(request('provider_type') === 'laboratory')>
                                        {{ app()->getLocale() === 'ar' ? 'مختبر' : 'Laboratory' }}
                                    </option>
                                    <option value="pharmacy" @selected(request('provider_type') === 'pharmacy')>
                                        {{ app()->getLocale() === 'ar' ? 'صيدلية' : 'Pharmacy' }}
                                    </option>
                                </select>
                            </div>

                            {{-- Governorate Filter (Required) --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ app()->getLocale() === 'ar' ? 'المحافظة' : 'Governorate' }}
                                    <span class="text-red-500">*</span>
                                </label>
                                <select name="governorate_id" id="governorate_id" class="w-full border rounded-md p-2">
                                    <option value="">{{ app()->getLocale() === 'ar' ? 'اختر المحافظة' : 'Select Governorate' }}</option>
                                    @foreach($governorates ?? [] as $governorate)
                                        <option value="{{ $governorate->id }}" @selected(request('governorate_id') == $governorate->id)>
                                            {{ app()->getLocale() === 'ar' ? $governorate->name_ar : $governorate->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- City Filter (Optional) --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ app()->getLocale() === 'ar' ? 'المدينة' : 'City' }}
                                    <span class="text-gray-400 text-xs">({{ app()->getLocale() === 'ar' ? 'اختياري' : 'Optional' }})</span>
                                </label>
                                <select name="city_id" id="city_id" class="w-full border rounded-md p-2">
                                    <option value="">{{ app()->getLocale() === 'ar' ? 'جميع المدن' : 'All Cities' }}</option>
                                    @foreach($cities ?? [] as $city)
                                        <option value="{{ $city->id }}" @selected(request('city_id') == $city->id)>
                                            {{ app()->getLocale() === 'ar' ? $city->name_ar : $city->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Area Filter (Optional) --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ app()->getLocale() === 'ar' ? 'المنطقة' : 'Area' }}
                                    <span class="text-gray-400 text-xs">({{ app()->getLocale() === 'ar' ? 'اختياري' : 'Optional' }})</span>
                                </label>
                                <select name="area_id" id="area_id" class="w-full border rounded-md p-2">
                                    <option value="">{{ app()->getLocale() === 'ar' ? 'جميع المناطق' : 'All Areas' }}</option>
                                    @foreach($areas ?? [] as $area)
                                        <option value="{{ $area->id }}" @selected(request('area_id') == $area->id)>
                                            {{ app()->getLocale() === 'ar' ? $area->name_ar : $area->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                {{ app()->getLocale() === 'ar' ? 'بحث' : 'Search' }}
                            </button>
                            <a href="{{ route('client.dashboard') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                {{ app()->getLocale() === 'ar' ? 'إعادة تعيين' : 'Reset' }}
                            </a>
                        </div>
                    </form>
                    </div>
                </div>
            </div>

            @if(request()->has('governorate_id') && request()->has('provider_type'))
                {{-- Map Section --}}
                @if(isset($markers) && count($markers) > 0)
                    <div class="bg-white rounded-lg shadow p-6 mt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            {{ app()->getLocale() === 'ar' ? 'الخريطة' : 'Map' }}
                            <span class="text-sm text-gray-500 font-normal">
                                ({{ count($markers) }} {{ app()->getLocale() === 'ar' ? 'موقع' : 'location' }}{{ count($markers) !== 1 ? 's' : '' }})
                            </span>
                        </h3>
                        <div id="serviceProviderMap"></div>
                    </div>

                    {{-- Results Cards Section --}}
                    <div class="bg-white rounded-lg shadow p-6 mt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            {{ app()->getLocale() === 'ar' ? 'نتائج البحث' : 'Search Results' }}
                            <span class="text-sm text-gray-500 font-normal">
                                ({{ count($results) }} {{ app()->getLocale() === 'ar' ? 'نتيجة' : 'result' }}{{ count($results) !== 1 ? 's' : '' }})
                            </span>
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($results as $item)
                                <div class="border rounded-lg p-4 hover:shadow-lg transition-shadow" data-marker-id="{{ $item->id }}">
                                    {{-- Logo --}}
                                    @if(isset($item->logo) && $item->logo)
                                        <div class="mb-3">
                                            <img src="{{ asset('storage/' . $item->logo) }}" 
                                                 alt="{{ $item->name }}" 
                                                 class="w-full h-32 object-contain rounded"
                                                 onerror="this.style.display='none'">
                                        </div>
                                    @endif

                                    {{-- Name --}}
                                    <h4 class="text-lg font-semibold text-gray-800 mb-2">{{ $item->name }}</h4>

                                    {{-- Type Badge --}}
                                    <div class="mb-2">
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            {{ $providerType === 'laboratory' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $providerType === 'laboratory' 
                                                ? (app()->getLocale() === 'ar' ? 'مختبر' : 'Laboratory')
                                                : (app()->getLocale() === 'ar' ? 'صيدلية' : 'Pharmacy') }}
                                        </span>
                                    </div>

                                    {{-- Location --}}
                                    @if($item->area)
                                        <div class="text-sm text-gray-600 mb-2">
                                            <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            {{ app()->getLocale() === 'ar' ? $item->area->name_ar : $item->area->name }}
                                            @if($item->area->city)
                                                , {{ app()->getLocale() === 'ar' ? $item->area->city->name_ar : $item->area->city->name }}
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Contact Info --}}
                                    <div class="space-y-1 text-sm text-gray-600">
                                        @if($item->phone)
                                            <div>
                                                <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                </svg>
                                                {{ $item->phone }}
                                            </div>
                                        @endif
                                        @if($item->email)
                                            <div>
                                                <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                </svg>
                                                {{ $item->email }}
                                            </div>
                                        @endif
                                        @if($item->opening_time && $item->closing_time)
                                            <div>
                                                <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                {{ \Carbon\Carbon::parse($item->opening_time)->format('H:i') }} - 
                                                {{ \Carbon\Carbon::parse($item->closing_time)->format('H:i') }}
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Address --}}
                                    @if($item->address)
                                        <div class="mt-2 text-sm text-gray-500">
                                            {{ Str::limit($item->address, 100) }}
                                        </div>
                                    @endif

                                    {{-- Action Buttons --}}
                                    <div class="mt-4 space-y-2">
                                        @if($providerType === 'laboratory')
                                            <!-- <a href="{{ route('client.laboratories.offers', $item->id) }}" 
                                               class="block w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors text-center mb-2">
                                                <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 011 12V7a4 4 0 014-4z"/>
                                                </svg>
                                                {{ app()->getLocale() === 'ar' ? 'عرض العروض' : 'View Offers' }}
                                            </a> -->
                                            @if($item->type === 'test' || $item->type === 'both')
                                                <a href="{{ route('client.test-requests.create', ['type' => 'test', 'laboratory_id' => $item->id]) }}" 
                                                   class="block w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors text-center mb-2">
                                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    {{ app()->getLocale() === 'ar' ? 'طلب تحاليل طبية' : 'Request Medical Tests' }}
                                                </a>
                                            @endif
                                            @if($item->type === 'radiology' || $item->type === 'both')
                                                <a href="{{ route('client.test-requests.create', ['type' => 'radiology', 'laboratory_id' => $item->id]) }}" 
                                                   class="block w-full px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors text-center">
                                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    {{ app()->getLocale() === 'ar' ? 'طلب أشعة' : 'Request Radiology' }}
                                                </a>
                                            @endif
                                        @else
                                            <a href="{{ route('client.medicine-requests.create', ['pharmacy_id' => $item->id]) }}" 
                                               class="block w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors text-center">
                                                <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                                </svg>
                                                {{ app()->getLocale() === 'ar' ? 'طلب أدوية' : 'Request Medicines' }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="bg-white rounded-lg shadow p-6 mt-6">
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-gray-500 text-lg">
                                {{ app()->getLocale() === 'ar' ? 'لا توجد نتائج متاحة' : 'No results found' }}
                            </p>
                        </div>
                    </div>
                @endif
            @endif
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Requests -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-slate-900">
                        {{ app()->getLocale() === 'ar' ? 'الطلبات الأخيرة' : 'Recent Requests' }}
                    </h3>
                </div>
                <div class="p-6">
                    @if($recentRequests->count() > 0)
                        <div class="space-y-4">
                            @foreach($recentRequests as $request)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-slate-900">#{{ $request->id }}</p>
                                        <p class="text-sm text-gray-600">{{ $request->created_at->format('M d, Y') }}</p>
                                    </div>
                                    <span class="px-3 py-1 text-xs font-medium rounded-full
                                        {{ $request->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $request->status === 'accepted' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $request->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">
                            {{ app()->getLocale() === 'ar' ? 'لا توجد طلبات' : 'No requests yet' }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-slate-900">
                        {{ app()->getLocale() === 'ar' ? 'الطلبات الأخيرة' : 'Recent Orders' }}
                    </h3>
                </div>
                <div class="p-6">
                    @if($recentOrders->count() > 0)
                        <div class="space-y-4">
                            @foreach($recentOrders as $order)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-slate-900">Order #{{ $order->id }}</p>
                                        <p class="text-sm text-gray-600">
                                            {{ $order->pharmacy->name ?? 'N/A' }}
                                        </p>
                                    </div>
                                    <span class="px-3 py-1 text-xs font-medium rounded-full
                                        {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">
                            {{ app()->getLocale() === 'ar' ? 'لا توجد طلبات' : 'No orders yet' }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@if(app()->getLocale() === 'ar')
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/i18n/ar.min.js"></script>
@endif
<script>
    $(document).ready(function() {
        const isRTL = {{ app()->getLocale() === 'ar' ? 'true' : 'false' }};
        const isArabic = {{ app()->getLocale() === 'ar' ? 'true' : 'false' }};

        // Collapsible filter functionality
        const filterToggle = document.getElementById('filterToggle');
        const filterContent = document.getElementById('filterContent');
        const filterIcon = document.getElementById('filterIcon');
        let isExpanded = false;

        if (filterToggle && filterContent && filterIcon) {
            filterToggle.addEventListener('click', function() {
                isExpanded = !isExpanded;
                if (isExpanded) {
                    filterContent.classList.remove('collapsed');
                    filterContent.classList.add('expanded');
                    filterIcon.classList.add('rotate-180');
                } else {
                    filterContent.classList.remove('expanded');
                    filterContent.classList.add('collapsed');
                    filterIcon.classList.remove('rotate-180');
                }
            });
        }

        // Initialize Select2
        $('#provider_type').select2({
            placeholder: isArabic ? 'اختر النوع' : 'Select Type',
            allowClear: false,
            width: '100%',
            language: isRTL ? 'ar' : 'en',
            dir: isRTL ? 'rtl' : 'ltr',
            minimumResultsForSearch: Infinity
        });

        $('#governorate_id').select2({
            placeholder: isArabic ? 'اختر المحافظة' : 'Select Governorate',
            allowClear: false,
            width: '100%',
            language: isRTL ? 'ar' : 'en',
            dir: isRTL ? 'rtl' : 'ltr'
        });

        $('#city_id').select2({
            placeholder: isArabic ? 'جميع المدن' : 'All Cities',
            allowClear: true,
            width: '100%',
            language: isRTL ? 'ar' : 'en',
            dir: isRTL ? 'rtl' : 'ltr'
        });

        $('#area_id').select2({
            placeholder: isArabic ? 'جميع المناطق' : 'All Areas',
            allowClear: true,
            width: '100%',
            language: isRTL ? 'ar' : 'en',
            dir: isRTL ? 'rtl' : 'ltr'
        });

        // Update cities when governorate changes
        $('#governorate_id').on('change', function() {
            const governorateId = $(this).val();
            const citySelect = $('#city_id');
            const areaSelect = $('#area_id');
            
            citySelect.select2('destroy');
            areaSelect.select2('destroy');
            
            citySelect.empty().append('<option value="">' + (isArabic ? 'جميع المدن' : 'All Cities') + '</option>');
            areaSelect.empty().append('<option value="">' + (isArabic ? 'جميع المناطق' : 'All Areas') + '</option>');

            if (governorateId) {
                fetch(`/api/cities?governorate_id=${governorateId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.data) {
                            data.data.forEach(city => {
                                citySelect.append(new Option(
                                    isArabic ? (city.name_ar || city.name) : (city.name || city.name_ar),
                                    city.id,
                                    false,
                                    false
                                ));
                            });
                        }
                    })
                    .catch(error => console.error('Error:', error))
                    .finally(() => {
                        citySelect.select2({
                            placeholder: isArabic ? 'جميع المدن' : 'All Cities',
                            allowClear: true,
                            width: '100%',
                            language: isRTL ? 'ar' : 'en',
                            dir: isRTL ? 'rtl' : 'ltr'
                        });
                        areaSelect.select2({
                            placeholder: isArabic ? 'جميع المناطق' : 'All Areas',
                            allowClear: true,
                            width: '100%',
                            language: isRTL ? 'ar' : 'en',
                            dir: isRTL ? 'rtl' : 'ltr'
                        });
                    });
            } else {
                citySelect.select2({
                    placeholder: isArabic ? 'جميع المدن' : 'All Cities',
                    allowClear: true,
                    width: '100%',
                    language: isRTL ? 'ar' : 'en',
                    dir: isRTL ? 'rtl' : 'ltr'
                });
                areaSelect.select2({
                    placeholder: isArabic ? 'جميع المناطق' : 'All Areas',
                    allowClear: true,
                    width: '100%',
                    language: isRTL ? 'ar' : 'en',
                    dir: isRTL ? 'rtl' : 'ltr'
                });
            }
        });

        // Update areas when city changes
        $('#city_id').on('change', function() {
            const cityId = $(this).val();
            const areaSelect = $('#area_id');
            
            areaSelect.select2('destroy');
            areaSelect.empty().append('<option value="">' + (isArabic ? 'جميع المناطق' : 'All Areas') + '</option>');

            if (cityId) {
                fetch(`/api/areas?city_id=${cityId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.data) {
                            data.data.forEach(area => {
                                areaSelect.append(new Option(
                                    isArabic ? (area.name_ar || area.name) : (area.name || area.name_ar),
                                    area.id,
                                    false,
                                    false
                                ));
                            });
                        }
                    })
                    .catch(error => console.error('Error:', error))
                    .finally(() => {
                        areaSelect.select2({
                            placeholder: isArabic ? 'جميع المناطق' : 'All Areas',
                            allowClear: true,
                            width: '100%',
                            language: isRTL ? 'ar' : 'en',
                            dir: isRTL ? 'rtl' : 'ltr'
                        });
                    });
            } else {
                areaSelect.select2({
                    placeholder: isArabic ? 'جميع المناطق' : 'All Areas',
                    allowClear: true,
                    width: '100%',
                    language: isRTL ? 'ar' : 'en',
                    dir: isRTL ? 'rtl' : 'ltr'
                });
            }
        });

        // Initialize map if markers exist
        @if(isset($markers) && count($markers) > 0)
        const mapCenter = {!! json_encode($mapCenter) !!};
        const markers = {!! json_encode($markers) !!};
        
        const map = L.map('serviceProviderMap').setView([mapCenter.lat, mapCenter.lng], 12);
        
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);

        const mapMarkers = [];
        markers.forEach(function(markerData) {
            const iconColor = markerData.type === 'laboratory' ? 'blue' : 'green';
            const customIcon = L.divIcon({
                className: 'custom-marker',
                html: `<div style="background-color: ${iconColor}; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            });

            const marker = L.marker([markerData.lat, markerData.lng], { icon: customIcon })
                .addTo(map)
                .bindPopup(`
                    <div style="min-width: 200px;">
                        <h4 style="font-weight: bold; margin-bottom: 5px;">${markerData.name}</h4>
                        <p style="margin: 2px 0; font-size: 12px;">${markerData.type === 'laboratory' ? (isArabic ? 'مختبر' : 'Laboratory') : (isArabic ? 'صيدلية' : 'Pharmacy')}</p>
                        ${markerData.phone ? `<p style="margin: 2px 0; font-size: 12px;">📞 ${markerData.phone}</p>` : ''}
                        ${markerData.address ? `<p style="margin: 2px 0; font-size: 12px;">📍 ${markerData.address}</p>` : ''}
                    </div>
                `);

            mapMarkers.push({ marker: marker, data: markerData });
        });

        // Fit map to show all markers
        if (markers.length > 0) {
            const group = new L.featureGroup(mapMarkers.map(m => m.marker));
            map.fitBounds(group.getBounds().pad(0.1));
        }

        // Highlight card when marker is clicked
        mapMarkers.forEach(function(item) {
            item.marker.on('click', function() {
                const card = document.querySelector(`[data-marker-id="${item.data.id}"]`);
                if (card) {
                    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    card.style.border = '2px solid #3b82f6';
                    setTimeout(() => {
                        card.style.border = '';
                    }, 2000);
                }
            });
        });
        @endif
    });
</script>
@endpush
@endsection

